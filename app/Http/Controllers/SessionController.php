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
        User::normalizeStoredEmailRecord($normalizedEmail);

        request()->merge([
            'email' => $normalizedEmail,
        ]);

        // validate
        $attributes = request()->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = request()->filled('remember');

        if (! Auth::attempt($attributes, $remember)) {
            throw ValidationException::withMessages(
                ['email' => 'Sorry, those credentials do not match']
            );
        }
        request()->session()->regenerate();

        return redirect('/dashboard');
    }

    public function destroy()
    {
        Auth::logout();

        return redirect('/');
    }
}
