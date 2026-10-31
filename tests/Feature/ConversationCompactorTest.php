<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\AiChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ConversationCompactorTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected AiChatService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'api_key' => Crypt::encryptString('test-api-key-gemini'),
        ]);

        $this->actingAs($this->user);
        $this->service = app(AiChatService::class);
    }

    public function test_conversation_below_threshold_returns_all_messages_verbatim(): void
    {
        $conversation = Conversation::create([
            'user_id' => $this->user->id,
            'title' => 'Short Chat',
        ]);

        for ($i = 1; $i <= 10; $i++) {
            Message::create([
                'conversation_id' => $conversation->id,
                'content' => "Message number {$i}",
                'is_ai_response' => ($i % 2 === 0),
            ]);
        }

        $context = $this->service->getConversationContextData($conversation->id);

        $this->assertCount(10, $context['history']);
        $this->assertNull($context['summary']);
        $this->assertNull($conversation->fresh()->summary);
    }

    public function test_conversation_at_or_above_threshold_compacts_older_messages_and_keeps_recent_verbatim(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'User and Lumi discussed goals, morning routines, and mindful habits.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $conversation = Conversation::create([
            'user_id' => $this->user->id,
            'title' => 'Long Chat',
        ]);

        $messages = [];
        for ($i = 1; $i <= 16; $i++) {
            $messages[] = Message::create([
                'conversation_id' => $conversation->id,
                'content' => "Message {$i} content about topic {$i}",
                'is_ai_response' => ($i % 2 === 0),
            ]);
        }

        $context = $this->service->getConversationContextData($conversation->id);

        // Verbatim window is last 8 messages
        $this->assertCount(8, $context['history']);
        $this->assertSame('Message 9 content about topic 9', $context['history'][0]['content']);
        $this->assertSame('Message 16 content about topic 16', $context['history'][7]['content']);

        // Summary was generated and saved
        $this->assertNotNull($context['summary']);
        $this->assertStringContainsString('mindful habits', $context['summary']);

        $refreshed = $conversation->fresh();
        $this->assertSame($context['summary'], $refreshed->summary);
        $this->assertSame($messages[7]->id, $refreshed->last_summarized_message_id);
    }

    public function test_system_prompt_includes_condensed_conversation_memory(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getGeminiSystemPrompt');
        $method->setAccessible(true);

        $promptWithoutSummary = $method->invoke($this->service, 'Current date: Tuesday', null);
        $this->assertStringNotContainsString('CONVERSATION MEMORY', $promptWithoutSummary);

        $summary = 'User previously shared that they are traveling to Japan in November.';
        $promptWithSummary = $method->invoke($this->service, 'Current date: Tuesday', $summary);

        $this->assertStringContainsString('CONVERSATION MEMORY', $promptWithSummary);
        $this->assertStringContainsString($summary, $promptWithSummary);
    }

    public function test_summary_is_reused_without_recompacting_until_sufficient_messages_accumulate(): void
    {
        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Initial compaction summary.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $conversation = Conversation::create([
            'user_id' => $this->user->id,
            'title' => 'Accumulation Chat',
        ]);

        for ($i = 1; $i <= 16; $i++) {
            Message::create([
                'conversation_id' => $conversation->id,
                'content' => "Msg {$i}",
                'is_ai_response' => ($i % 2 === 0),
            ]);
        }

        // First run triggers compaction
        $this->service->getConversationContextData($conversation->id);
        $this->assertSame(1, Http::recorded()->count());

        // Add 1 more message (total 17) -> only 1 new older message -> should reuse cached summary without calling API
        Message::create([
            'conversation_id' => $conversation->id,
            'content' => "Msg 17",
            'is_ai_response' => false,
        ]);

        $context2 = $this->service->getConversationContextData($conversation->id);
        $this->assertSame(1, Http::recorded()->count(), 'API should not be called again for just 1 incremental message');
        $this->assertSame('Initial compaction summary.', $context2['summary']);
    }
}
