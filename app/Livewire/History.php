<?php

namespace App\Livewire;
use App\Models\Entry;
use Livewire\Component;
use Livewire\WithPagination;
class History extends Component
{
    use WithPagination;
    public $sort = 'newest';
    public function updatingSort()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Entry::with('tags');

        switch ($this->sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'longest':
                $query->orderByRaw('LENGTH(content) DESC');
                break;
            case 'shortest':
                $query->orderByRaw('LENGTH(content) ASC');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
        }

        $recentEntries = $query->paginate(5);

        return view('livewire.history', [
            'recentEntries' => $recentEntries
        ]);
    }
}