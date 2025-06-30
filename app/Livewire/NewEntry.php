<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Entry;
use App\Models\Tag;
use Illuminate\Support\Str;

class NewEntry extends Component
{
    public $title = '';
    public $content = '';
    public $selectedTags = [];
    public $availableTags = [];

    public function mount()
    {
        $this->availableTags = Tag::all();
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|min:3|max:255',
            'content' => 'required|min:10',
            'selectedTags' => 'array'
        ]);

        $entry = Entry::create([
            'title' => $this->title,
            'content' => $this->content,
            'user_id' => auth()->id(),
            'slug' => Str::slug($this->title)
        ]);

        if (!empty($this->selectedTags)) {
            $entry->tags()->attach($this->selectedTags);
        }
        preg_match_all('/#(\w+)/', request()->content, $matches);
        $tags = array_unique(array_map('strtolower', $matches[1]));

        // --- Attach tags to entry ---
        foreach ($tags as $tagName) {
            $tag = Tag::firstOrCreate(['name' => $tagName]);
            $entry->tags()->attach($tag);
        }
        $this->reset(['title', 'content', 'selectedTags']);

        session()->flash('message', 'Entry created successfully!');
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.new-entry');
    }
}