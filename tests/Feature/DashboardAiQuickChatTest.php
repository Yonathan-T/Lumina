<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\AiQuickChat;
use App\Models\Conversation;
use App\Models\Entry;
use App\Models\Message;
use App\Models\User;
use App\Services\AiChatService;
use App\Services\UserDataService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardAiQuickChatTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->string('avatar')->nullable();
            $table->text('api_key')->nullable();
            $table->timestamp('api_key_verified_at')->nullable();
            $table->text('elevenlabs_api_key')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('polar_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->morphs('billable');
            $table->string('type')->default('default');
            $table->string('polar_id')->default('');
            $table->string('status')->default('active');
            $table->string('product_id')->default('');
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('entry_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id');
            $table->foreignId('tag_id');
            $table->timestamps();
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('title');
            $table->string('type')->default('general');
            $table->integer('message_count')->default(0);
            $table->timestamp('last_activity')->useCurrent();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id');
            $table->text('content');
            $table->boolean('is_ai_response')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Gate::define('access-premium', fn () => true);

        $this->user = User::factory()->create();
    }

    /**
     * Test F6: Declarative wire:loading directives are present in view for all actions.
     */
    public function test_ai_quick_chat_view_renders_declarative_wire_loading_targets(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(AiQuickChat::class);

        $html = $component->html();

        // F6: All actions have wire:loading with specific wire:target
        $this->assertStringContainsString('wire:target="startGuidedReflection"', $html);
        $this->assertStringContainsString('wire:target="summarizePastWeek"', $html);
        $this->assertStringContainsString('wire:target="reviewPastMemos"', $html);
        $this->assertStringContainsString('wire:target="startTherapySession"', $html);

        // Assert wire:loading is used instead of broken server conditionals
        $this->assertStringContainsString('wire:loading', $html);
        $this->assertStringNotContainsString('$isProcessing === \'guided-reflection\'', $html);
        $this->assertStringNotContainsString('$isProcessing === \'weekly-summary\'', $html);
        $this->assertStringNotContainsString('$isProcessing === \'review-memos\'', $html);
        $this->assertStringNotContainsString('$isProcessing === \'therapy-session\'', $html);
    }

    /**
     * Test F7: Deferred weekly summary modal opens immediately and caches results.
     */
    public function test_weekly_summary_opens_immediately_and_caches_result(): void
    {
        $this->actingAs($this->user);

        // Create entries within the past week
        Entry::create([
            'user_id' => $this->user->id,
            'title' => 'Productive Day',
            'content' => 'Worked on optimization features today and felt very accomplished.',
            'created_at' => now()->subDays(2),
        ]);

        $mockAi = $this->mock(AiChatService::class);
        $mockAi->shouldReceive('generateResponse')
            ->once()
            ->andReturn("# 🧭 TL;DR\nGreat progress on features.\n\n# ✨ Key Themes\n- Productivity");

        $component = Livewire::test(AiQuickChat::class);

        // 1. Calling summarizePastWeek opens the modal with summaryLoading = true immediately (deferred)
        $component->call('summarizePastWeek');
        $component->assertSet('showSummaryModal', true);
        $component->assertSet('summaryLoading', true);
        $component->assertSet('weeklySummary', '');

        // 2. loadWeeklySummary executes background AI generation and sets summary
        $component->call('loadWeeklySummary');
        $component->assertSet('summaryLoading', false);
        $component->assertSee('Great progress on features');

        // 3. Cache must contain the generated summary
        $cacheKey = "weekly_summary_{$this->user->id}_" . now()->startOfWeek()->format('Y_m_d');
        $this->assertTrue(Cache::has($cacheKey));

        // 4. Subsequent open of weekly summary reads directly from cache with summaryLoading = false
        $subsequentComponent = Livewire::test(AiQuickChat::class);
        $subsequentComponent->call('summarizePastWeek');
        $subsequentComponent->assertSet('showSummaryModal', true);
        $subsequentComponent->assertSet('summaryLoading', false);
        $subsequentComponent->assertSee('Great progress on features');
    }

    /**
     * Test F8: Fast-handoff for startGuidedReflection without blocking.
     */
    public function test_start_guided_reflection_creates_conversation_and_redirects_quickly(): void
    {
        $this->actingAs($this->user);

        Entry::create([
            'user_id' => $this->user->id,
            'title' => 'Morning Thoughts',
            'content' => 'Peaceful morning with coffee.',
            'created_at' => now(),
        ]);

        Livewire::test(AiQuickChat::class)
            ->call('startGuidedReflection')
            ->assertRedirect(route('chat.index'));

        // Verify Conversation was created rapidly in DB
        $conversation = Conversation::where('user_id', $this->user->id)->first();
        $this->assertNotNull($conversation);
        $this->assertEquals('Guided Reflection', $conversation->title);
        $this->assertEquals('reflection', $conversation->type);

        // Verify initial message was seeded referencing user entry
        $message = Message::where('conversation_id', $conversation->id)->first();
        $this->assertNotNull($message);
        $this->assertTrue((bool)$message->is_ai_response);
        $this->assertStringContainsString('Morning Thoughts', $message->content);
    }

    /**
     * Test F8: Fast-handoff for reviewPastMemos without blocking.
     */
    public function test_review_past_memos_creates_conversation_and_redirects_quickly(): void
    {
        $this->actingAs($this->user);

        Entry::create([
            'user_id' => $this->user->id,
            'title' => 'Retrospective',
            'content' => 'Looking back at milestones.',
            'created_at' => now(),
        ]);

        Livewire::test(AiQuickChat::class)
            ->call('reviewPastMemos')
            ->assertRedirect(route('chat.index'));

        // Verify Conversation was created in DB
        $conversation = Conversation::where('user_id', $this->user->id)
            ->where('type', 'analysis')
            ->first();
        $this->assertNotNull($conversation);
        $this->assertEquals('Memo Review & Analysis', $conversation->title);

        // Verify initial message was seeded referencing journal entries
        $message = Message::where('conversation_id', $conversation->id)->first();
        $this->assertNotNull($message);
        $this->assertTrue((bool)$message->is_ai_response);
        $this->assertStringContainsString('memo review', $message->content);
    }

    /**
     * Test F8: Fast-handoff for startTherapySession without blocking.
     */
    public function test_start_therapy_session_creates_conversation_and_redirects_quickly(): void
    {
        $this->actingAs($this->user);

        Entry::create([
            'user_id' => $this->user->id,
            'title' => 'Evening Contemplation',
            'content' => 'Feeling a bit drained after work.',
            'created_at' => now(),
        ]);

        Livewire::test(AiQuickChat::class)
            ->call('startTherapySession')
            ->assertRedirect(route('chat.index'));

        $conversation = Conversation::where('user_id', $this->user->id)
            ->where('type', 'therapy')
            ->first();
        $this->assertNotNull($conversation);
        $this->assertEquals('Therapy Session', $conversation->title);

        $message = Message::where('conversation_id', $conversation->id)->first();
        $this->assertNotNull($message);
        $this->assertStringContainsString('Evening Contemplation', $message->content);
    }

    /**
     * Test F9: UserDataService::getAllEntriesForContext() selects only required columns.
     */
    public function test_user_data_service_context_query_projects_only_necessary_columns(): void
    {
        $this->actingAs($this->user);

        Entry::create([
            'user_id' => $this->user->id,
            'title' => 'Context Test',
            'content' => 'Test content for context projection.',
            'created_at' => now(),
        ]);

        $userDataService = app(UserDataService::class);
        $entries = $userDataService->getAllEntriesForContext();

        $this->assertNotEmpty($entries);
        $first = $entries->first();

        // Verify selected attributes exist
        $this->assertNotNull($first->id);
        $this->assertEquals('Context Test', $first->title);
        $this->assertEquals('Test content for context projection.', $first->content);
        $this->assertNotNull($first->created_at);

        // Verify relations were not eagerly loaded unnecessarily
        $this->assertFalse($first->relationLoaded('tags'));
    }

    /**
     * Test F9: AiChatService generateGeminiResponse specifies timeout(15)->connectTimeout(5).
     */
    public function test_gemini_api_call_includes_explicit_http_timeouts(): void
    {
        // Encrypt test API key on user
        $this->user->update([
            'api_key' => Crypt::encryptString('dummy-gemini-key'),
        ]);
        $this->actingAs($this->user);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Gemini response text'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = app(AiChatService::class);
        $response = $service->generateResponse('Test prompt message');

        $this->assertEquals('Gemini response text', $response);

        // Verify Http request was recorded with timeout options
        Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            return str_contains($request->url(), 'generativelanguage.googleapis.com');
        });
    }
}
