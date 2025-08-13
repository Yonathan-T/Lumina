<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class Appearance extends Component
{

    public $darkMode = false;
    public $fontSize = 'Large';

    public function mount()
    {
        $user = auth()->user();
        $settings = $user->settings ?? [];

        // Initialize default settings if they don't exist
        if (empty($settings)) {
            $settings = [
                'dark_mode' => false,
            ];
            $user->update(['settings' => $settings]);
        }

        $this->darkMode = $settings['dark_mode'] ?? false;
        $this->fontSize = session('settings.fontSize', 'Large');
    }

    public function updatedDarkMode($value)
    {
        $this->updateSetting('dark_mode', $value);
    }
    private function updateSetting($key, $value)
    {
        $user = auth()->user();
        $settings = $user->settings ?? [];
        $settings[$key] = $value;
        $user->update(['settings' => $settings]);
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
