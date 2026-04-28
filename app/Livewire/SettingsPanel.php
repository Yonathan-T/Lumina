<?php

namespace App\Livewire;

use Livewire\Component;

class SettingsPanel extends Component
{
    public $activeTab = 'settings';

    public function mount(): void
    {
        $requestedTab = request()->query('tab', 'settings');
        $allowedTabs = ['settings', 'account', 'subscription', 'data', 'preference'];
        $this->activeTab = in_array($requestedTab, $allowedTabs, true) ? $requestedTab : 'settings';
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.settings-panel');
    }
}
