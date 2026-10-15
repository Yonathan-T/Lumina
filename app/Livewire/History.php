<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Entry;
use App\Models\Message;
use App\Services\AiChatService;
use App\Services\UserDataService;
use Livewire\Component;
use Livewire\WithPagination;

class History extends Component
{
    use WithPagination;

    public $sort = 'newest';

    public $perPage = 5;

    public $queryString = ['sort'];

    public ?string $isProcessing = null;

    public ?int $processingEntryId = null;

    public function Reflect($entryId)
    {
        $this->isProcessing = 'reflection';
        $this->processingEntryId = $entryId;
        try {
            if (empty(auth()->user()->api_key)) {
                $this->isProcessing = null;
                $this->processingEntryId = null;
                session()->flash('error', 'Gemini API key is required to start an AI reflection session. Please add your key in Settings → Account.');
                return;
            }

            $userDataService = app(UserDataService::class);
            $reflectionData = $userDataService->reflectOnEntry($entryId);

            if (! $reflectionData) {
                $this->isProcessing = null;
                $this->processingEntryId = null;
                session()->flash('error', 'Entry not found');

                return;
            }

            // Create a new conversation for this reflection
            $conversation = Conversation::create([
                'user_id' => auth()->id(),
                'title' => $reflectionData['conversation_title'],
                'type' => 'reflection',
                'message_count' => 1,
                'last_activity' => now(),
            ]);

            $entry = $reflectionData['entry'];
            $titleSnippet = !empty($entry->title) ? " around \"{$entry->title}\"" : '';
            $initialMessage = "I've reviewed your journal entry{$titleSnippet}. Take a moment to settle in—how are you feeling about what you wrote, and what would you like to reflect on together?";

            // Save the AI's response as the initial message
            Message::create([
                'conversation_id' => $conversation->id,
                'content' => $initialMessage,
                'is_ai_response' => true,
            ]);

            $this->isProcessing = null;
            $this->processingEntryId = null;

            // Redirect immediately to the chat interface
            return $this->redirect(route('chat.index', ['conversation' => $conversation->id]), navigate: true);

        } catch (\Exception $e) {
            \Log::error('Reflection Error: '.$e->getMessage());
            $this->isProcessing = null;
            $this->processingEntryId = null;
            session()->flash('error', 'Unable to start reflection. Please try again.');
        }
    }

    public function updatingSort()
    {
        $userId = auth()->id();
        $total = \Illuminate\Support\Facades\Cache::remember(
            "user_{$userId}_entries_count",
            60,
            fn () => Entry::where('user_id', $userId)->count()
        );

        if ($total > 0) {
            $this->resetPage();
        }
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $userId = auth()->id();

        // Cache the total count for 60s to avoid duplicate COUNT(*) on page navigation
        $total = \Illuminate\Support\Facades\Cache::remember(
            "user_{$userId}_entries_count",
            60,
            fn () => Entry::where('user_id', $userId)->count()
        );

        if ($total === 0) {
            $recentEntries = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                $this->perPage,
                1,
                [
                    'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );

            return view('livewire.history', [
                'recentEntries' => $recentEntries,
                'totalEntries' => 0,
            ]);
        }

        $query = Entry::with('tags:id,name')
            ->select(['id', 'title', 'content', 'created_at', 'user_id'])
            ->where('user_id', $userId);

        match ($this->sort) {
            'oldest' => $query->oldest(),
            'longest' => $query->orderByRaw('LENGTH(content) DESC'),
            'shortest' => $query->orderByRaw('LENGTH(content) ASC'),
            default => $query->latest(),
        };

        $perPage = 5;

        // Cache the total count for 60s to avoid duplicate COUNT(*) on page navigation
        $total = \Illuminate\Support\Facades\Cache::remember(
            "user_{$userId}_entries_count",
            60,
            fn () => Entry::where('user_id', $userId)->count()
        );

        $currentPage = $this->getPage();
        $items = $query->forPage($currentPage, $perPage)->get();

        // PRE-PROCESS – runs once for only the current page items
        $items->transform(function ($entry) {
            $plain = strip_tags($entry->content);
            $entry->content_html = nl2br(e(\Str::limit($plain, 200)));
            $entry->date_month = $entry->created_at->format('M');
            $entry->date_day = $entry->created_at->format('d');
            $entry->date_formatted = $entry->created_at->format('M d, Y');
            $entry->diff = $entry->created_at->diffForHumans();

            return $entry;
        });

        $recentEntries = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
        $recentEntries->withQueryString();

        return view('livewire.history', [
            'recentEntries' => $recentEntries,
        ]);
    }
}
