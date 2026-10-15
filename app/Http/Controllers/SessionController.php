<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store()
    {
        $normalizedEmail = User::normalizeEmailValue((string) request('email'));

        request()->merge([
            'email' => $normalizedEmail,
        ]);

        $attributes = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = request()->filled('remember');

        if (! Auth::attempt($attributes, $remember)) {
            throw ValidationException::withMessages([
                'password' => 'These credentials do not match our records.',
            ]);
        }

        $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Please click the verification link inside your email (check spam) to complete your registration and unlock full access to your sanctuary.',
            ]);
        }

        request()->session()->regenerate();

        if (request()->wantsJson()) {
            return response()->json([
                'redirect' => '/dashboard',
            ]);
        }

        return redirect()->intended('/dashboard');
    }

    public function destroy()
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        if (request()->wantsJson()) {
            return response()->json([
                'redirect' => url('/'),
            ]);
        }

        return redirect('/');
    }
}
