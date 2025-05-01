<?php

namespace App\Livewire;

use App\Models\Entry;
use App\Models\Tag;
use Livewire\Component;

class Sidebar extends Component
{
    public $selectedEntryId;
    public $showNewMemoForm = false;

    // New properties
    public $isEditing = false;
    public $editedTitle;
    public $editedContent;
    public $editedTags = '';

    public function selectEntry($id)
    {
        $this->selectedEntryId = $id;
        $this->isEditing = false; 
    }

    public function openNewMemoForm()
    {
        $this->showNewMemoForm = true;
    }

    public function editEntry()
    {
        $entry = Entry::find($this->selectedEntryId);
        if ($entry) {
            $this->editedTitle = $entry->title;
            $this->editedContent = $entry->content;
            $this->editedTags = $entry->tags->pluck('name')->implode(', '); // Join tags into a string
            $this->isEditing = true;
        }
    }

    public function saveEntry()
    {
        $entry = Entry::find($this->selectedEntryId);
        if ($entry) {
            $this->validate([
                'editedTitle' => 'required|string|max:255',
                'editedContent' => 'required|string',
            ]);
    
            $entry->title = $this->editedTitle;
            $entry->content = $this->editedContent;
            $entry->save();
    
            // Sync tags
            $tagNames = collect(explode(',', $this->editedTags))
                             ->map(fn($tag) => strtolower(trim($tag)))
                            ->filter(); // remove empty values
    
            $tagIds = [];
            foreach ($tagNames as $name) {
                $tag = Tag::firstOrCreate(['name' => $name]);
                $tagIds[] = $tag->id;
            }
            $entry->tags()->sync($tagIds);
    
            $this->isEditing = false;
        }
    }
    

    public function render()
    {
        $selectedEntry = $this->selectedEntryId ? Entry::find($this->selectedEntryId) : null;

        return view('livewire.sidebar', [
            'entries' => auth()->user()->entries()->latest()->get(),
            'tags' => auth()->user()->entries()->with('tags')->get()->flatMap->tags,
            'selectedEntry' => $selectedEntry,
            'showNewMemoForm' => $this->showNewMemoForm
        ]);
    }
}
