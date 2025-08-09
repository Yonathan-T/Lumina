<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;

class PasswordResetController extends Controller
{

    public function showLinkRequestForm()
    {
        //when implmenting the login forgot password feature, this will be the view for requesting a password reset link.also remember me token generation is good i think
        return view('auth.password.passwordRequest');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $response = Password::broker()->sendResetLink(
            $request->only('email')
        );

        return $response == Password::RESET_LINK_SENT
            ? back()->with('status', __($response))
            : back()->withErrors(['email' => __($response)]);
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.password.passwordReset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }


    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $response = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        if ($response == Password::PASSWORD_RESET) {
            return redirect('/auth/login')->with('status', __($response));
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => __($response)]);

    }
}