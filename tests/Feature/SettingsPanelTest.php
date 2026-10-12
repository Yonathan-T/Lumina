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
}
