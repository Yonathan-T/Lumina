<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use Illuminate\Http\Request;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view("auth.register");
    }
    public function store()
    {

        request()->validate(
            [
                "name" => ["required", "string"],
                "email" => ["required", "email", "unique:users,email"],
                "password" => ['required', Password::min(6), 'confirmed'],
            ],
            [
                "password.confirmed" => "The confirmation password entered does not match the original password",
                "email.unique" => "There is a user with that email address already.",
            ]
        );
        $user = User::create([
            "name" => request("name"),
            "email" => request("email"),
            "password" => request("password"),
        ]);
        auth()->login($user);
        return redirect('/dashboard');

    }

}
