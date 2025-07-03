<?php

namespace App\Livewire;
use App\Models\Entry;
use Livewire\Component;

class History extends Component
{


    public function render()
    {

        $recentEntries = Entry::with('tags')->orderByDesc('created_at')->paginate(5);
        return view('livewire.history', [
            'recentEntries' => $recentEntries
        ]);
    }
}
