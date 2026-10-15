<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Entry;
use App\Models\Message;
use App\Models\Tag;
use App\Models\User;
use App\Services\AiChatService;
use App\Services\ElevenLabsTTSService;
use App\Services\UserDataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CoreFunctionalIntegrityAuditTest extends TestCase
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
            $table->timestamp('api_key_tested_at')->nullable();
            $table->text('elevenlabs_api_key')->nullable();
            $table->timestamp('elevenlabs_api_key_verified_at')->nullable();
            $table->timestamp('elevenlabs_api_key_tested_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('title');
            $table->text('content');
            $table->string('banner_path')->nullable();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
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
            $table->timestamps();
        });

        $this->user = User::factory()->create([
            'email' => 'auditor@example.com',
            'password' => Hash::make('SecretPassword123!'),
        ]);
    }

    /**
     * 1. Audit Auth: User authentication, password hashing, encryption accessors.
     */
    public function test_audit_auth_system_intact(): void
    {
        $this->assertTrue(Hash::check('SecretPassword123!', $this->user->password));

        // Test encryption and decryption of keys
        $testElevenKey = 'el_test_key_1234567890';
        $this->user->update([
            'elevenlabs_api_key' => Crypt::encryptString($testElevenKey),
        ]);

        $this->assertTrue($this->user->hasElevenLabsKey());
        $this->assertSame($testElevenKey, $this->user->getElevenLabsApiKey());
    }

    /**
     * 2. Audit Tags: Creation, assignment to entry, eager loading, and relationships.
     */
    public function test_audit_tag_assignment_and_relationships_intact(): void
    {
        $entry = Entry::create([
            'user_id' => $this->user->id,
            'title' => 'Integrity Audit Note',
            'content' => 'Verifying tag relationships in Memo-Mate',
        ]);

        $tag1 = Tag::create(['name' => 'forensics', 'slug' => 'forensics']);
        $tag2 = Tag::create(['name' => 'security', 'slug' => 'security']);

        $entry->tags()->attach([$tag1->id, $tag2->id]);

        $reloaded = Entry::with('tags')->find($entry->id);
        $this->assertCount(2, $reloaded->tags);
        $this->assertTrue($reloaded->tags->pluck('name')->contains('forensics'));
        $this->assertTrue($reloaded->tags->pluck('name')->contains('security'));
    }

    /**
     * 3. Audit PDF Export: Barryvdh\DomPDF generates valid PDF binary from template.
     */
    public function test_audit_pdf_export_functionality_intact(): void
    {
        $entry = Entry::create([
            'user_id' => $this->user->id,
            'title' => 'PDF Verification Title',
            'content' => 'Deep reflection content to be rendered as PDF document.',
        ]);

        $tag = Tag::create(['name' => 'audit', 'slug' => 'audit']);
        $entry->tags()->attach($tag->id);

        $entryWithTags = Entry::with('tags')->find($entry->id);

        $pdf = Pdf::loadView('livewire.download-entry-pdf', [
            'entry' => $entryWithTags,
        ]);

        $rawOutput = $pdf->output();

        $this->assertNotEmpty($rawOutput);
        $this->assertStringStartsWith('%PDF-', $rawOutput, 'Generated output must be a genuine PDF document starting with %PDF- header.');
        $this->assertGreaterThan(1000, strlen($rawOutput), 'PDF output must contain compiled PDF binary content.');
    }

    /**
     * 4. Audit ElevenLabs TTS Service: Instantiation, headers, model ID, error handling.
     */
    public function test_audit_elevenlabs_tts_service_intact(): void
    {
        $service = new ElevenLabsTTSService('fake_xi_api_key');
        $this->assertTrue($service->hasKey());

        // Test with mocked HTTP failing response
        Http::fake([
            'https://api.elevenlabs.io/v1/text-to-speech/*' => Http::response([
                'detail' => ['message' => 'Quota exceeded for test key'],
            ], 401),
        ]);

        $result = $service->generateAudio('Testing audio narration text');
        $this->assertNull($result);
        $this->assertStringContainsString('character quota has been reached', $service->getLastError());
    }

    /**
     * 5. Audit AI Chat Service: Prompt formatting, context formatting, and Gemini call.
     */
    public function test_audit_ai_chat_and_context_service_intact(): void
    {
        $userDataService = app(UserDataService::class);
        $this->actingAs($this->user);

        $entry = Entry::create([
            'user_id' => $this->user->id,
            'title' => 'Sample Context Entry',
            'content' => 'Reflecting on project integrity and performance.',
        ]);

        $formatted = $userDataService->formatEntriesForAI(collect([$entry]));
        $this->assertStringContainsString('Sample Context Entry', $formatted);
        $this->assertStringContainsString('Reflecting on project integrity', $formatted);

        $this->user->update([
            'api_key' => Crypt::encryptString('mock-gemini-key'),
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Authentic AI reflection response from Gemini.'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $aiService = app(AiChatService::class);
        $response = $aiService->generateResponse('Prompt message');

        $this->assertSame('Authentic AI reflection response from Gemini.', $response);
    }

    /**
     * 6. History Reflect: verifies missing key flashes error and valid key redirects to chat.
     */
    public function test_history_reflect_handles_missing_key_and_successful_redirect(): void
    {
        $this->user->update(['api_key' => null]);
        $this->actingAs($this->user->fresh());

        $entry = Entry::create([
            'user_id' => $this->user->id,
            'title' => 'History Reflection Test',
            'content' => 'Deep reflection on personal thoughts.',
        ]);

        // 1. Missing Gemini key: does not redirect, flashes clear error
        \Livewire\Livewire::test(\App\Livewire\History::class)
            ->call('Reflect', $entry->id)
            ->assertNoRedirect()
            ->assertSee('Gemini API key is required');

        // 2. With Gemini key: creates reflection conversation and redirects to chat
        $this->user->update([
            'api_key' => Crypt::encryptString('valid-gemini-key'),
        ]);
        $this->actingAs($this->user->fresh());

        \Livewire\Livewire::test(\App\Livewire\History::class)
            ->call('Reflect', $entry->id)
            ->assertRedirect();

        $conversation = Conversation::where('user_id', $this->user->id)->first();
        $this->assertNotNull($conversation);
        $this->assertEquals('reflection', $conversation->type);
    }

    /**
     * 7. Entry Reflection Missing Key: verifies NewEntry does not stay on thinking and displays missing key alert.
     */
    public function test_entry_reflection_shows_clear_missing_key_message(): void
    {
        $this->actingAs($this->user);

        // Without Gemini key: startEntryChat populates clear missing-key message
        $component = \Livewire\Livewire::test(\App\Livewire\NewEntry::class)
            ->set('savedEntryTitle', 'Test Entry')
            ->set('savedEntryContent', 'Test Content')
            ->call('startEntryChat')
            ->assertSet('showQuickChatModal', true);

        $messages = $component->get('quickChatMessages');
        $this->assertNotEmpty($messages);
        $this->assertTrue($messages[0]['isMissingKey'] ?? false);
        $this->assertStringContainsString('Gemini API key', $messages[0]['content']);

        // Sending message without key also displays clear missing-key message and stops loading
        $component->call('sendQuickChat', 'Can we reflect on this?')
            ->assertSet('quickChatLoading', false);

        $updatedMessages = $component->get('quickChatMessages');
        $lastAiMessage = end($updatedMessages);
        $this->assertStringContainsString('Gemini API key is missing', $lastAiMessage['content']);
    }

    /**
     * 8. History: Full card anchor, isolated Reflect action, and reactive 5-per-page pagination.
     */
    public function test_history_pagination_and_full_card_anchor(): void
    {
        $this->actingAs($this->user);

        // Create 26 entries to test 5-per-page pagination (e.g. Showing 1 to 5 of 26 results)
        for ($i = 1; $i <= 26; $i++) {
            Entry::create([
                'user_id' => $this->user->id,
                'title' => "Journal Entry #{$i}",
                'content' => "Reflecting on thoughts for entry number {$i}.",
                'created_at' => now()->subMinutes(30 - $i),
            ]);
        }

        $component = \Livewire\Livewire::test(\App\Livewire\History::class);

        // Verify default perPage is 5 and no perPage dropdown is present
        $component->assertSet('perPage', 5)
            ->assertDontSee('5 / page')
            ->assertDontSee('10 / page')
            ->assertDontSee('25 / page')
            ->assertSee('Newest First')
            ->assertSee('Oldest First');

        // Verify 5 entries rendered on page 1
        $viewEntries = $component->viewData('recentEntries');
        $this->assertCount(5, $viewEntries->items());
        $this->assertEquals(26, $viewEntries->total());

        // Verify full-card anchor and isolated Reflect button
        $firstEntry = $viewEntries->first();
        $component->assertSee(route('entries.show', $firstEntry))
            ->assertSee("wire:click.stop=\"Reflect({$firstEntry->id})\"", false);

        // Fast Livewire pagination to page 2 (without full page reload: Showing 6 to 10 of 26 results)
        $component->call('nextPage');

        $this->assertEquals(2, $component->instance()->getPage());

        $page2Entries = $component->viewData('recentEntries');
        $this->assertEquals(2, $page2Entries->currentPage());
        $this->assertCount(5, $page2Entries->items());
    }
}
