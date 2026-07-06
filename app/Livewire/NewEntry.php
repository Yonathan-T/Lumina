<?php

namespace App\Livewire;

use App\Models\Entry;
use App\Models\Tag;
use App\Services\EntrySearchService;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class NewEntry extends Component
{
    use WithFileUploads;

    public $title = '';

    public $content = '';

    public $banner;

    public $selectedTags = [];

    // Modal states
    public $showConfirmationModal = false;

    public $showQuickChatModal = false;

    public $savedEntry = null;

    // Quick chat properties
    public $quickChatMessages = [];

    public $quickChatInput = '';

    public $quickChatLoading = false;

    public function save()
    {
        $this->validate([
            'title' => 'required|string|min:3|max:255',
            'content' => 'required|string|min:10',
            'selectedTags' => 'array',
            'banner' => 'nullable|image|max:10240', // 10MB Max
        ]);

        preg_match_all('/(?<=\s|^)#([A-Za-z][\w]{1,30})/', $this->content, $matches);
        $tagsFromContent = array_map('strtolower', $matches[1]);

        $tagsFromInput = array_map(fn ($tag) => ltrim(strtolower($tag), '#'), $this->selectedTags);

        $allTags = array_unique(array_merge($tagsFromContent, $tagsFromInput));

        $bannerPath = null;
        if ($this->banner) {
            $bannerPath = $this->banner->store('banners', 'public');
        }

        $entry = Entry::create([
            'title' => $this->title,
            'content' => trim($this->content),
            'user_id' => auth()->id(),
            'banner_path' => $bannerPath,
        ]);

        foreach ($allTags as $tagName) {
            if (empty($tagName)) {
                continue;
            }
            $tag = Tag::firstOrCreate([
                'slug' => Str::slug($tagName),
            ], [
                'name' => $tagName,
            ]);
            $entry->tags()->attach($tag->id);
        }

        $entry->load('tags');

        // Rebuild the search index after the response is flushed so saving feels
        // instant — indexing can insert up to 500 token rows.
        dispatch(fn () => app(EntrySearchService::class)->updateEntryIndex($entry))->afterResponse();

        // Store the saved entry for potential chat context
        $this->savedEntry = $entry;

        $this->reset(['title', 'content', 'selectedTags']);

        // Show confirmation modal instead of redirecting immediately
        $this->showConfirmationModal = true;
    }

    /**
     * Handle "Yes, let's talk about it" button
     */
    public function startEntryChat()
    {
        $this->showConfirmationModal = false;
        $this->showQuickChatModal = true;

        // Initialize chat with context about the newly created entry
        $this->quickChatMessages = [
            [
                'sender' => 'ai',
                'content' => "I just read your entry about \"{$this->savedEntry->title}\". I'm here to help you reflect on it. What would you like to explore further?",
                'timestamp' => now()->format('g:i A'),
            ],
        ];
        $this->quickChatInput = '';
    }

    /**
     * Handle "Maybe later" button
     */
    public function goToDashboard()
    {
        session()->flash('message', 'Entry created successfully!');

        $this->redirect(route('dashboard'), navigate: true);
    }

    /**
     * Send message in quick chat with entry context
     */
    public function sendQuickChat()
    {
        if (empty(trim($this->quickChatInput))) {
            return;
        }

        $userMessage = trim($this->quickChatInput);

        $this->quickChatMessages[] = [
            'sender' => 'user',
            'content' => $userMessage,
            'timestamp' => now()->format('g:i A'),
        ];

        $this->quickChatInput = '';
        $this->quickChatLoading = true;

        try {
            // Include entry context in the AI prompt with clearer instructions
            $entryContext = "The user just wrote a journal entry. Here's the context for our conversation:\n";
            $entryContext .= "Title: {$this->savedEntry->title}\n";
            $entryContext .= "Content: {$this->savedEntry->content}\n\n";

            $prompt = $entryContext.'You are having a natural, supportive conversation about their journal entry. '
                .'Respond as if you are a therapist by reading their entry and you are genuinely interested in their thoughts. '
                .'Be warm, avoid generic responses, and ask specific and brilliant follow-up questions. '
                .'The user mentioned: '.$userMessage;

            $aiResponse = app(\App\Services\AiChatService::class)->generateResponse($prompt, null);

            $this->quickChatMessages[] = [
                'sender' => 'ai',
                'content' => $aiResponse,
                'timestamp' => now()->format('g:i A'),
            ];

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Entry Quick Chat Error: '.$e->getMessage());
            $this->quickChatMessages[] = [
                'sender' => 'ai',
                'content' => "I'm having trouble connecting right now. Please try again in a moment.",
                'timestamp' => now()->format('g:i A'),
            ];
        } finally {
            $this->quickChatLoading = false;
        }
    }

    /**
     * Close quick chat modal and redirect to dashboard
     */
    public function closeQuickChatModal()
    {
        $this->showQuickChatModal = false;
        $this->quickChatMessages = [];
        $this->quickChatInput = '';

        session()->flash('message', 'Entry created successfully!');

        $this->redirect(route('dashboard'), navigate: true);
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
            'banner.image' => 'The file must be an image. 🖼️',
            'banner.max' => 'The image is too large. Maximum size is 10MB. 📉',
        ];

    }

    public function render()
    {
        return view('livewire.new-entry');
    }
}
