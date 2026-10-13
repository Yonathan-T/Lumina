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

        request()->session()->regenerate();

        if (request()->wantsJson()) {
            return response()->json([
                'redirect' => redirect()->intended('/dashboard')->getTargetUrl(),
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
