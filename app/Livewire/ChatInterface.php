<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Message;
use Livewire\Component;

class ChatInterface extends Component
{
    public $sessions = [];

    public $activeSession = null;

    public $messages = [];

    public $newMessage = '';

    public $isLoading = false;

    public $isTyping = false;

    public $isSwitchingSession = false;

    public $isLoadingMessages = false;


    public $messagesLoaded = false;

    public function mount()
    {
        $this->loadSessions();
        if (! empty($this->sessions)) {
            $this->selectSession($this->sessions[0]['id']);
        }
    }

    public function loadSessions()
    {
        $this->sessions = Conversation::where('user_id', auth()->id())
            ->select(['id', 'title', 'last_activity', 'created_at', 'message_count', 'type'])
            ->orderBy('last_activity', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(20) // Limit sessions for better performance
            ->get()
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'lastActivity' => $conversation->last_activity ? $conversation->last_activity->diffForHumans() : $conversation->created_at->diffForHumans(),
                    'messageCount' => $conversation->message_count ?? 0,
                    'type' => $conversation->type ?? 'general',
                ];
            })->toArray();
    }

    public function selectSession($sessionId)
    {
        $this->messages = [];
        $this->isTyping = false;
        $this->isSwitchingSession = true;
        $this->isLoadingMessages = true;
        $this->messagesLoaded = false;

        $this->activeSession = collect($this->sessions)->firstWhere('id', $sessionId);
        $this->loadMessages($sessionId);
    }

    public function loadMessages($sessionId)
    {
        $this->messages = Message::where('conversation_id', $sessionId)
            ->select(['id', 'content', 'is_ai_response', 'created_at'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'isAi' => $message->is_ai_response,
                    'createdAt' => $message->created_at->toIso8601String(),
                ];
            })->toArray();

        // Update loading states
        $this->isSwitchingSession = false;
        $this->isLoadingMessages = false;
        $this->messagesLoaded = true;

        $this->dispatch('messages-updated');
    }

    public function createNewSession()
    {
        $conversation = Conversation::create([
            'user_id' => auth()->id(),
            'title' => 'New Conversation',
            'type' => 'general',
            'message_count' => 0,
            'last_activity' => now(),
        ]);

        $newSession = [
            'id' => $conversation->id,
            'title' => 'New Conversation',
            'lastActivity' => 'Just now',
            'messageCount' => 0,
            'type' => 'general',
        ];

        $this->sessions = array_merge([$newSession], $this->sessions);
        $this->activeSession = $newSession;
        $this->messages = [];
    }

    public function ensureSessionForMessage()
    {
        if (! $this->activeSession) {
            $this->createNewSession();
        }

        return $this->activeSession['id'] ?? null;
    }

    public function completeStreamingResponse($sessionId)
    {
        $this->isTyping = false;
        $this->loadSessions();

        $sessionId = (int) $sessionId;
        $selectedSession = collect($this->sessions)->firstWhere('id', $sessionId);

        if ($selectedSession) {
            $this->activeSession = $selectedSession;
        }

        if ($this->activeSession) {
            $this->loadMessages($this->activeSession['id']);
        }
    }

    public function failStreamingResponse($sessionId = null, $errorMessage = 'Sorry, I encountered an error. Please try again.')
    {
        $this->isTyping = false;

        if ($sessionId) {
            $this->loadSessions();

            $sessionId = (int) $sessionId;
            $selectedSession = collect($this->sessions)->firstWhere('id', $sessionId);

            if ($selectedSession) {
                $this->activeSession = $selectedSession;
                $this->loadMessages($sessionId);
            }
        }

        $this->messages[] = [
            'id' => 'error_'.time(),
            'content' => $errorMessage,
            'isAi' => true,
            'createdAt' => now()->toIso8601String(),
            'isError' => true,
        ];

        $this->dispatch('messages-updated');
    }

    public function deleteSession($sessionId)
    {
        // Find the session to see if it was the active one
        $deletedSession = collect($this->sessions)->firstWhere('id', $sessionId);

        // Delete the conversation and all associated messages from the database
        Conversation::destroy($sessionId);

        // Reload the sessions list from the database
        $this->loadSessions();

        // Now, if the active session was deleted, force the UI to reset
        if ($deletedSession && $this->activeSession && $deletedSession['id'] === $this->activeSession['id']) {
            // This is the key change: force a reset regardless of remaining sessions
            $this->activeSession = null;
            $this->messages = [];
        }
    }

    public function render()
    {
        return view('livewire.chat-interface');
    }
}
