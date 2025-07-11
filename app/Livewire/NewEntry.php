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

    public $newTag = '';
    public $tagError = null;

    public function mount()
    {
        $this->availableTags = Tag::all();
    }
    // public function updatedTitle($value)
    // {
    //     $this->validate([
    //         'title' => 'required|string|min:3|max:255',
    //     ]);
    // }


    public function save()
    {
        $this->validate([
            'title' => 'required|string|min:3|max:255',
            'content' => 'required|string|min:10',
            'selectedTags' => 'array'
        ]);

        preg_match_all('/(?<=\s|^)#([A-Za-z][\w]{1,30})/', $this->content, $matches);
        $tagsFromContent = array_map('strtolower', $matches[1]);

        $tagsFromInput = array_map(fn($tag) => ltrim(strtolower($tag), '#'), $this->selectedTags);


        $allTags = array_unique(array_merge($tagsFromContent, $tagsFromInput));

        $entry = Entry::create([
            'title' => $this->title,
            'content' => trim($this->content),
            'user_id' => auth()->id(),
        ]);

        foreach ($allTags as $tagName) {
            if (empty($tagName))
                continue;
            $tag = Tag::firstOrCreate([
                'slug' => Str::slug($tagName)
            ], [
                'name' => $tagName
            ]);
            $entry->tags()->attach($tag->id);
        }

        $this->reset(['title', 'content', 'selectedTags']);

        session()->flash('message', 'Entry created successfully!');
        return redirect()->route('dashboard');
    }

    public function addTag()
    {
        $tag = trim($this->newTag);

        if ($tag === '') {
            $this->tagError = "Tag cannot be empty.";
            return;
        }
        if (preg_match('/\\s/', $tag)) {
            $this->tagError = "Tags cannot contain spaces.";
            return;
        }
        if (in_array(strtolower($tag), array_map('strtolower', $this->selectedTags))) {
            $this->tagError = "You already added this tag.";
            return;
        }

        $this->selectedTags[] = $tag;
        $this->newTag = '';
        $this->tagError = null;
    }

    public function removeTag($tag)
    {
        $this->selectedTags = array_filter($this->selectedTags, function ($t) use ($tag) {
            return strtolower($t) !== strtolower($tag);
        });
        $this->selectedTags = array_values($this->selectedTags);
    }
    public function messages()
    {
        return [
            'title.required' => 'Oops! You forgot the title, it’s kind of important. 📛',
            'title.string' => "C'mon now, the title can't be a number or emoji salad. Make it a string! 📝",
            'title.min' => 'Too short! The title needs to be at least :min characters. Stretch it out a bit. 📏',
            'title.max' => 'Whoa there! That title’s a bit too epic. Keep it under :max characters. ✂️',

            'content.required' => 'No content? No story. Fill in the blanks, please! 🕳️',
            'content.string' => 'The content should be words, not wizardry. Use a string! 🔮',
            'content.min' => 'Give us more to work with! Content must be at least :min characters. 🧱',
        ];

    }

    public function render()
    {
        return view('livewire.new-entry');
    }
}