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
        $this->assertStringContainsString('Quota exceeded for test key', $service->getLastError());
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
}
