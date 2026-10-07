<?php

namespace App\Livewire;

use App\Models\Entry;
use App\Models\Tag;
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

    public function showTagEntries($tagId)
    {
        $this->selectedTagId = $tagId;
        $tag = Tag::withCount(['entries' => function ($query) {
            $query->where('user_id', auth()->id());
        }])->find($tagId);
        $this->selectedTagName = $tag?->name;
        $this->selectedTagCount = $tag?->entries_count;
    }

    public function updatingSort()
    {
        $this->resetPage();
    }

    public function render()
    {
        $userId = auth()->id();

        $query = Tag::query()
            ->select(['tags.id', 'tags.name', 'tags.created_at'])
            ->whereHas('entries', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->withCount(['entries' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
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

        $tagEntries = collect();
        if ($this->selectedTagId) {
            $tag = Tag::find($this->selectedTagId);
            if ($tag && ! $this->selectedTagName) {
                $this->selectedTagName = $tag->name;
            }
            $tagEntries = $tag ? $tag->entries()
                ->where('user_id', $userId)
                ->select(['entries.id', 'entries.title', 'entries.content', 'entries.created_at', 'entries.user_id'])
                ->with(['tags:id,name'])
                ->latest('entries.created_at')
                ->get() : collect();
        }

        return view('livewire.tag-list', [
            'tagList' => $tagList,
            'tags' => $tagList,
            'tagEntries' => $tagEntries,
            'selectedTagId' => $this->selectedTagId,
            'selectedTagName' => $this->selectedTagName,
            'selectedTagCount' => $this->selectedTagCount,
        ]);
    }
}