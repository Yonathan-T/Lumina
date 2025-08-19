<?php

namespace App\Services;

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

    public function generateResponse(string $message, array $conversationHistory = []): string
    {
        try {
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
                'content' => 'You are a helpful AI assistant for a journaling app called Memo Mate. You help users reflect on their thoughts and provide supportive guidance. Be empathetic, encouraging, and thoughtful in your responses.'
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
}
