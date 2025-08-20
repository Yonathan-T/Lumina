<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
    }

    public function generateResponse(string $message, int $conversationId = null): string
    {
        try {
            $conversationHistory = $this->getConversationHistory($conversationId);
            $messages = $this->buildMessages($message, $conversationHistory);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/chat/completions', [
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

        } catch (\Exception $e) {
            Log::error('AI Chat Service Error', ['error' => $e->getMessage()]);
            return 'Sorry, I encountered an error while processing your request.';
        }
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

        // Add conversation history
        foreach ($conversationHistory as $historyMessage) {
            $messages[] = [
                'role' => $historyMessage['is_ai_response'] ? 'assistant' : 'user',
                'content' => $historyMessage['content']
            ];
        }

        // current message BABYYY
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
