<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
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

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function test_registration_does_not_log_in_user_and_sends_verification_email(): void
    {
        Notification::fake();

        $response = $this->post('/auth/register', [
            'name' => 'Alice Wonder',
            'email' => 'alice@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ]);

        $this->assertGuest();

        $user = User::where('email', 'alice@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, CustomVerifyEmail::class);

        $response->assertSessionHas('status');
    }

    public function test_json_registration_returns_clean_success_message(): void
    {
        Notification::fake();

        $response = $this->postJson('/auth/register', [
            'name' => 'Alice Json',
            'email' => 'alice.json@example.com',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ]);

        $this->assertGuest();
        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertStringContainsString('Verification link sent to your email', $response->json('message'));
    }

    public function test_unverified_user_cannot_log_in_and_receives_validation_error(): void
    {
        $user = User::create([
            'name' => 'Bob Unverified',
            'email' => 'bob@example.com',
            'password' => Hash::make('Secret123!'),
            'email_verified_at' => null,
        ]);

        $response = $this->post('/auth/login', [
            'email' => 'bob@example.com',
            'password' => 'Secret123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
    }

    public function test_unverified_user_cannot_access_protected_dashboard(): void
    {
        $user = User::create([
            'name' => 'Charlie Unverified',
            'email' => 'charlie@example.com',
            'password' => Hash::make('Secret123!'),
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');
        $response->assertRedirect(route('verification.notice'));
    }

    public function test_valid_signed_verification_link_verifies_email_and_logs_in(): void
    {
        $user = User::create([
            'name' => 'Diana Verified',
            'email' => 'diana@example.com',
            'password' => Hash::make('Secret123!'),
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->get($verificationUrl);

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect('/dashboard');
    }

    public function test_resend_verification_email_dispatches_notification(): void
    {
        Notification::fake();

        $user = User::create([
            'name' => 'Eve Resend',
            'email' => 'eve@example.com',
            'password' => Hash::make('Secret123!'),
            'email_verified_at' => null,
        ]);

        $response = $this->post(route('verification.send'), [
            'email' => 'eve@example.com',
        ]);

        Notification::assertSentTo($user, CustomVerifyEmail::class);
        $response->assertSessionHas('status', 'verification-link-sent');
    }
}
