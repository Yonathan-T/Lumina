<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            $normalizedEmail = User::normalizeEmailValue((string) $socialUser->getEmail());
            User::normalizeStoredEmailRecord($normalizedEmail);

            $user = User::where('provider_id', $socialUser->getId())
                ->where('provider', $provider)
                ->first();

            if ($user) {
                Auth::login($user);
            } else {
                $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();

                if ($user) {
                    $user->update([
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'avatar' => $socialUser->getAvatar(),
                    ]);
                } else {
                    $user = User::create([
                        'name' => $socialUser->getName() ?? $socialUser->getNickname(),
                        'email' => $normalizedEmail,
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'avatar' => $socialUser->getAvatar(),
                        'password' => bcrypt(str()->random(24)),
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
