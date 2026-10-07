<?php

namespace Tests\Feature;

use App\Livewire\EditEntry;
use App\Models\Entry;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class EditEntryModalTest extends TestCase
{
    protected User $user;
    protected Entry $entry;

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

        Gate::define('access-premium', fn () => true);

        $this->user = User::factory()->create();
        $this->entry = Entry::create([
            'user_id' => $this->user->id,
            'title' => 'My Personal Reflection',
            'content' => 'Deep thoughts and experiences recorded in Memo-Mate journal.',
        ]);
    }

    /**
     * Test EditEntry component mounts and renders successfully.
     */
    public function test_edit_entry_mounts_and_renders_successfully(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(EditEntry::class, ['entry' => $this->entry]);

        $component->assertOk();
        $component->assertSee('My Personal Reflection');
    }

    /**
     * Test both modals are enclosed inside the root Alpine component container.
     */
    public function test_both_modals_are_enclosed_within_root_alpine_component(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(EditEntry::class, ['entry' => $this->entry]);
        $html = $component->html();

        // 1. Root element has x-data with Alpine state
        $this->assertStringContainsString('showDeleteModal: false', $html);
        $this->assertStringContainsString('showMissingVoiceKey', $html);

        // 2. Both modals exist in the rendered output
        $this->assertStringContainsString('x-show="showDeleteModal"', $html);
        $this->assertStringContainsString('x-show="showMissingVoiceKey"', $html);

        // 3. Confirm Delete modal and Missing Voice Key modal are enclosed within root element
        // In valid single-root Livewire HTML, all content including both modals must precede the final closing </div>.
        $deleteModalPos = strpos($html, 'x-show="showDeleteModal"');
        $voiceKeyModalPos = strpos($html, 'x-show="showMissingVoiceKey"');
        $lastClosingDivPos = strrpos($html, '</div>');

        $this->assertNotFalse($deleteModalPos, 'Delete modal should be present');
        $this->assertNotFalse($voiceKeyModalPos, 'Missing voice key modal should be present');
        $this->assertNotFalse($lastClosingDivPos, 'Closing root div should be present');

        $this->assertLessThan($lastClosingDivPos, $deleteModalPos, 'Delete modal must be inside root container before final closing div');
        $this->assertLessThan($lastClosingDivPos, $voiceKeyModalPos, 'Missing voice key modal must be inside root container before final closing div');

        // 4. Trace rendered HTML depth: verify that before both modals, depth is at least 1 (root container is open)
        // and does NOT drop to 0 prematurely.
        preg_match_all('/<\/?div(?:\s[^>]*)?>/', $html, $matches, PREG_OFFSET_CAPTURE);
        $depth = 0;
        $deleteModalDepth = null;
        $voiceKeyModalDepth = null;

        foreach ($matches[0] as $idx => [$token, $offset]) {
            if (str_starts_with($token, '</div')) {
                $depth--;
            } else {
                $depth++;
                if (str_contains($token, 'x-show="showDeleteModal"')) {
                    $deleteModalDepth = $depth;
                }
                if (str_contains($token, 'x-show="showMissingVoiceKey"')) {
                    $voiceKeyModalDepth = $depth;
                }
            }

            // Between root element and the final closing tag, depth must never drop to 0
            if ($idx > 0 && $idx < count($matches[0]) - 1) {
                $this->assertGreaterThanOrEqual(1, $depth, "HTML nesting depth prematurely dropped to 0 at token $idx, indicating premature root closure");
            }
        }

        $this->assertEquals(0, $depth, 'Final HTML nesting depth must be 0');
        $this->assertNotNull($deleteModalDepth, 'Delete modal must be found in tokens');
        $this->assertNotNull($voiceKeyModalDepth, 'Voice key modal must be found in tokens');
        $this->assertGreaterThanOrEqual(2, $deleteModalDepth, 'Delete modal must be nested inside root container (depth >= 2)');
        $this->assertGreaterThanOrEqual(2, $voiceKeyModalDepth, 'Voice key modal must be nested inside root container (depth >= 2)');
    }

    /**
     * Test blade template tag balance: exactly 59 opening and 59 closing div tags, with zero premature closures.
     */
    public function test_blade_template_has_exact_tag_balance_and_proper_nesting(): void
    {
        $filePath = resource_path('views/livewire/edit-entry.blade.php');
        $this->assertFileExists($filePath);

        $content = file_get_contents($filePath);

        // Opening <div count (either followed by whitespace or '>')
        $openCount = preg_match_all('/<div(?:\s|>)/', $content);
        // Closing </div> count
        $closeCount = preg_match_all('/<\/div>/', $content);

        $this->assertEquals(59, $openCount, 'Expected exactly 59 opening <div tags in edit-entry.blade.php');
        $this->assertEquals(59, $closeCount, 'Expected exactly 59 closing </div> tags in edit-entry.blade.php');
        $this->assertEquals($openCount, $closeCount, 'Opening and closing div tags must be perfectly balanced');

        // Check sequential nesting depth across the file
        preg_match_all('/<\/?div(?:\s[^>]*)?>/', $content, $matches, PREG_OFFSET_CAPTURE);

        $depth = 0;
        $deleteModalTokenIndex = null;
        $voiceKeyModalTokenIndex = null;

        foreach ($matches[0] as $idx => [$token, $offset]) {
            if (str_starts_with($token, '</div')) {
                $depth--;
            } else {
                $depth++;
                if (str_contains($token, 'x-show="showDeleteModal"')) {
                    $deleteModalTokenIndex = $idx;
                    // When delete modal opens, depth must be 2 (inside root container which is depth 1)
                    $this->assertEquals(2, $depth, 'Delete modal should open at depth 2 (inside root element)');
                }
                if (str_contains($token, 'x-show="showMissingVoiceKey"')) {
                    $voiceKeyModalTokenIndex = $idx;
                    // When missing voice key modal opens, depth must be 2 (inside root container which is depth 1)
                    $this->assertEquals(2, $depth, 'Missing voice key modal should open at depth 2 (inside root element)');
                }
            }

            // Depth should never drop below 1 until the very last closing tag
            if ($idx < count($matches[0]) - 1) {
                $this->assertGreaterThanOrEqual(1, $depth, "Tag nesting depth dropped below 1 at token $idx (premature root closure)");
            }
        }

        // Final depth must be exactly 0
        $this->assertEquals(0, $depth, 'Final tag nesting depth must be 0');
        $this->assertNotNull($deleteModalTokenIndex);
        $this->assertNotNull($voiceKeyModalTokenIndex);
    }

    /**
     * Test confirmDelete action deletes entry and redirects.
     */
    public function test_confirm_delete_removes_entry_and_redirects(): void
    {
        $this->actingAs($this->user);

        Livewire::test(EditEntry::class, ['entry' => $this->entry])
            ->call('confirmDelete')
            ->assertRedirect(route('archive.entries'));

        $this->assertDatabaseMissing('entries', ['id' => $this->entry->id]);
    }

    /**
     * Test generateAudio without ElevenLabs key triggers missing voice key modal flag.
     */
    public function test_generate_audio_without_elevenlabs_key_triggers_modal_flag(): void
    {
        config(['services.elevenlabs.key' => null]);
        $this->actingAs($this->user);

        Livewire::test(EditEntry::class, ['entry' => $this->entry])
            ->call('generateAudio')
            ->assertSet('showMissingElevenLabsModal', true);
    }
}
