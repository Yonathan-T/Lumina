<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Conversation;
use App\Services\UserDataService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class AiChatService
{
    protected $provider;
    protected $openaiApiKey;
    protected $huggingfaceApiKey;
    protected $geminiApiKey;
    protected $huggingfaceModel;
    protected $userDataService;

    public function __construct()
    {

        $user = auth()->user();

        $encryptedKey = $user->api_key ?? null;

        $this->geminiApiKey = null;

        if ($encryptedKey) {
            try {
                $this->geminiApiKey = Crypt::decryptString($encryptedKey);
            } catch (DecryptException $e) {
                \Log::error("Failed to decrypt Gemini API key for user {$user->id}: " . $e->getMessage());
            }
        }

        $this->provider = config('services.ai.provider', 'gemini');
        // $this->openaiApiKey = config('services.openai.api_key');
        // $this->huggingfaceApiKey = config('services.huggingface.api_key');
        // $this->huggingfaceModel = config('services.huggingface.model');
        // $this->geminiApiKey = config('services.gemini.api_key');
        $this->userDataService = new UserDataService();
    }

    public function generateResponse(string $message, int $conversationId = null): string
    {
        try {
            $conversationHistory = $this->getConversationHistory($conversationId);
            $userContext = $this->getUserContext($message);

            if ($this->provider === 'gemini' && !$this->geminiApiKey) {
                return $this->generateFallbackResponse('gemini');
            }

            if ($this->provider === 'openai' && !$this->openaiApiKey) {
                return $this->generateFallbackResponse('openai');
            }

            if ($this->provider === 'huggingface' && !$this->huggingfaceApiKey) {
                return $this->generateFallbackResponse('huggingface');
            }


            if ($this->provider === 'gemini') {
                return $this->generateGeminiResponse($message, $conversationHistory, $userContext);
            } elseif ($this->provider === 'huggingface') {
                return $this->generateHuggingFaceResponse($message, $conversationHistory, $userContext);
            } elseif ($this->provider === 'openai') {
                return $this->generateOpenAIResponse($message, $conversationHistory, $userContext);
            }

            return 'Sorry, the configured AI provider is not supported.';

        } catch (\Exception $e) {
            Log::error('AI Chat Service Error', ['error' => $e->getMessage()]);
            return 'Sorry, I encountered an error while processing your request.';
        }
    }
    public function streamResponse(string $message, int $conversationId = null, callable $onChunk = null): string
    {
        $onChunk = $onChunk ?? static function (): void {};

        try {
            if ($this->provider === 'gemini' && $this->geminiApiKey) {
                return $this->streamGeminiResponse($message, $conversationId, $onChunk);
            }

            $response = $this->generateResponse($message, $conversationId);
            $this->streamTextResponse($response, $onChunk);

            return $response;
        } catch (\Exception $e) {
            Log::error('AI Chat Stream Error', ['error' => $e->getMessage()]);

            $fallbackMessage = $this->getConnectionErrorMessage($e);
            $this->streamTextResponse($fallbackMessage, $onChunk);

            return $fallbackMessage;
        }
    }

    protected function streamGeminiResponse(string $message, int $conversationId = null, callable $onChunk = null): string
    {
        set_time_limit(180);

        $onChunk = $onChunk ?? static function (): void {};

        $conversationHistory = $this->getConversationHistory($conversationId);
        $userContext = $this->getUserContext($message);
        $systemPrompt = $this->getGeminiSystemPrompt($userContext);
        $messages = $this->buildGeminiMessages($message, $conversationHistory);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->withOptions([
                    'stream' => true,
                ])
                ->timeout(120)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:streamGenerateContent?alt=sse&key={$this->geminiApiKey}", $this->buildGeminiRequestPayload($messages, $systemPrompt));

        if (! $response->successful()) {
            Log::error('Gemini Streaming API Error', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            $errorMessage = $this->getGeminiErrorMessage($response);
            $this->streamTextResponse($errorMessage, $onChunk);

            return $errorMessage;
        }

        $stream = $response->toPsrResponse()->getBody();
        $buffer = '';
        $fullResponse = '';

        while (! $stream->eof()) {
            $buffer .= $stream->read(2048);

            // Gemini's SSE stream delimits events with CRLF (\r\n\r\n). Normalize
            // to LF so the "\n\n" event delimiter below actually matches; without
            // this, no event is ever parsed and the response comes back empty.
            $buffer = str_replace("\r\n", "\n", $buffer);

            while (($delimiterPosition = strpos($buffer, "\n\n")) !== false) {
                $rawEvent = substr($buffer, 0, $delimiterPosition);
                $buffer = substr($buffer, $delimiterPosition + 2);

                $delta = $this->extractDeltaFromGeminiStreamEvent($rawEvent, $fullResponse);

                if ($delta === '') {
                    continue;
                }

                $fullResponse .= $delta;
                $onChunk($delta);
            }
        }

        if (trim($buffer) !== '') {
            $delta = $this->extractDeltaFromGeminiStreamEvent($buffer, $fullResponse);

            if ($delta !== '') {
                $fullResponse .= $delta;
                $onChunk($delta);
            }
        }

        if ($fullResponse === '') {
            $errorMessage = 'Sorry, I could not generate a response. Please try again.';
            $this->streamTextResponse($errorMessage, $onChunk);

            return $errorMessage;
        }

        return $fullResponse;
    }

    protected function streamTextResponse(string $response, callable $onChunk): void
    {
        if ($response === '') {
            return;
        }

        $chunks = preg_split('/(\s+)/u', $response, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($chunks as $chunk) {
            $onChunk($chunk);
        }
    }

    protected function extractDeltaFromGeminiStreamEvent(string $rawEvent, string $currentResponse): string
    {
        $rawEvent = str_replace("\r\n", "\n", trim($rawEvent));

        if ($rawEvent === '') {
            return '';
        }

        $dataLines = [];

        foreach (explode("\n", $rawEvent) as $line) {
            $line = trim($line);

            if (! str_starts_with($line, 'data:')) {
                continue;
            }

            $dataLines[] = trim(substr($line, 5));
        }

        if (empty($dataLines)) {
            return '';
        }

        $payload = implode("\n", $dataLines);

        if ($payload === '[DONE]') {
            return '';
        }

        $decoded = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return '';
        }

        $text = $this->extractVisibleTextFromGeminiParts(
            data_get($decoded, 'candidates.0.content.parts', [])
        );

        if ($text === '') {
            return '';
        }

        if ($currentResponse !== '' && str_starts_with($text, $currentResponse)) {
            return substr($text, strlen($currentResponse));
        }

        if ($currentResponse !== '' && str_contains($currentResponse, $text)) {
            return '';
        }

        return $text;
    }

    protected function extractVisibleTextFromGeminiParts(array $parts): string
    {
        $text = '';

        foreach ($parts as $part) {
            if (! is_array($part) || ! empty($part['thought'])) {
                continue;
            }

            if (isset($part['text']) && is_string($part['text']) && $part['text'] !== '') {
                $text .= $part['text'];
            }
        }

        return $text;
    }

    protected function getGeminiErrorMessage($response): string
    {
        $status = method_exists($response, 'status') ? $response->status() : null;
        $body = method_exists($response, 'json') ? $response->json() : null;
        $error = is_array($body) ? ($body['error'] ?? null) : null;
        $code = is_array($error) ? ($error['code'] ?? $status) : $status;
        $message = is_array($error) ? (string) ($error['message'] ?? '') : '';
        $errorStatus = is_array($error) ? (string) ($error['status'] ?? '') : '';

        if ($code === 429 || str_contains(strtolower($message), 'quota') || str_contains(strtolower($message), 'rate limit')) {
            return "You've reached your Gemini API limit. On the free tier that's about 20 requests per day for this model. Wait a bit or check your usage at ai.google.dev, then try again.";
        }

        if ($code === 503 || $errorStatus === 'UNAVAILABLE' || str_contains(strtolower($message), 'high demand')) {
            return 'Gemini is under heavy load right now. Please try again in a moment.';
        }

        if (in_array($code, [401, 403], true) || str_contains(strtolower($message), 'api key')) {
            return 'Your Gemini API key looks invalid or expired. Update it in Settings > Account > API Integration.';
        }

        if ($message !== '') {
            return 'Gemini returned an error: '.trim(explode("\n", $message)[0]);
        }

        return 'Sorry, I encountered an error while processing your request.';
    }

    protected function getConnectionErrorMessage(\Throwable $e): string
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'connection refused') || str_contains($message, 'could not resolve host') || str_contains($message, 'timed out')) {
            return 'Could not reach Gemini right now. Check your internet connection and try again.';
        }

        return 'Sorry, I encountered an error while processing your request.';
    }

    protected function getGeminiGenerationConfig(): array
    {
        return [
            'thinkingConfig' => [
                'thinkingBudget' => 0,
            ],
        ];
    }

    protected function buildGeminiRequestPayload(array $messages, string $systemPrompt): array
    {
        return [
            'contents' => $messages,
            'systemInstruction' => [
                'parts' => [
                    ['text' => $systemPrompt],
                ],
            ],
            'generationConfig' => $this->getGeminiGenerationConfig(),
        ];
    }


    protected function generateGeminiResponse(string $message, array $conversationHistory, string $userContext = ''): string
    {
        $systemPrompt = $this->getGeminiSystemPrompt($userContext);
        $messages = $this->buildGeminiMessages($message, $conversationHistory);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$this->geminiApiKey}", $this->buildGeminiRequestPayload($messages, $systemPrompt));


        if ($response->successful()) {
            $data = $response->json();
            $text = $this->extractVisibleTextFromGeminiParts(
                data_get($data, 'candidates.0.content.parts', [])
            );

            return $text !== '' ? $text : 'Sorry, I could not generate a response.';
        }

        \Log::error('Gemini API Error', ['status' => $response->status(), 'response' => $response->body()]);

        return $this->getGeminiErrorMessage($response);
    }

    protected function generateOpenAIResponse(string $message, array $conversationHistory, string $userContext = ''): string
    {
        $messages = $this->buildMessages($message, $conversationHistory, $userContext);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openaiApiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => $messages,
                    'max_tokens' => 500,
                    'temperature' => 0.7,
                ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? 'Sorry, I could not generate a response.';
        }

        Log::error('OpenAI API Error', ['response' => $response->body()]);
        return 'Sorry, I encountered an error while processing your request.';
    }

    protected function generateHuggingFaceResponse(string $message, array $conversationHistory, string $userContext = ''): string
    {
        $prompt = $this->buildMistralPrompt($message, $conversationHistory, $userContext);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->huggingfaceApiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post("https://api-inference.huggingface.co/models/{$this->huggingfaceModel}", [
                    'inputs' => $prompt,
                    'parameters' => [
                        'max_new_tokens' => 500,
                        'temperature' => 0.7,
                        'do_sample' => true,
                        'return_full_text' => false,
                    ],
                ]);

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data[0]['generated_text'])) {
                return trim($data[0]['generated_text']);
            }
            if (isset($data['generated_text'])) {
                return trim($data['generated_text']);
            }
            if (is_string($data)) {
                return trim($data);
            }

            return 'Sorry, I could not generate a response.';
        }

        if ($response->status() === 503) {
            $errorData = $response->json();
            if (isset($errorData['error']) && str_contains($errorData['error'], 'loading')) {
                return 'The AI model is currently loading. Please try again in a few moments.';
            }
        }

        Log::error('HuggingFace API Error', [
            'status' => $response->status(),
            'response' => $response->body(),
        ]);
        return 'Sorry, I encountered an error while processing your request.';
    }
    protected function generateFallbackResponse(string $model): string
    {
        return "To start chatting with Lumi, please configure your {$model} API key first. Head to Settings > Account > API Integration Settings to enter and save your key.";
    }
    protected function getGeminiSystemPrompt(string $userContext = ''): string
    {
        return "### Lumina AI Assistant System Prompt
You are Lumi, Lumi is the short form of Lumina, a platform dedicated to self-reflection and mental well-being. you Lumi are an empathetic and intelligent journaling assistant designed to support users on their path to self-reflection and mental well-being. Your role is to engage users in thoughtful, therapeutic-style conversations by understanding and referencing the context of their journal entries. Each interaction should help users explore their feelings, gain insights, and encourage positive mental health habits.
If the user asks if you remember, you will acknowledge the conversation and proceed. You will not admit to being an AI with a lack of memory.
### Key principles you follow:

- Respond as if you were a real, empathetic friend Listen and respond naturally - don't over-analyze or ask obvious questions.
- Use simple, human-like language.
- Ask open-ended questions to encourage the user to share more but - When someone is clear about something, acknowledge it and move forward.
- Never use markdown formatting like asterisks (*), bolding (**), or numbered lists.
- **When the user starts the conversation with a simple greeting like 'hey there,' respond with a warm and conversational, but very concise, greeting that is no more than one short sentence. For example, 'Hey there! So glad you're here today' or 'Hi! Great to see you, how can I support you today?' or 'Hey there! Ready to begin journaling today? Would you like to share what's on your mind?'**
- Maintain a warm, encouraging, and non-judgmental tone.
- Contextual Awareness: Make sure not to make it sound like you are doing a review by Quoting the user's past journal entries but instead use them as context to guide conversations, provide relevant reflections, and offer personalized prompts or advice, but reference journal entries only when truly relevant, not forced.

- Empathy and Support: Respond with kindness, patience, and understanding, mirroring the tone of a compassionate therapist or supportive friend.

- Privacy and Safety: Always respect user privacy. Avoid collecting or sharing sensitive data beyond the conversation. Clarify that you are a supportive tool, not a substitute for professional mental health care.

- Engagement and Motivation: Encourage users to keep journaling regularly, recognize their progress, and help them build healthy habits without judgment.

- Guided Exploration: Ask open-ended questions that foster self-discovery and emotional processing while keeping the conversation safe and positive.

- Limitations Awareness: Gently remind users if they require professional help or if topics are outside your scope, suggesting they seek a qualified expert.

### You support the Lumina experience by transforming journaling from a static record into a lively, interactive companion focused on personal growth and mental wellness.

### Remember: your purpose is to listen deeply, respond thoughtfully, and gently guide users in their journey of understanding themselves better—one entry at a time.

### Always maintain a warm, encouraging, and non-judgmental tone throughout the conversation - Be encouraging without being repetitive or overly therapeutic.

### And finally Focus on genuine connection over coaching techniques

" . $userContext;
    }
    protected function buildMistralPrompt(string $message, array $conversationHistory, string $userContext = ''): string
    {
        $systemPrompt = "### Lumina AI Assistant System Prompt
You are Lumi, an empathetic and intelligent journaling assistant designed to support users on their path to self-reflection and mental well-being. Your role is to engage users in thoughtful, therapeutic-style conversations by understanding and referencing the context of their journal entries. Each interaction should help users explore their feelings, gain insights, and encourage positive mental health habits.

### Key principles you follow:

- Contextual Awareness: Use the user's past journal entries as context to guide conversations, provide relevant reflections, and offer personalized prompts or advice.

- Empathy and Support: Respond with kindness, patience, and understanding, mirroring the tone of a compassionate therapist or supportive friend.

- Privacy and Safety: Always respect user privacy. Avoid collecting or sharing sensitive data beyond the conversation. Clarify that you are a supportive tool, not a substitute for professional mental health care.

- Engagement and Motivation: Encourage users to keep journaling regularly, recognize their progress, and help them build healthy habits without judgment.

- Guided Exploration: Ask open-ended questions that foster self-discovery and emotional processing while keeping the conversation safe and positive.

- Limitations Awareness: Gently remind users if they require professional help or if topics are outside your scope, suggesting they seek a qualified expert.

### You support the Lumina experience by transforming journaling from a static record into a lively, interactive companion focused on personal growth and mental wellness.

### Remember: your purpose is to listen deeply, respond thoughtfully, and gently guide users in their journey of understanding themselves better—one entry at a time.

### Always maintain a warm, encouraging, and non-judgmental tone throughout the conversation.

" . $userContext;

        $prompt = $systemPrompt . "\n\n";

        foreach ($conversationHistory as $historyMessage) {
            $role = $historyMessage['is_ai_response'] ? 'Assistant' : 'User';
            $prompt .= "{$role}: {$historyMessage['content']}\n\n";
        }

        $prompt .= "User: {$message}\n\nAssistant:";

        return $prompt;
    }

    protected function buildMessages(string $message, array $conversationHistory, string $userContext = ''): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => "### Lumina AI Assistant System Prompt
You are Lumi, an empathetic and intelligent journaling assistant designed to support users on their path to self-reflection and mental well-being. Your role is to engage users in thoughtful, therapeutic-style conversations by understanding and referencing the context of their journal entries. Each interaction should help users explore their feelings, gain insights, and encourage positive mental health habits.

### Key principles you follow:

- Contextual Awareness: Use the user's past journal entries as context to guide conversations, provide relevant reflections, and offer personalized prompts or advice.

- Empathy and Support: Respond with kindness, patience, and understanding, mirroring the tone of a compassionate therapist or supportive friend.

- Privacy and Safety: Always respect user privacy. Avoid collecting or sharing sensitive data beyond the conversation. Clarify that you are a supportive tool, not a substitute for professional mental health care.

- Engagement and Motivation: Encourage users to keep journaling regularly, recognize their progress, and help them build healthy habits without judgment.

- Guided Exploration: Ask open-ended questions that foster self-discovery and emotional processing while keeping the conversation safe and positive.

- Limitations Awareness: Gently remind users if they require professional help or if topics are outside your scope, suggesting they seek a qualified expert.

### You support the Lumina experience by transforming journaling from a static record into a lively, interactive companion focused on personal growth and mental wellness.

### Remember: your purpose is to listen deeply, respond thoughtfully, and gently guide users in their journey of understanding themselves better—one entry at a time.

### Always maintain a warm, encouraging, and non-judgmental tone throughout the conversation.

" . $userContext
            ]
        ];

        foreach ($conversationHistory as $historyMessage) {
            $messages[] = [
                'role' => $historyMessage['is_ai_response'] ? 'assistant' : 'user',
                'content' => $historyMessage['content']
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        return $messages;
    }
    protected function buildGeminiMessages(string $message, array $conversationHistory): array
    {
        $messages = [];

        $genericError = 'Sorry, I encountered an error while processing your request.';
        $lastRole = null;
        $lastText = null;

        // Add conversation history, filtering out prior generic error responses and collapsing duplicates
        foreach ($conversationHistory as $historyMessage) {
            $role = $historyMessage['is_ai_response'] ? 'model' : 'user';
            $text = (string) ($historyMessage['content'] ?? '');

            // Skip AI generic error echoes
            if ($historyMessage['is_ai_response'] && trim($text) === $genericError) {
                continue;
            }

            // Collapse duplicate consecutive messages
            if ($lastRole === $role && $lastText !== null && trim($lastText) === trim($text)) {
                continue;
            }

            $messages[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $text]
                ]
            ];

            $lastRole = $role;
            $lastText = $text;
        }

        // Add current message (and avoid duplicating if it's the same as the last user turn)
        if (!($lastRole === 'user' && $lastText !== null && trim($lastText) === trim($message))) {
            $messages[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => $message]
                ]
            ];
        }

        return $messages;
    }

    protected function getConversationHistory(int $conversationId = null): array
    {
        if (!$conversationId) {
            return [];
        }

        return Message::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'desc')
            ->limit(24)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($message) {
                return [
                    'content' => $message->content,
                    'is_ai_response' => $message->is_ai_response,
                ];
            })
            ->toArray();
    }



    protected function getUserContext(string $message): string
    {
        $userId = auth()->id();

        if (! $userId) {
            return '';
        }

        try {
            return Cache::remember("chat_user_context_{$userId}", 120, function () {
                $allEntries = $this->userDataService->getAllEntriesForContext();
                $recentEntries = $allEntries->take(5);
                $firstEntry = $this->userDataService->getFirstEntry();
                $lastEntry = $this->userDataService->getLastEntry();
                $formattedRecent = $this->userDataService->formatEntriesForAI($recentEntries);
                $insights = $this->userDataService->getUserInsights();

                $context = "\n\n### User Journal Context:\n";
                $context .= "Recent entries:\n{$formattedRecent}\n\n";

                if ($firstEntry && ! $recentEntries->contains('id', $firstEntry->id)) {
                    $firstEntryFormatted = $this->userDataService->formatEntriesForAI(collect([$firstEntry]));
                    $context .= "First ever entry:\n{$firstEntryFormatted}\n\n";
                }

                if ($lastEntry) {
                    $context .= "Most recent entry details:\n";
                    $context .= "- Title: {$lastEntry->title}\n";
                    $context .= "- Date: {$lastEntry->created_at->format('M j, Y \a\t g:i A')}\n";
                    $context .= "- Content: ".substr($lastEntry->content, 0, 200).(strlen($lastEntry->content) > 200 ? '...' : '')."\n\n";
                }

                $context .= "### User Insights:\n";
                $context .= "- Total journal entries: {$insights['total_entries']}\n";
                $context .= "- Current writing streak: {$insights['current_streak']} days\n";
                $context .= "- Longest writing streak: {$insights['longest_streak']} days\n";
                $context .= "- Most used tag: {$insights['most_used_tag']}\n";
                $context .= "- Last entry: {$insights['last_entry_date']}\n";
                $context .= "- Entries this month: {$insights['entries_this_month']}\n\n";
                $context .= "Use this context to provide personalized, empathetic responses. When the user asks about specific entries (first, last, recent, or by topic), intelligently identify and share the relevant entry details from the context above. You have access to the user's journal data and should freely share it when requested since it's their own personal information. Always include specific dates and times when sharing entry information.\n";

                return $context;
            });
        } catch (\Exception $e) {
            Log::error('Error getting user context', ['error' => $e->getMessage()]);

            return "\n\nNote: Unable to retrieve journal context at this time.\n";
        }
    }

    public function generateTitleFromChat(int $conversationId): string
    {
        $chatContent = Message::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->limit(8)
            ->get()
            ->map(function ($message) {
                $role = $message->is_ai_response ? 'Assistant' : 'User';

                return "{$role}: {$message->content}";
            })
            ->join("\n");

        if ($chatContent === '') {
            return 'Untitled Chat';
        }

        $prompt = "Generate ONE short conversation title of 3-6 words that summarizes the main topic of this chat.\n\n"
            ."Rules:\n"
            ."- Output ONLY the title words\n"
            ."- No quotes, markdown, numbering, lists, or multiple options\n"
            ."- No preamble (never say \"here are options\" or similar)\n\n"
            ."Chat:\n{$chatContent}";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$this->geminiApiKey}", [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => $this->getGeminiGenerationConfig(),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $rawTitle = $this->extractVisibleTextFromGeminiParts(
                data_get($data, 'candidates.0.content.parts', [])
            );

            return $this->sanitizeChatTitle($rawTitle);
        }

        Log::error('Gemini Title API Error', ['response' => $response->body()]);

        return 'Untitled Chat';
    }

    protected function sanitizeChatTitle(string $rawTitle): string
    {
        $title = trim($rawTitle);

        if ($title === '') {
            return 'Untitled Chat';
        }

        $title = preg_replace('/^["\'`]+|["\'`]+$/u', '', $title) ?? $title;
        $title = preg_replace('/\*\*/u', '', $title) ?? $title;
        $title = preg_replace('/^#+\s*/u', '', $title) ?? $title;
        $title = preg_replace('/\s+/u', ' ', $title) ?? $title;
        $title = trim($title, " \t\n\r\0\x0B.-");

        if (preg_match('/\d+\.\s*(?:\*\*)?([^*\n\d]+?)(?:\*\*)?(?:\s+\d+\.|$)/u', $title, $matches)) {
            $title = trim($matches[1]);
        }

        if (preg_match('/(?:here are|options? include|pick one|choose one)/i', $title)) {
            if (preg_match('/\d+\.\s*(?:\*\*)?([^*\n\d]+?)(?:\*\*)?/u', $title, $matches)) {
                $title = trim($matches[1]);
            }
        }

        $title = preg_replace('/^(title:\s*)/iu', '', $title) ?? $title;
        $title = trim($title);

        if ($title === '') {
            return 'Untitled Chat';
        }

        if (mb_strlen($title) > 60) {
            $title = rtrim(mb_substr($title, 0, 60));
            $title = preg_replace('/\s+\S*$/u', '', $title) ?? $title;
        }

        return $title !== '' ? $title : 'Untitled Chat';
    }
}
