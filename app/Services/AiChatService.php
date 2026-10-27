<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Conversation;
use App\Services\UserDataService;
use App\Services\Ai\Tools\ToolRegistry;
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
    protected ToolRegistry $toolRegistry;
    protected array $executedTools = [];

    public function __construct()
    {
        $this->toolRegistry = new ToolRegistry();

        $user = auth()->user();

        $encryptedKey = $user->api_key ?? null;

        $this->geminiApiKey = null;

        if ($encryptedKey) {
            try {
                $this->geminiApiKey = Crypt::decryptString($encryptedKey);
            } catch (DecryptException $e) {
                Log::error("Failed to decrypt Gemini API key for user {$user->id}: " . $e->getMessage());
            }
        }

        $this->provider = config('services.ai.provider', 'gemini');
        // $this->openaiApiKey = config('services.openai.api_key');
        // $this->huggingfaceApiKey = config('services.huggingface.api_key');
        // $this->huggingfaceModel = config('services.huggingface.model');
        // $this->geminiApiKey = config('services.gemini.api_key');
        $this->userDataService = new UserDataService();
    }

    public function generateResponse(string $message, ?int $conversationId = null): string
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
    public function getExecutedTools(): array
    {
        return $this->executedTools;
    }

    public function streamResponse(
        string $message,
        ?int $conversationId = null,
        ?callable $onChunk = null,
        ?callable $onToolEvent = null
    ): string
    {
        $this->executedTools = [];
        $onChunk = $onChunk ?? static function (): void {};

        try {
            if ($this->provider === 'gemini' && $this->geminiApiKey) {
                $streamed = $this->streamGeminiResponse($message, $conversationId, $onChunk, $onToolEvent);
                if (!empty(trim($streamed))) {
                    return $streamed;
                }
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

    protected function streamGeminiResponse(
        string $message,
        ?int $conversationId = null,
        ?callable $onChunk = null,
        ?callable $onToolEvent = null,
        ?array $preparedMessages = null,
        int $depth = 0
    ): string
    {
        set_time_limit(180);

        $onChunk = $onChunk ?? static function (): void {};

        $conversationHistory = $this->getConversationHistory($conversationId);
        $userContext = $this->getUserContext($message);
        $systemPrompt = $this->getGeminiSystemPrompt($userContext);
        $messages = $preparedMessages ?? $this->buildGeminiMessages($message, $conversationHistory);

        $payload = $this->buildGeminiRequestPayload($messages, $systemPrompt);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->withOptions([
                    'stream' => true,
                ])
                ->timeout(120)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:streamGenerateContent?alt=sse&key={$this->geminiApiKey}", $payload);

        if (! $response->successful()) {
            // If gemini-2.5-flash hits rate limit / quota (429) or is unavailable (503), fall back to gemini-1.5-flash
            if ($response->status() === 429 || $response->status() === 503) {
                $fallbackResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->withOptions([
                    'stream' => true,
                ])
                ->timeout(120)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:streamGenerateContent?alt=sse&key={$this->geminiApiKey}", $payload);

                if ($fallbackResponse->successful()) {
                    $response = $fallbackResponse;
                }
            }
        }

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
        $detectedFunctionCall = null;

        while (! $stream->eof()) {
            $buffer .= $stream->read(2048);

            // Gemini's SSE stream delimits events with CRLF (\r\n\r\n). Normalize
            // to LF so the "\n\n" event delimiter below actually matches; without
            // this, no event is ever parsed and the response comes back empty.
            $buffer = str_replace("\r\n", "\n", $buffer);

            while (($delimiterPosition = strpos($buffer, "\n\n")) !== false) {
                $rawEvent = substr($buffer, 0, $delimiterPosition);
                $buffer = substr($buffer, $delimiterPosition + 2);

                if ($depth === 0) {
                    $fn = $this->extractFunctionCallFromGeminiStreamEvent($rawEvent);
                    if ($fn) {
                        $detectedFunctionCall = $fn;
                        break 2;
                    }
                }

                $delta = $this->extractDeltaFromGeminiStreamEvent($rawEvent, $fullResponse);

                if ($delta === '') {
                    continue;
                }

                $fullResponse .= $delta;
                $onChunk($delta);
            }
        }

        if ($detectedFunctionCall !== null && $depth < 2) {
            $toolName = (string) ($detectedFunctionCall['name'] ?? '');
            $toolArgs = (array) ($detectedFunctionCall['args'] ?? []);
            $user = auth()->user();

            if ($onToolEvent) {
                $onToolEvent([
                    'status' => 'running',
                    'tool' => $toolName,
                    'label' => $this->formatToolLabel($toolName, $toolArgs, 'running'),
                    'args' => $toolArgs,
                ]);
            }

            Log::info("Lumi executing tool '{$toolName}' in stream", ['args' => $toolArgs]);
            $toolResult = $user ? $this->toolRegistry->execute($toolName, $toolArgs, $user) : ['error' => 'Unauthenticated'];

            $completedToolEvent = [
                'status' => 'done',
                'tool' => $toolName,
                'label' => $this->formatToolLabel($toolName, $toolArgs, 'done', $toolResult),
                'args' => $toolArgs,
                'result' => $toolResult,
            ];
            $this->executedTools[] = $completedToolEvent;

            if ($onToolEvent) {
                $onToolEvent($completedToolEvent);
            }

            // Append model turn with functionCall
            $messages[] = [
                'role' => 'model',
                'parts' => [
                    ['functionCall' => $detectedFunctionCall],
                ],
            ];

            // Append user turn with functionResponse
            $messages[] = [
                'role' => 'user',
                'parts' => [
                    [
                        'functionResponse' => [
                            'name' => $toolName,
                            'response' => $toolResult,
                        ],
                    ],
                ],
            ];

            return $this->streamGeminiResponse($message, $conversationId, $onChunk, $onToolEvent, $messages, $depth + 1);
        }

        if (trim($buffer) !== '') {
            $delta = $this->extractDeltaFromGeminiStreamEvent($buffer, $fullResponse);

            if ($delta !== '') {
                $fullResponse .= $delta;
                $onChunk($delta);
            }
        }

        if ($fullResponse === '') {
            return '';
        }

        return $fullResponse;
    }

    protected function extractFunctionCallFromGeminiStreamEvent(string $rawEvent): ?array
    {
        $rawEvent = str_replace("\r\n", "\n", trim($rawEvent));
        if ($rawEvent === '') {
            return null;
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
            return null;
        }

        $payload = implode("\n", $dataLines);
        if ($payload === '[DONE]') {
            return null;
        }

        $decoded = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        $parts = data_get($decoded, 'candidates.0.content.parts', []);
        foreach ($parts as $part) {
            if (isset($part['functionCall']) && is_array($part['functionCall'])) {
                return $part['functionCall'];
            }
        }

        return null;
    }

    protected function formatToolLabel(string $tool, array $args, string $status, ?array $result = null): string
    {
        if ($tool === 'search_journal') {
            $q = trim((string) ($args['query'] ?? ''));
            if ($status === 'running') {
                return $q !== '' ? "Searching memories for \"{$q}\"..." : 'Searching memories...';
            }
            $count = (int) ($result['count'] ?? 0);
            return $count > 0
                ? "Searched memories for \"{$q}\" · {$count} " . ($count === 1 ? 'match' : 'matches')
                : "Searched memories for \"{$q}\" · No matches";
        }

        if ($tool === 'get_user_stats') {
            if ($status === 'running') {
                return 'Checking journaling streak & stats...';
            }
            return 'Checked writing streak and stats';
        }

        if ($tool === 'list_tags') {
            if ($status === 'running') {
                return 'Checking your journal tags...';
            }
            $count = (int) ($result['total_unique_tags'] ?? count($result['tags'] ?? []));
            return $count > 0
                ? "Checked journal tags · {$count} unique " . ($count === 1 ? 'tag' : 'tags')
                : 'Checked journal tags · No tags found';
        }

        if ($tool === 'draft_entry') {
            $title = trim((string) ($args['title'] ?? 'entry'));
            if ($status === 'running') {
                return "Saving memo \"{$title}\"...";
            }
            return "Saved memo \"{$title}\" to sanctuary";
        }

        return $status === 'running' ? "Running {$tool}..." : "Completed {$tool}";
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
        $payload = [
            'contents' => $messages,
            'systemInstruction' => [
                'parts' => [
                    ['text' => $systemPrompt],
                ],
            ],
            'generationConfig' => $this->getGeminiGenerationConfig(),
        ];

        $declarations = $this->toolRegistry->getGeminiDeclarations();
        if (! empty($declarations)) {
            $payload['tools'] = [
                [
                    'functionDeclarations' => $declarations,
                ],
            ];
        }

        return $payload;
    }

    protected function generateGeminiResponse(
        string $message,
        array $conversationHistory,
        string $userContext = '',
        ?array $preparedMessages = null,
        int $depth = 0
    ): string
    {
        $systemPrompt = $this->getGeminiSystemPrompt($userContext);
        $messages = $preparedMessages ?? $this->buildGeminiMessages($message, $conversationHistory);
        $payload = $this->buildGeminiRequestPayload($messages, $systemPrompt);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(30)->connectTimeout(10)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$this->geminiApiKey}", $payload);

        if (! $response->successful() && ($response->status() === 429 || $response->status() === 503)) {
            $fallbackResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->connectTimeout(10)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$this->geminiApiKey}", $payload);

            if ($fallbackResponse->successful()) {
                $response = $fallbackResponse;
            }
        }

        if ($response->successful()) {
            $data = $response->json();
            $parts = data_get($data, 'candidates.0.content.parts', []);

            // Check if model called a function
            if ($depth < 2) {
                $functionCallPart = collect($parts)->first(fn ($p) => isset($p['functionCall']));
                if ($functionCallPart) {
                    $functionCall = $functionCallPart['functionCall'];
                    $toolName = (string) ($functionCall['name'] ?? '');
                    $toolArgs = (array) ($functionCall['args'] ?? []);
                    $user = auth()->user();

                    Log::info("Lumi executing tool '{$toolName}' in non-stream", ['args' => $toolArgs]);
                    $toolResult = $user ? $this->toolRegistry->execute($toolName, $toolArgs, $user) : ['error' => 'Unauthenticated'];

                    $messages[] = [
                        'role' => 'model',
                        'parts' => [
                            ['functionCall' => $functionCall],
                        ],
                    ];

                    $messages[] = [
                        'role' => 'user',
                        'parts' => [
                            [
                                'functionResponse' => [
                                    'name' => $toolName,
                                    'response' => $toolResult,
                                ],
                            ],
                        ],
                    ];

                    return $this->generateGeminiResponse($message, $conversationHistory, $userContext, $messages, $depth + 1);
                }
            }

            $text = $this->extractVisibleTextFromGeminiParts($parts);

            return $text !== '' ? $text : 'Sorry, I could not generate a response.';
        }

        Log::error('Gemini API Error', ['status' => $response->status(), 'response' => $response->body()]);

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
        $userName = auth()->user() ? auth()->user()->name : 'the user';

        $prompt = <<<EOT
You are Lumi, a calm, grounded companion in Lumina (a personal sanctuary journal). You are in a reflective conversation with {$userName}.

Persona & Voice:
- Conversational, warm, authentic, and unhurried.
- Speak like a thoughtful, trusted friend over coffee—never like a clinical therapist, life coach, or corporate chatbot.
- Never use patronizing therapeutic filler ("I hear you", "That must be so heavy", "Thank you for sharing your vulnerability", "Let's sit with that feeling").
- Keep casual responses concise: 1–3 thoughtful sentences are often best. Never overwhelm the user with long lectures or bulleted lists unless explicitly asked.
- If the user shares something deep, listen attentively and ask at most one gentle, meaningful question to help them reflect.

Sanctuary Tools & Memory:
- You have tools to recall past memories, check writing streaks, or save new journal entries when asked.
- `search_journal`: Use when the user asks about past thoughts, memories, specific dates, or previous entries.
- `get_user_stats`: Use when the user asks about their streak, total entries, or journaling consistency.
- `list_tags`: Use when the user asks about their tags, how many tags they have, or what categories/topics they tag frequently.
- `draft_entry`: Persist and save a finalized journal entry into the user's sanctuary database.
  * CRITICAL RULES FOR SAVING ENTRIES:
    1. NEVER save a 3-word placeholder (like "Today was peaceful") when a user mentions a brief idea like "Save a memo about how peaceful today was". A journal entry must be a meaningful, thoughtful piece of writing.
    2. Collaborative Drafting: When a user wants to record an entry or convert their chat conversation into a journal memo:
       - Present a draft in your message first: propose a thoughtful title (or ask if they have a title in mind), provide a rich, well-written draft reflection capturing what was shared, and ask what tags they'd like (e.g. `peacefulday, Tuesday` or `no / skip`).
       - Example response:
         "Here is a draft for your journal:
         **Title:** A Quiet Sanctuary (or let me know what you'd like to call it)
         **Draft:** [Thoughtful, expressive reflection capturing the essence of what was discussed...]
         How does this feel? Would you like to adjust anything, give it your own title, or attach any tags?"
    3. Final Save: ONLY call the `draft_entry` tool when the user has approved the draft or provided final confirmation (e.g., "looks good, save it with tags X, Y", "yes save that", "skip tags").
    4. Direct Save Exception: If the user provides the full, ready-to-save text directly in their prompt (e.g., "Save an entry titled 'Morning Calm' with content 'Watched the mist rise over the hills...' and tags morning, calm"), call `draft_entry` immediately with their exact text.
- Only call tools when relevant to what the user asks; do not search unprompted during simple casual hellos.
EOT;

        if (! empty(trim($userContext))) {
            $prompt .= "\n\n" . trim($userContext);
        }

        return $prompt;
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

    protected function getConversationHistory(?int $conversationId = null): array
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
        $now = now()->format('l, F j, Y');

        return "Current date: {$now}.";
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
