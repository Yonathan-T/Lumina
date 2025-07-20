<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class DataPrivacy extends Component
{
    public $showDeleteConfirm = false;
    public $exported = false;

    public function exportData()
    {
        // Simulate export
        $this->exported = true;
        session()->flash('message', 'Your data export is being prepared.');
    }

    public function confirmDelete()
    {
        $this->showDeleteConfirm = true;
    }

    public function deleteAccount()
    {
        // Simulate account deletion
        $this->showDeleteConfirm = false;
        session()->flash('message', 'Your account has been deleted (simulation).');
    }

    public function render()
    {
        return view('livewire.settings.data-privacy');
    }
}
