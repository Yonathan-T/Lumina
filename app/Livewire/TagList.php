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
        $this->selectedTagId = (int) $tagId;
    }

    public function Reflect($entryId)
    {
        try {
            $userDataService = app(\App\Services\UserDataService::class);
            $reflectionData = $userDataService->reflectOnEntry($entryId);

            if (! $reflectionData) {
                $this->dispatch('notify', message: 'Entry not found', type: 'error');
                return;
            }

            $conversation = \App\Models\Conversation::create([
                'user_id' => auth()->id(),
                'title' => $reflectionData['conversation_title'],
                'type' => 'reflection',
                'message_count' => 1,
                'last_activity' => now(),
            ]);

            $entry = $reflectionData['entry'];
            $titleSnippet = !empty($entry->title) ? " around \"{$entry->title}\"" : '';
            $initialMessage = "I've reviewed your journal entry{$titleSnippet}. Take a moment to settle in—how are you feeling about what you wrote, and what would you like to reflect on together?";

            \App\Models\Message::create([
                'conversation_id' => $conversation->id,
                'content' => $initialMessage,
                'is_ai_response' => true,
            ]);

            return $this->redirect(route('chat.index', ['conversation' => $conversation->id]), navigate: true);
        } catch (\Exception $e) {
            \Log::error('Reflection Error: '.$e->getMessage());
            $this->dispatch('notify', message: 'Unable to start reflection. Please try again.', type: 'error');
        }
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
            $tag = Tag::select(['id', 'name'])->find($this->selectedTagId);
            if ($tag) {
                $this->selectedTagName = $tag->name;
                $tagEntries = $tag->entries()
                    ->where('user_id', $userId)
                    ->select(['entries.id', 'entries.title', 'entries.content', 'entries.created_at', 'entries.user_id'])
                    ->with(['tags:id,name'])
                    ->latest('entries.created_at')
                    ->get();
                $this->selectedTagCount = $tagEntries->count();
            }
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