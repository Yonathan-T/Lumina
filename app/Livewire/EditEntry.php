<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Entry;
use App\Models\Tag;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ElevenLabsTTSService;

class EditEntry extends Component
{
    public Entry $entry;
    public $title;
    public $content;
    public $selectedTags = [];
    public $availableTags = [];
    public $newTag = '';
    public $tagError = null;
    public $isEditing = false;
    public $showDeleteModal = false;
    public $audioUrl = null;
    public $isGeneratingAudio = false;

    protected $rules = [
        'title' => 'required|string|min:3|max:255',
        'content' => 'required|string|min:10',
        'selectedTags' => 'array'
    ];

    public function mount(Entry $entry)
    {
        $this->entry = $entry;
        $this->title = $entry->title;
        $this->content = $entry->content;
        $this->selectedTags = $entry->tags->pluck('name')->toArray();
        $this->availableTags = Tag::whereHas('entries', function($query) {
            $query->where('user_id', auth()->id());
        })->get();
    }

    public function startEditing()
    {
        $this->isEditing = true;
    }

    public function cancelEditing()
    {
        $this->isEditing = false;
        $this->title = $this->entry->title;
        $this->content = $this->entry->content;
        $this->selectedTags = $this->entry->tags->pluck('name')->toArray();
        $this->tagError = null;
    }

    public function save()
    {
        $this->validate();

        preg_match_all('/(?<=\s|^)#([A-Za-z][\w]{1,30})/', $this->content, $matches);
        $tagsFromContent = array_map('strtolower', $matches[1]);

        $tagsFromInput = array_map(fn($tag) => ltrim(strtolower($tag), '#'), $this->selectedTags);

        $allTags = array_unique(array_merge($tagsFromContent, $tagsFromInput));

        $this->entry->update([
            'title' => $this->title,
            'content' => trim($this->content),
        ]);

        // tags should be synced so here we go
        $tagIds = [];
        foreach ($allTags as $tagName) {
            if (empty($tagName))
                continue;
            $tag = Tag::firstOrCreate([
                'slug' => Str::slug($tagName)
            ], [
                'name' => $tagName
            ]);
            $tagIds[] = $tag->id;
        }
        $this->entry->tags()->sync($tagIds);

        $this->isEditing = false;
        session()->flash('message', 'Entry updated successfully!');
    }

    public function showDeleteConfirmation()
    {
        $this->showDeleteModal = true;
    }

    public function hideDeleteConfirmation()
    {
        $this->showDeleteModal = false;
    }

    public function confirmDelete()
    {
        $this->entry->delete();
        session()->flash('message', 'Entry deleted successfully!');
        return redirect()->route('archive.entries');
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

    public function downloadPdf()
    {
        try {
            // Refresh the entry to get the latest data with tags
            $entry = Entry::with('tags')->findOrFail($this->entry->id);
            
            $pdf = Pdf::loadView('livewire.download-entry-pdf', [
                'entry' => $entry
            ]);

            $fileName = 'entry-' . Str::slug($entry->title) . '-' . date('Y-m-d') . '.pdf';
            
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $fileName);

        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());
            session()->flash('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    public function generateAudio()
    {
        $this->isGeneratingAudio = true;
        $this->audioUrl = null;

        try {
            $ttsService = new ElevenLabsTTSService();
            
            // Combine title and content for reading
            $textToRead = $this->entry->title . ". " . $this->entry->content;
            
            $this->audioUrl = $ttsService->generateAudio(
                $textToRead,
                'UgBBYS2sOqTuMpoF3BR0' // Default voice
            );

            if (!$this->audioUrl) {
                session()->flash('error', 'Failed to generate audio. This might be due to quota limits or API issues. Please try again later or check your ElevenLabs account.');
            } else {
                session()->flash('message', 'Audio generated successfully!');
            }

        } catch (\Exception $e) {
            \Log::error('Audio Generation Error: ' . $e->getMessage());
            session()->flash('error', 'Failed to generate audio: ' . $e->getMessage());
        }

        $this->isGeneratingAudio = false;
    }

    //copy pasted same messages from the new-entry form error so maybe this needs to be component ?
    public function messages()
    {
        return [
            'title.required' => 'Oops! You forgot the title, it\'s kind of important. 📛',
            'title.string' => "C'mon now, the title can't be a number or emoji salad. Make it a string! 📝",
            'title.min' => 'Too short! The title needs to be at least :min characters. Stretch it out a bit. 📏',
            'title.max' => 'Whoa there! That title\'s a bit too epic. Keep it under :max characters. ✂️',

            'content.required' => 'No content? No story. Fill in the blanks, please! 🕳️',
            'content.string' => 'The content should be words, not wizardry. Use a string! 🔮',
            'content.min' => 'Give us more to work with! Content must be at least :min characters. 🧱',
        ];
    }

    public function render()
    {
        return view('livewire.edit-entry');
    }
}