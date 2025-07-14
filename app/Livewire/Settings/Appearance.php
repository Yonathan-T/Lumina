<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class Appearance extends Component
{
    public $darkMode = false;
    public $fontSize = 'Large';

    public function mount()
    {
        // In a real app, load these from user settings in DB
        $this->darkMode = session('settings.darkMode', false);
        $this->fontSize = session('settings.fontSize', 'Large');
    }

    public function updatedDarkMode($value)
    {
        // In a real app, save to DB
        session(['settings.darkMode' => $value]);
    }

    public function updatedFontSize($value)
    {
        // In a real app, save to DB
        session(['settings.fontSize' => $value]);
    }

    public function render()
    {
        return view('livewire.settings.appearance');
    }
}
