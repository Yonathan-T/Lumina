<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Appearance extends Component
{

    public $darkMode = false;
    public $fontSize = 16;

    public function mount()
    {
        $user = auth()->user();
        $settings = $user->settings ?? [];

        if (empty($settings)) {
            $settings = [
                'dark_mode' => false,
                'entry_font_size' => 16,
            ];
            $user->update(['settings' => $settings]);
        }

        $this->darkMode = $settings['dark_mode'] ?? false;
        $this->fontSize = $settings['entry_font_size'] ?? 16;
    }

    public function updatedDarkMode($value)
    {
        $this->updateSetting('dark_mode', $value);
    }

    public function updatedFontSize($value)
    {
        $this->updateSetting('entry_font_size', $value);
        
        $user = Auth::user()->fresh();
        Auth::setUser($user);
        
        $this->dispatch('font-size-changed', size: $value);
    }

    private function updateSetting($key, $value)
    {
        $user = auth()->user();
        $settings = $user->settings ?? [];
        $settings[$key] = $value;
        // Persist reliably regardless of mass-assignment config
        $user->forceFill(['settings' => $settings])->save();
    }

    public function render()
    {
        return view('livewire.settings.appearance');
    }
}
