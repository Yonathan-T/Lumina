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

    public $savedEntryId = null;
    public $savedEntryTitle = '';
    public $savedEntryContent = '';

    // Quick chat properties
    public $quickChatMessages = [];

    public $quickChatInput = '';

    public $quickChatLoading = false;

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'selectedTags' => 'array',
        ]);

        preg_match_all('/(?<=\s|^)#([A-Za-z][\w]{1,30})/', $this->content, $matches);
        $tagsFromContent = array_map('strtolower', $matches[1] ?? []);

        $tagsFromInput = array_map(fn ($tag) => ltrim(strtolower($tag), '#'), $this->selectedTags);

        $allTags = array_unique(array_merge($tagsFromContent, $tagsFromInput));

        $bannerPath = null;
        if ($this->banner) {
            try {
                $this->validate([
                    'banner' => 'image|max:10240', // 10MB Max
                ]);

                $disk = Entry::getStorageDisk();
                try {
                    $bannerPath = $this->banner->store('banners', $disk);
                } catch (\Throwable $e) {
                    \Log::warning('Banner upload failed on primary disk, trying public fallback: ' . $e->getMessage());
                    try {
                        $bannerPath = $this->banner->store('banners', 'public');
                    } catch (\Throwable $e2) {
                        \Log::error('Banner upload failed: ' . $e2->getMessage());
                        $bannerPath = null;
                    }
                }

                if (!empty($bannerPath) && method_exists($this->banner, 'delete')) {
                    try {
                        $this->banner->delete();
                    } catch (\Throwable $delEx) {
                        // Silent cleanup fail
                    }
                }
            } catch (\Illuminate\Validation\ValidationException $ve) {
                throw $ve;
            } catch (\Throwable $e) {
                \Log::warning('Banner processing encountered an issue: ' . $e->getMessage());
                $bannerPath = null;
                $this->banner = null;
            }
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

        // Rebuild the search index safely
        try {
            dispatch(fn () => app(EntrySearchService::class)->updateEntryIndex($entry))->afterResponse();
        } catch (\Throwable $e) {
            \Log::warning('Search index async dispatch failed, attempting direct sync: ' . $e->getMessage());
            try {
                app(EntrySearchService::class)->updateEntryIndex($entry);
            } catch (\Throwable $e2) {
                \Log::warning('Direct search index update failed: ' . $e2->getMessage());
            }
        }

        // Store lightweight scalar properties
        $this->savedEntryId = $entry->id;
        $this->savedEntryTitle = $entry->title;
        $this->savedEntryContent = $entry->content;

        $this->reset(['title', 'content', 'selectedTags', 'banner']);

        // Show confirmation modal
        $this->showConfirmationModal = true;
    }

    /**
     * Handle "Yes, let's talk about it" button
     */
    public function startEntryChat()
    {
        $this->showConfirmationModal = false;
        $this->showQuickChatModal = true;

        if (empty(auth()->user()->api_key)) {
            $this->quickChatMessages = [
                [
                    'sender' => 'ai',
                    'content' => "Welcome! To reflect on your journal entry with Lumi, please configure your personal Gemini API key in Settings > Account > API Integration.",
                    'timestamp' => now()->format('g:i A'),
                    'isMissingKey' => true,
                ],
            ];
            $this->quickChatInput = '';
            return;
        }

        // Initialize chat with context about the newly created entry
        $this->quickChatMessages = [
            [
                'sender' => 'ai',
                'content' => "I just read your entry about \"{$this->savedEntryTitle}\". I'm here to help you reflect on it. What would you like to explore further?",
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
    public function sendQuickChat($message = null)
    {
        $messageToSend = $message !== null && trim($message) !== '' ? trim($message) : trim($this->quickChatInput);

        if (empty($messageToSend)) {
            return;
        }

        $userMessage = $messageToSend;

        $this->quickChatMessages[] = [
            'sender' => 'user',
            'content' => $userMessage,
            'timestamp' => now()->format('g:i A'),
        ];

        $this->quickChatInput = '';

        if (empty(auth()->user()->api_key)) {
            $this->quickChatMessages[] = [
                'sender' => 'ai',
                'content' => "Gemini API key is missing. Please configure your key in Settings > Account > API Integration to reflect on your entries.",
                'timestamp' => now()->format('g:i A'),
                'isMissingKey' => true,
            ];
            $this->quickChatLoading = false;
            return;
        }

        $this->quickChatLoading = true;

        try {
            // Include entry context in the AI prompt with clearer instructions
            $entryContext = "The user just wrote a journal entry. Here's the context for our conversation:\n";
            $entryContext .= "Title: {$this->savedEntryTitle}\n";
            $entryContext .= "Content: {$this->savedEntryContent}\n\n";

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
            'title.max' => 'Whoa there! That title’s a bit too epic. Keep it under :max characters. ✂️',

            'content.required' => 'No content? No story. Fill in the blanks, please! 🕳️',
            'content.string' => 'The content should be words, not wizardry. Use a string! 🔮',
            'banner.image' => 'The file must be an image. 🖼️',
            'banner.max' => 'The image is too large. Maximum size is 10MB. 📉',
        ];
    }

    public function render()
    {
        return view('livewire.new-entry');
    }
}
