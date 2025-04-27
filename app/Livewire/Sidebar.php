<?php

namespace App\Livewire;

use App\Models\Entry;
use Livewire\Component;

class Sidebar extends Component
{
    public $selectedEntryId; // Property to store the selected entry ID

    public function selectEntry($id)
    {
        // Update the selected entry ID when a button is clicked
        $this->selectedEntryId = $id;
    }

    public function render()
    {
        // Fetch the selected entry if the ID is set
        $selectedEntry = $this->selectedEntryId ? Entry::find($this->selectedEntryId) : null;

        return view('livewire.sidebar', [
            'entries' => auth()->user()->entries,
            'selectedEntry' => $selectedEntry, // Pass the selected entry to the view
        ]);
    }
}
