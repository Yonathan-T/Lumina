<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    protected function resolveRedirectUrl(string $provider): string
    {
        $configured = config("services.{$provider}.redirect");

        if ($configured && (str_starts_with($configured, 'http://') || str_starts_with($configured, 'https://'))) {
            $configuredHost = parse_url($configured, PHP_URL_HOST);
            $currentHost = request()->getHost();

            if ($configuredHost === $currentHost ||
                (in_array($configuredHost, ['localhost', '127.0.0.1']) && in_array($currentHost, ['localhost', '127.0.0.1']))) {
                return $configured;
            }
        }

        return url("/auth/{$provider}/callback");
    }

    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)
            ->redirectUrl($this->resolveRedirectUrl($provider))
            ->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)
                ->redirectUrl($this->resolveRedirectUrl($provider))
                ->user();
            $normalizedEmail = User::normalizeEmailValue((string) $socialUser->getEmail());
            User::normalizeStoredEmailRecord($normalizedEmail);

            $user = User::where('provider_id', $socialUser->getId())
                ->where('provider', $provider)
                ->first();

            if ($user) {
                if (! $user->email_verified_at) {
                    $user->update(['email_verified_at' => now()]);
                }
                Auth::login($user);
            } else {
                $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();

                if ($user) {
                    $user->update([
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'avatar' => $socialUser->getAvatar(),
                        'email_verified_at' => $user->email_verified_at ?? now(),
                    ]);
                } else {
                    $user = User::create([
                        'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                        'email' => $normalizedEmail,
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'avatar' => $socialUser->getAvatar(),
                        'password' => bcrypt(str()->random(24)),
                        'email_verified_at' => now(),
                    ]);
                }

                Auth::login($user);
            }

            return redirect()->intended('/dashboard');

        } catch (Exception $e) {
            Log::error('Social login failed: '.$e->getMessage());

            return redirect('/login')->with('error', 'Login failed. Please try again.');
        }
    }
}
