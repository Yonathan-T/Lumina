<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    public function create()
    {
        return view("auth.login");
    }
    public function store()
    {
        //validate
        $attributes = request()->validate([
            "email" => ["required", "email"],
            "password" => ["required"],
        ]);

        $remember = request()->filled('remember');

        if (!Auth::attempt($attributes, $remember)) {
            throw ValidationException::withMessages(
                ["email" => "Sorry, those credentials do not match"]
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
