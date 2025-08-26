<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Conversation;
use App\Services\UserDataService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $this->provider = config('services.ai.provider', 'huggingface');
        $this->openaiApiKey = config('services.openai.api_key');
        $this->huggingfaceApiKey = config('services.huggingface.api_key');
        $this->huggingfaceModel = config('services.huggingface.model');
        $this->geminiApiKey = config('services.gemini.api_key');
        $this->userDataService = new UserDataService();
    }

    public function generateResponse(string $message, int $conversationId = null): string
    {
        try {
            $conversationHistory = $this->getConversationHistory($conversationId);
            $userContext = $this->getUserContext($message);

            if ($this->provider === 'openai' && !$this->openaiApiKey) {
                return $this->generateFallbackResponse($message);
            }

            if ($this->provider === 'huggingface' && !$this->huggingfaceApiKey) {
                return $this->generateFallbackResponse($message);
            }

            if ($this->provider === 'gemini' && !$this->geminiApiKey) {
                return $this->generateFallbackResponse($message);
            }

            if ($this->provider === 'openai') {
                return $this->generateOpenAIResponse($message, $conversationHistory, $userContext);
            } elseif ($this->provider === 'huggingface') {
                return $this->generateHuggingFaceResponse($message, $conversationHistory, $userContext);
            } elseif ($this->provider === 'gemini') {
                return $this->generateGeminiResponse($message, $conversationHistory, $userContext);
            }

            return 'Sorry, the configured AI provider is not supported.';

        } catch (\Exception $e) {
            Log::error('AI Chat Service Error', ['error' => $e->getMessage()]);
            return 'Sorry, I encountered an error while processing your request.';
        }
    }

    protected function generateGeminiResponse(string $message, array $conversationHistory, string $userContext = ''): string
    {
        $systemPrompt = "### Lumina AI Assistant System Prompt
You are Lumi, Lumi is the short form of Lumina, a platform dedicated to self-reflection and mental well-being. you Lumi are an empathetic and intelligent journaling assistant designed to support users on their path to self-reflection and mental well-being. Your role is to engage users in thoughtful, therapeutic-style conversations by understanding and referencing the context of their journal entries. Each interaction should help users explore their feelings, gain insights, and encourage positive mental health habits.
If the user asks if you remember, you will acknowledge the conversation and proceed. You will not admit to being an AI with a lack of memory.
### Key principles you follow:

- Respond as if you were a real, empathetic friend.
- Use simple, human-like language.
- Ask open-ended questions to encourage the user to share more.
- Never use markdown formatting like asterisks (*), bolding (**), or numbered lists.
- **When the user starts the conversation with a simple greeting like 'hey there,' respond with a warm and conversational, but very concise, greeting that is no more than one short sentence. For example, 'Hey there! So glad you're here today' or 'Hi! Great to see you, how can I support you today?' or 'Hey there! Ready to begin journaling today? Would you like to share what's on your mind?'**
- Maintain a warm, encouraging, and non-judgmental tone.

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
        #endregion
        $messages = $this->buildGeminiMessages($message, $conversationHistory);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$this->geminiApiKey}", [
                    'system_instruction' => [
                        'parts' => [
                            ['text' => $systemPrompt]
                        ]
                    ],
                    'contents' => $messages,
                ]);

        if ($response->successful()) {
            $data = $response->json();
            \Log::info('Gemini Response', ['data' => $data]);
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I could not generate a response.';
        }

        \Log::error('Gemini API Error', ['response' => $response->body()]);
        return 'Sorry, I encountered an error while processing your request.';
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
    protected function generateFallbackResponse(string $message): string
    {
        return "I'm having trouble accessing my full capabilities right now. Please try again shortly — I want to give you the thoughtful response you deserve.";

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

        // Add conversation history
        foreach ($conversationHistory as $historyMessage) {
            $role = $historyMessage['is_ai_response'] ? 'model' : 'user';
            $messages[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $historyMessage['content']]
                ]
            ];
        }

        // Add current message
        $messages[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $message]
            ]
        ];

        \Log::info(json_encode($messages));
        return $messages;
    }

    protected function getConversationHistory(int $conversationId = null): array
    {
        if (!$conversationId) {
            return [];
        }

        return Message::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->get()
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
        try {
            // Get a broader set of entries for the AI to work with
            $allEntries = $this->userDataService->getAllEntriesForContext();
            $recentEntries = $allEntries->take(10); // Most recent 10 entries
            $firstEntry = $this->userDataService->getFirstEntry();
            $lastEntry = $this->userDataService->getLastEntry();

            // Format entries for AI context
            $formattedRecent = $this->userDataService->formatEntriesForAI($recentEntries);

            $insights = $this->userDataService->getUserInsights();

            $context = "\n\n### User Journal Context:\n";
            $context .= "Recent entries:\n{$formattedRecent}\n\n";

            // Add first entry info if it exists and is different from recent entries
            if ($firstEntry && !$recentEntries->contains('id', $firstEntry->id)) {
                $firstEntryFormatted = $this->userDataService->formatEntriesForAI(collect([$firstEntry]));
                $context .= "First ever entry:\n{$firstEntryFormatted}\n\n";
            }

            // Add explicit most recent entry info for clarity
            if ($lastEntry) {
                $context .= "Most recent entry details:\n";
                $context .= "- Title: {$lastEntry->title}\n";
                $context .= "- Date: {$lastEntry->created_at->format('M j, Y \a\t g:i A')}\n";
                $context .= "- Content: " . substr($lastEntry->content, 0, 200) . (strlen($lastEntry->content) > 200 ? '...' : '') . "\n\n";
            }

            $context .= "### User Insights:\n";
            $context .= "- Total journal entries: {$insights['total_entries']}\n";
            $context .= "- Current writing streak: {$insights['current_streak']} days\n";
            $context .= "- Longest writing streak: {$insights['longest_streak']} days\n";
            $context .= "- Most used tag: {$insights['most_used_tag']}\n";
            $context .= "- Last entry: {$insights['last_entry_date']}\n";
            $context .= "- Entries this month: {$insights['entries_this_month']}\n\n";
            $context .= "Use this context to provide personalized, empathetic responses. When the user asks about specific entries (first, last, recent, or by topic), intelligently identify and share the relevant entry details from the context above. You have access to the user's journal data and should freely share it when requested since it's their own personal information. Always include specific dates and times when sharing entry information.\n";

            // Add detailed logging for debugging
            \Log::info('Context data for AI:', [
                'recent_entries_count' => $recentEntries->count(),
                'first_entry_id' => $firstEntry ? $firstEntry->id : null,
                'first_entry_title' => $firstEntry ? $firstEntry->title : null,
                'first_entry_date' => $firstEntry ? $firstEntry->created_at->format('M j, Y \a\t g:i A') : null,
                'last_entry_id' => $lastEntry ? $lastEntry->id : null,
                'last_entry_title' => $lastEntry ? $lastEntry->title : null,
                'last_entry_date' => $lastEntry ? $lastEntry->created_at->format('M j, Y \a\t g:i A') : null,
                'recent_first_entry_date' => $recentEntries->first() ? $recentEntries->first()->created_at->format('M j, Y \a\t g:i A') : null
            ]);
            return $context;
        } catch (\Exception $e) {
            Log::error('Error getting user context', ['error' => $e->getMessage()]);
            return "\n\nNote: Unable to retrieve journal context at this time.\n";
        }
    }

    public function generateTitleFromChat(string $chatContent): string
    {
        $prompt = "Create a concise title (3-6 words) that captures the main topic of this journal chat conversation. Focus on the primary subjects discussed, not on pleasantries or brief responses like 'thanks'. Chat content: " . $chatContent;

        \Log::info('Generating title with prompt', ['prompt' => substr($prompt, 0, 200) . '...']);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$this->geminiApiKey}", [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Untitled Chat';
        }

        \Log::error('Gemini Title API Error', ['response' => $response->body()]);
        return 'Untitled Chat';
    }
}
