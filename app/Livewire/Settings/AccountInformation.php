<?php

namespace App\Livewire\Settings;

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
        $user->name = $this->name;
        $user->email = $this->email;
        $user->save();

        session()->flash('message', 'Account information updated successfully!');
    }

    public function render()
    {
        return view('livewire.settings.account-information');
    }
}
