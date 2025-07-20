<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class Notifications extends Component
{
    public $emailNotifications = false;
    public $pushNotifications = false;

    public function mount()
    {
        $this->emailNotifications = session('settings.emailNotifications', false);
        $this->pushNotifications = session('settings.pushNotifications', false);
    }

    public function updatedEmailNotifications($value)
    {
        session(['settings.emailNotifications' => $value]);
    }

    public function updatedPushNotifications($value)
    {
        session(['settings.pushNotifications' => $value]);
    }

    public function render()
    {
        return view('livewire.settings.notifications');
    }
}
