<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Entry;

class History extends Component
{
    use WithPagination;

    public string $sort = 'newest';

    protected function queryString()
    {
        return [
            'sort' => ['except' => 'newest'],
            'page' => ['except' => 1],
        ];
    }

    public function updatedSort()
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

        return view('livewire.history', [
            'recentEntries' => $query->paginate(5)->withQueryString()
        ]);
    }
}
