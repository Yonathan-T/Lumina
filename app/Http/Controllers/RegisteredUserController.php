<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store()
    {
        $normalizedEmail = User::normalizeEmailValue((string) request('email'));

        User::normalizeStoredEmailRecord($normalizedEmail);

        request()->merge([
            'email' => $normalizedEmail,
        ]);

        request()->validate(
            [
                'name' => ['required', 'string'],
                'email' => ['required', 'email'],
                'password' => ['required', Password::min(6), 'confirmed'],
            ],
            [
                'password.confirmed' => 'The confirmation password entered does not match the original password',
            ]
        );

        if (User::whereRaw('LOWER(email) = ?', [request('email')])->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'There is a user with that email address already.',
            ]);
        }

        $user = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'password' => request('password'),
        ]);

        $user->sendEmailVerificationNotification();
        event(new \Illuminate\Auth\Events\Registered($user));

        $message = 'Verification link sent to your email! Click the verification link inside the email (check spam) to complete your registration and unlock full access to your sanctuary.';

        session(['unverified_email' => $user->email]);
        session()->flash('status', $message);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);

    }
}
