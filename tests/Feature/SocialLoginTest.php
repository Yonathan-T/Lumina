<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_mass_assigns_social_and_verification_attributes(): void
    {
        $user = User::create([
            'name' => 'Tester',
            'email' => 'social_tester@example.com',
            'password' => bcrypt('password123'),
            'provider' => 'google',
            'provider_id' => 'google-uid-12345',
            'avatar' => 'https://lh3.googleusercontent.com/avatar',
            'email_verified_at' => now(),
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-uid-12345',
            'avatar' => 'https://lh3.googleusercontent.com/avatar',
        ]);

        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasVerifiedEmail());
    }

    public function test_google_login_creates_verified_user_and_redirects_to_dashboard(): void
    {
        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getId')->andReturn('google-unique-987');
        $socialUser->shouldReceive('getEmail')->andReturn('googleuser@example.com');
        $socialUser->shouldReceive('getName')->andReturn('Google User');
        $socialUser->shouldReceive('getNickname')->andReturn(null);
        $socialUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/pic.jpg');

        $provider = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'googleuser@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('google', $user->provider);
        $this->assertEquals('google-unique-987', $user->provider_id);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertAuthenticatedAs($user);
    }

    public function test_existing_unverified_user_logging_in_via_google_gets_auto_verified(): void
    {
        $existing = User::create([
            'name' => 'Legacy User',
            'email' => 'legacy@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => null,
        ]);

        $this->assertFalse($existing->hasVerifiedEmail());

        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getId')->andReturn('google-legacy-111');
        $socialUser->shouldReceive('getEmail')->andReturn('legacy@example.com');
        $socialUser->shouldReceive('getName')->andReturn('Legacy User');
        $socialUser->shouldReceive('getNickname')->andReturn(null);
        $socialUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/new.jpg');

        $provider = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/dashboard');

        $existing->refresh();
        $this->assertTrue($existing->hasVerifiedEmail());
        $this->assertEquals('google', $existing->provider);
        $this->assertEquals('google-legacy-111', $existing->provider_id);
        $this->assertAuthenticatedAs($existing);
    }
}
