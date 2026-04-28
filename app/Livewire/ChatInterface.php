<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\AiChatService;
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

    public $optimisticMessageId = null;

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
                    'timestamp' => $message->created_at->format('g:i A'), // time stamp should use their systems time i think
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

    public function sendMessage()
    {
        if (empty(trim($this->newMessage))) {
            return;
        }

        if (! $this->activeSession) {
            $this->createNewSession();
        }

        $messageContent = trim($this->newMessage);
        $this->isTyping = true;
        $this->newMessage = '';

        $userMessage = Message::create([
            'conversation_id' => $this->activeSession['id'],
            'content' => $messageContent,
            'is_ai_response' => false,
        ]);

        try {
            $this->messages[] = [
                'id' => $userMessage->id,
                'content' => $userMessage->content,
                'isAi' => false,
                'timestamp' => $userMessage->created_at->format('g:i A'),
            ];
            $this->dispatch('messages-updated');

            $aiResponse = app(AiChatService::class)->generateResponse($messageContent, $this->activeSession['id']);

            $this->isTyping = false;

            $aiMessage = Message::create([
                'conversation_id' => $this->activeSession['id'],
                'content' => $aiResponse,
                'is_ai_response' => true,
            ]);

            $this->messages[] = [
                'id' => $aiMessage->id,
                'content' => $aiMessage->content,
                'isAi' => true,
                'timestamp' => $aiMessage->created_at->format('g:i A'),
            ];

            $this->dispatch('messages-updated');
            $this->updateConversationMeta($this->activeSession['id'], $messageContent);

        } catch (\Exception $e) {
            $this->isTyping = false;
            $this->messages[] = [
                'id' => 'error_'.time(),
                'content' => 'Sorry, I encountered an error. Please try again.',
                'isAi' => true,
                'timestamp' => now()->format('g:i A'),
                'isError' => true,
            ];
            $this->dispatch('messages-updated');
        }
    }

    private function updateConversationMeta($sessionId, $userContent)
    {
        $conversation = Conversation::find($sessionId);
        if (! $conversation) {
            return;
        }

        if ($conversation->title === 'New Conversation' && count($this->messages) >= 4) {
            $conversation->title = app(AiChatService::class)->generateTitleFromChat($userContent);
            $this->activeSession['title'] = $conversation->title;

            foreach ($this->sessions as &$session) {
                if ($session['id'] === $sessionId) {
                    $session['title'] = $conversation->title;
                    break;
                }
            }
        }

        $conversation->message_count = count($this->messages);
        $conversation->last_activity = now();
        $conversation->save();
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
