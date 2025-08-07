<?php

namespace App\Livewire\Settings;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Http\Request;

use Livewire\Component;

class AccountInformation extends Component
{
    public $name;
    public $email;

    public function mount()
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $user = auth()->user();
        abort_unless(auth()->check(), 403);

        // Check if the new email is already taken by another user
        $emailExists = \App\Models\User::where('email', $this->email)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($emailExists) {
            session()->flash('emailError', 'The email address is already in use by another account.');
            return;
        }

        $user->name = $this->name;
        $user->email = $this->email;
        $user->save();

        session()->flash('message', 'Account information updated successfully!');
    }
    public function forgotPassword()
    {
        $userEmail = Auth::user()->email;

        $request = new Request(['email' => $userEmail]);

        $passwordResetController = new PasswordResetController();

        $response = $passwordResetController->sendResetLinkEmail($request);

        if ($response->getSession()->has('status')) {
            session()->flash('status', $response->getSession()->get('status'));
        } elseif ($response->getSession()->has('errors')) {
            session()->flash('error', $response->getSession()->get('errors')->first('email'));
        }

        return back();
    }
    public function render()
    {
        return view('livewire.settings.account-information');
    }
}
