<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    /**
     * Display the email verification notice.
     */
    public function notice(Request $request)
    {
        if (Auth::check() && Auth::user()->hasVerifiedEmail()) {
            return redirect('/dashboard');
        }

        $email = $request->query('email') ?? (Auth::check() ? Auth::user()->email : session('unverified_email'));

        return view('auth.verify-email', [
            'email' => $email,
        ]);
    }

    /**
     * Mark the authenticated/specified user's email address as verified.
     */
    public function verify(Request $request, $id, $hash)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This verification link is invalid or has expired.');
        }

        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'This verification link is invalid.');
        }

        if ($user->hasVerifiedEmail()) {
            Auth::login($user);
            return redirect('/dashboard')->with('message', 'Your email address is already verified.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        Auth::login($user);

        return redirect('/dashboard')->with('message', '🎉 Your email address has been verified! Welcome to Lumina.');
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request)
    {
        $email = $request->input('email') ?? (Auth::check() ? Auth::user()->email : session('unverified_email'));

        if ($email) {
            $normalizedEmail = User::normalizeEmailValue((string) $email);
            $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();

            if ($user && ! $user->hasVerifiedEmail()) {
                $user->sendEmailVerificationNotification();
            }
        }

        return back()->with([
            'status' => 'verification-link-sent',
            'email' => $email,
        ]);
    }
}
