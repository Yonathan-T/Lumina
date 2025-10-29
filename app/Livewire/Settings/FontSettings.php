<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class FontSettings extends Component
{
    public $selectedFont = 'inter';

    public function mount()
    {
        $user = Auth::user();
        $settings = $user->settings ?? [];
        $this->selectedFont = $settings['entry_font'] ?? 'inter';
    }

    public function updateFont($font)
    {
        $this->selectedFont = $font;
        
        $user = Auth::user();
        $settings = $user->settings ?? [];
        $settings['entry_font'] = $font;
        // Persist reliably regardless of mass-assignment config
        $user->forceFill(['settings' => $settings])->save();
        
        // Refresh the user to get the latest settings
        $freshUser = $user->fresh();
        Auth::setUser($freshUser);
    }

    public function render()
    {
        return view('livewire.settings.font-settings');
    }
}