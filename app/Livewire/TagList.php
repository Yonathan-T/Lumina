<?php

namespace App\Livewire;
use App\Models\Tag;
use App\Models\Entry;
use Livewire\Component;
use Livewire\WithPagination;

class TagList extends Component
{
    use WithPagination;

    public $sort = 'most';
    public $queryString = ['sort'];
    public $selectedTagName = null;
    public $selectedTagCount = null;
    public $selectedTagId = null;
    public $tagEntries = [];
    public function showTagEntries($tagId)
    {
        $this->selectedTagId = $tagId;
        $tag = Tag::withCount(['entries' => function($query) {
            $query->where('user_id', auth()->id());
        }])->find($tagId);
        $this->selectedTagName = $tag?->name;
        $this->selectedTagCount = $tag?->entries_count;
        $this->tagEntries = Entry::where('user_id', auth()->id())
            ->whereHas('tags', function ($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            })->latest()->get();
    }
    public function updatingSort()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Tag::withCount(['entries' => function($query) {
            $query->where('user_id', auth()->id());
        }]);

        switch ($this->sort) {
            case 'recent':
                $query->orderBy('created_at', 'desc');
                break;
            case 'alphabetic':
                $query->orderBy('name', 'asc');
                break;
            case 'most':
            default:
                $query->orderBy('entries_count', 'desc');
        }

        $tagList = $query->paginate(25);

        return view('livewire.tag-list', [
            'tagList' => $tagList,
            'selectedTagId' => $this->selectedTagId,
            'selectedTagName' => $this->selectedTagName,
            'tagEntries' => $this->tagEntries,
        ]);
    }
}