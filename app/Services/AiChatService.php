<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    protected $provider;
    protected $openaiApiKey;
    protected $huggingfaceApiKey;
    protected $huggingfaceModel;

    public function __construct()
    {
        $this->provider = config('services.ai.provider', 'huggingface');
        $this->openaiApiKey = config('services.openai.api_key');
        $this->huggingfaceApiKey = config('services.huggingface.api_key');
        $this->huggingfaceModel = config('services.huggingface.model');
    }

    public function generateResponse(string $message, int $conversationId = null): string
    {
        try {
            $conversationHistory = $this->getConversationHistory($conversationId);

            // Check if API keys are configured
            if ($this->provider === 'openai' && !$this->openaiApiKey) {
                return $this->generateFallbackResponse($message);
            }

            if ($this->provider === 'huggingface' && !$this->huggingfaceApiKey) {
                return $this->generateFallbackResponse($message);
            }

            if ($this->provider === 'openai') {
                return $this->generateOpenAIResponse($message, $conversationHistory);
            } else {
                return $this->generateHuggingFaceResponse($message, $conversationHistory);
            }
        } catch (\Exception $e) {
            Log::error('AI Chat Service Error', ['error' => $e->getMessage()]);
            return 'Sorry, I encountered an error while processing your request.';
        }
    }



    protected function generateOpenAIResponse(string $message, array $conversationHistory): string
    {
        $messages = $this->buildMessages($message, $conversationHistory);

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

    protected function generateHuggingFaceResponse(string $message, array $conversationHistory): string
    {
        // Build the prompt for text generation style models
        $prompt = $this->buildMistralPrompt($message, $conversationHistory);

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
        $responses = [
            'stress' => "I can hear that you're dealing with stress right now. From your recent journal entries, I've noticed you often mention feeling overwhelmed during busy periods. What specific situations or thoughts tend to trigger these feelings for you?",
            'work' => "Work-related challenges can really impact our well-being. I see in your previous entries that you've mentioned work stress before. Have you noticed any patterns in when these feelings are strongest?",
            'anxiety' => "Anxiety can be really difficult to manage. Based on your journal patterns, it seems like you experience this more during certain times or situations. Would it help to explore some coping strategies that align with what you've found helpful before?",
            'mood' => "I've been analyzing your mood patterns from your recent entries. You seem to have more positive days when you mention activities like exercise or spending time outdoors. How are you feeling about incorporating more of these activities?",
            'sleep' => "Sleep issues can affect so many aspects of our lives. Looking at your journal entries, I notice you mention sleep troubles correlating with stressful periods. What does your current bedtime routine look like?",
            'hello' => "Hello! I'm here to help you explore your thoughts and feelings. What's on your mind today?",
            'how are you' => "Thank you for asking! I'm here and ready to listen. More importantly, how are you feeling today? What would you like to talk about?",
            'default' => "Thank you for sharing that with me. I can see from your recent journal entries that you've been reflecting on similar themes. What would be most helpful for you to explore right now? (Note: AI API not configured - using fallback responses)"
        ];

        $message = strtolower($message);

        foreach ($responses as $keyword => $response) {
            if (str_contains($message, $keyword)) {
                return $response;
            }
        }

        return $responses['default'];
    }
    protected function buildMistralPrompt(string $message, array $conversationHistory): string
    {
        $systemPrompt = "### Memo-Mate AI Assistant System Prompt
You are Memo-Mate AI, an empathetic and intelligent journaling assistant designed to support users on their path to self-reflection and mental well-being. Your role is to engage users in thoughtful, therapeutic-style conversations by understanding and referencing the context of their journal entries. Each interaction should help users explore their feelings, gain insights, and encourage positive mental health habits.

### Key principles you follow:

- Contextual Awareness: Use the user's past journal entries as context to guide conversations, provide relevant reflections, and offer personalized prompts or advice.

- Empathy and Support: Respond with kindness, patience, and understanding, mirroring the tone of a compassionate therapist or supportive friend.

- Privacy and Safety: Always respect user privacy. Avoid collecting or sharing sensitive data beyond the conversation. Clarify that you are a supportive tool, not a substitute for professional mental health care.

- Engagement and Motivation: Encourage users to keep journaling regularly, recognize their progress, and help them build healthy habits without judgment.

- Guided Exploration: Ask open-ended questions that foster self-discovery and emotional processing while keeping the conversation safe and positive.

- Limitations Awareness: Gently remind users if they require professional help or if topics are outside your scope, suggesting they seek a qualified expert.

### You support the Memo-Mate experience by transforming journaling from a static record into a lively, interactive companion focused on personal growth and mental wellness.

### Remember: your purpose is to listen deeply, respond thoughtfully, and gently guide users in their journey of understanding themselves better—one entry at a time.

### Always maintain a warm, encouraging, and non-judgmental tone throughout the conversation.

";

        $prompt = $systemPrompt . "\n\n";

        foreach ($conversationHistory as $historyMessage) {
            $role = $historyMessage['is_ai_response'] ? 'Assistant' : 'User';
            $prompt .= "{$role}: {$historyMessage['content']}\n\n";
        }

        $prompt .= "User: {$message}\n\nAssistant:";

        return $prompt;
    }

    protected function buildMessages(string $message, array $conversationHistory): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' =>
                    `
        ### Memo-Mate AI Assistant System Prompt
        You are Memo-Mate AI, an empathetic and intelligent journaling assistant designed to support users on their path to self-reflection and mental well-being. Your role is to engage users in thoughtful, therapeutic-style conversations by understanding and referencing the context of their journal entries. Each interaction should help users explore their feelings, gain insights, and encourage positive mental health habits.

### Key principles you follow:

- Contextual Awareness: Use the user’s past journal entries as context to guide conversations, provide relevant reflections, and offer personalized prompts or advice.

- Empathy and Support: Respond with kindness, patience, and understanding, mirroring the tone of a compassionate therapist or supportive friend.

- Privacy and Safety: Always respect user privacy. Avoid collecting or sharing sensitive data beyond the conversation. Clarify that you are a supportive tool, not a substitute for professional mental health care.

- Engagement and Motivation: Encourage users to keep journaling regularly, recognize their progress, and help them build healthy habits without judgment.

- Guided Exploration: Ask open-ended questions that foster self-discovery and emotional processing while keeping the conversation safe and positive.

- Limitations Awareness: Gently remind users if they require professional help or if topics are outside your scope, suggesting they seek a qualified expert.

### You support the Memo-Mate experience by transforming journaling from a static record into a lively, interactive companion focused on personal growth and mental wellness.

### Remember: your purpose is to listen deeply, respond thoughtfully, and gently guide users in their journey of understanding themselves better—one entry at a time.

### Always maintain a warm, encouraging, and non-judgmental tone throughout the conversation.
                `
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
}
