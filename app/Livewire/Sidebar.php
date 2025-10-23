<?php

namespace App\Livewire;

use App\Models\Entry;
use App\Models\Tag;
use Livewire\Component;

class Sidebar extends Component
{
    

    public function render()
    {
        return view('livewire.sidebar');
    }
}
