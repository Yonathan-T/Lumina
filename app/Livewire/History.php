<?php

namespace App\Livewire;
use App\Models\Entry;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Services\UserDataService;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\AiChatService;
class History extends Component
{
    use WithPagination;
    public $sort = 'newest';
    public $queryString = ['sort'];

    public $userDataService;

    public ?string $isProcessing = null;
public ?int $processingEntryId = null;
   

    public function Reflect($entryId)
    {
        $this->isProcessing = 'reflection';
        $this->processingEntryId = $entryId;
        $this->dispatch('start-reflection-async', entryId: $entryId);
    }

    #[On('start-reflection-async')]
    public function startReflectionAsync($entryId)
    {
        try {
            $userDataService = app(UserDataService::class);
            $reflectionData = $userDataService->reflectOnEntry($entryId);

            if (!$reflectionData) {
                $this->isProcessing = null;
                $this->processingEntryId = null;
                $this->dispatch('notify', message: 'Entry not found', type: 'error');
                return;
            }

            // Create a new conversation for this reflection
            $conversation = Conversation::create([
                'user_id' => auth()->id(),
                'title' => $reflectionData['conversation_title'],
                'type' => 'reflection',
                'message_count' => 0,
                'last_activity' => now(),
            ]);

            // Get AI response using the complete reflection prompt
            $aiChatService = app(AiChatService::class);
            $aiResponse = $aiChatService->generateResponse($reflectionData['prompt'], $conversation->id);

            // Save the AI's response as the first message
            Message::create([
                'conversation_id' => $conversation->id,
                'content' => $aiResponse,
                'is_ai_response' => true,
            ]);

            // Update conversation with the new message count
            $conversation->update([
                'message_count' => 1,
                'last_activity' => now(),
            ]);

            $this->isProcessing = null;
            $this->processingEntryId = null;

            // Redirect to the chat interface
            return redirect()->route('chat.index', ['conversation' => $conversation->id]);

        } catch (\Exception $e) {
            \Log::error('Reflection Error: ' . $e->getMessage());
            $this->isProcessing = null;
            $this->processingEntryId = null;
            $this->dispatch('notify', message: 'Unable to start reflection. Please try again.', type: 'error');
        }
    }
    public function updatingSort()
    {
        $this->resetPage();
    }

    // public function render()
    // {
    //     $query = Entry::with('tags')->where('user_id', auth()->id());

    //     switch ($this->sort) {
    //         case 'oldest':
    //             $query->orderBy('created_at', 'asc');
    //             break;
    //         case 'longest':
    //             $query->orderByRaw('LENGTH(content) DESC');
    //             break;
    //         case 'shortest':
    //             $query->orderByRaw('LENGTH(content) ASC');
    //             break;
    //         case 'newest':
    //         default:
    //             $query->orderBy('created_at', 'desc');
    //     }

    //     $recentEntries = $query->paginate(5)->withQueryString();

    //     return view('livewire.history', [
    //         'recentEntries' => $recentEntries
    //     ]);
    // }
    public function render()
{
    $query = Entry::with('tags')->where('user_id', auth()->id());

    match ($this->sort) {
        'oldest'   => $query->oldest(),
        'longest'  => $query->orderByRaw('LENGTH(content) DESC'),
        'shortest' => $query->orderByRaw('LENGTH(content) ASC'),
        default    => $query->latest(),
    };

    $recentEntries = $query->paginate(5)->withQueryString();

    // PRE-PROCESS – runs once, not per Blade line
    $recentEntries->getCollection()->transform(function ($entry) {
        $plain = strip_tags($entry->content);
        $entry->content_html = nl2br(e(\Str::limit($plain, 200)));
        $entry->date_month   = $entry->created_at->format('M');
        $entry->date_day     = $entry->created_at->format('d');
        $entry->diff         = $entry->created_at->diffForHumans();
        return $entry;
    });

    // return view('livewire.history', compact('recentEntries'));
     return view('livewire.history', [
            'recentEntries' => $recentEntries
        ])->layout('components.layout', [
                'showSidebar' => true,
                'showNav' => false,
                
            ]);
}
}