<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class Appearance extends Component
{
    public $darkMode = false;
    public $fontSize = 'Large';

    public function mount()
    {
        $this->darkMode = session('settings.darkMode', false);
        $this->fontSize = session('settings.fontSize', 'Large');
    }

    public function updatedDarkMode($value)
    {
        session(['settings.darkMode' => $value]);
    }

    public function updatedFontSize($value)
    {
        session(['settings.fontSize' => $value]);
    }

    public function render()
    {
        return view('livewire.settings.appearance');
    }
}
