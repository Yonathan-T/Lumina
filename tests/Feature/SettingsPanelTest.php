<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_requires_authentication(): void
    {
        $response = $this->get('/settings');
        $response->assertRedirect('/auth/login');
    }

    public function test_authenticated_user_can_view_settings_and_subcomponents(): void
    {
        $user = User::factory()->create([
            'settings' => [
                'dark_mode' => false,
                'entry_font_size' => 16,
                'daily_reminder' => false,
                'streak_alerts' => false,
                'blog_updates' => false,
            ],
        ]);

        $response = $this->actingAs($user)->get('/settings');
        $response->assertStatus(200);
        $response->assertSeeLivewire('settings-panel');
        $response->assertSeeLivewire('settings.general');
        $response->assertSeeLivewire('settings.account-information');
        $response->assertSeeLivewire('settings.api-integration');
        $response->assertSeeLivewire('settings.data-privacy');
        $response->assertSeeLivewire('settings.subscription');
        $response->assertSeeLivewire('settings.appearance');
    }

    public function test_api_integration_component_mounts_cleanly(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\ApiIntegration::class)
            ->assertSet('status', 'idle')
            ->assertSet('hasSavedGeminiKey', false)
            ->assertSet('hasSavedElevenLabsKey', false);
    }

    public function test_save_button_is_disabled_when_key_is_already_saved_and_unchanged(): void
    {
        $user = User::factory()->create([
            'api_key' => \Illuminate\Support\Facades\Crypt::encryptString('saved-gemini-key'),
            'api_key_verified_at' => now(),
            'elevenlabs_api_key' => \Illuminate\Support\Facades\Crypt::encryptString('saved-elevenlabs-key'),
            'elevenlabs_api_key_verified_at' => now(),
        ]);

        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\ApiIntegration::class)
            ->assertSet('hasSavedGeminiKey', true)
            ->assertSet('hasSavedElevenLabsKey', true);

        $this->assertFalse($component->instance()->canSaveGeminiKey());
        $this->assertFalse($component->instance()->canSaveElevenLabsKey());

        // Changing the key to something new resets verification and still cannot save until tested
        $component->set('apiKey', 'new-untested-gemini-key');
        $this->assertFalse($component->instance()->canSaveGeminiKey());

        $component->set('elevenLabsKey', 'new-untested-elevenlabs-key');
        $this->assertFalse($component->instance()->canSaveElevenLabsKey());
    }

    public function test_user_can_select_and_persist_elevenlabs_voice(): void
    {
        $user = User::factory()->create();

        $this->assertEquals('EXAVITQu4vr4xnSDxMaL', $user->getElevenLabsVoiceId());

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\ApiIntegration::class)
            ->set('selectedVoiceId', 'pNInz6obpgDQGcFmaJgB') // Adam
            ->assertSet('voiceSavedMessage', 'Voice preference saved!');

        $user->refresh();
        $this->assertEquals('pNInz6obpgDQGcFmaJgB', $user->getElevenLabsVoiceId());
    }

    public function test_user_can_set_custom_elevenlabs_voice_id(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(\App\Livewire\Settings\ApiIntegration::class)
            ->set('selectedVoiceId', 'custom')
            ->set('customVoiceId', 'my-custom-cloned-voice-id')
            ->call('saveCustomVoice')
            ->assertSet('voiceSavedMessage', 'Voice preference saved!');

        $user->refresh();
        $this->assertEquals('my-custom-cloned-voice-id', $user->getElevenLabsVoiceId());
    }

    public function test_user_can_delete_account_and_all_associated_data(): void
    {
        $user = User::factory()->create([
            'email' => 'delete-me@example.com',
        ]);

        $entry = \App\Models\Entry::create([
            'user_id' => $user->id,
            'title' => 'Delete Me Note',
            'content' => 'Test content',
        ]);

        $convo = \App\Models\Conversation::create([
            'user_id' => $user->id,
            'title' => 'Test Chat',
            'type' => 'general',
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Livewire\Settings\DataPrivacy::class)
            ->call('deleteAccount')
            ->assertRedirect('/auth/register');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('entries', ['id' => $entry->id]);
        $this->assertDatabaseMissing('conversations', ['id' => $convo->id]);
        $this->assertGuest();
    }
}
