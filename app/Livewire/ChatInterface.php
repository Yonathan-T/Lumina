<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\AiChatService;
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;

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
        if (!empty($this->sessions)) {
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

        $this->dispatch('session-selected', sessionId: $sessionId);
    }

    #[On('session-selected')]
    public function loadSessionMessages($sessionId)
    {
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
                    'timestamp' => $message->created_at->format('g:i A'),// time stamp should use their systems time i think
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
        $tempId = 'temp_' . time();
        $newSession = [
            'id' => $tempId,
            'title' => 'New Conversation',
            'lastActivity' => 'Just now',
            'messageCount' => 0,
            'type' => 'general',
        ];

        $this->sessions = array_merge([$newSession], $this->sessions);
        $this->activeSession = $newSession;
        $this->messages = [];

        $this->dispatch('create-session-async', sessionData: $newSession);
    }

    #[On('create-session-async')]
    public function createSessionAsync($sessionData)
    {
        $conversation = Conversation::create([
            'user_id' => auth()->id(),
            'title' => 'New Conversation',
            'type' => 'general',
            'message_count' => 0,
            'last_activity' => now(),
        ]);

        foreach ($this->sessions as &$session) {
            if ($session['id'] === $sessionData['id']) {
                $session['id'] = $conversation->id;
                break;
            }
        }

        if ($this->activeSession && $this->activeSession['id'] === $sessionData['id']) {
            $this->activeSession['id'] = $conversation->id;
        }
    }

    public function sendMessage()
    {
        if (empty(trim($this->newMessage))) {
            return;
        }

        if (!$this->activeSession) {
            $this->createNewSession();
        }

        $messageContent = $this->newMessage;
        $this->optimisticMessageId = 'temp_' . time();

        $this->messages[] = [
            'id' => $this->optimisticMessageId,
            'content' => $messageContent,
            'isAi' => false,
            'timestamp' => now()->format('g:i A'),
            'isOptimistic' => true
        ];

        $this->isTyping = true;
        $this->newMessage = '';

        $this->dispatch('messages-updated');
        $this->dispatch('message-sent-async', [
            'content' => $messageContent,
            'sessionId' => $this->activeSession['id'],
            'optimisticId' => $this->optimisticMessageId
        ]);
    }

    #[On('message-sent-async')]
    public function processSentMessage($data)
    {
        $userMessage = Message::create([
            'conversation_id' => $data['sessionId'],
            'content' => $data['content'],
            'is_ai_response' => false,
        ]);

        foreach ($this->messages as &$message) {
            if ($message['id'] === $data['optimisticId']) {
                $message['id'] = $userMessage->id;
                $message['timestamp'] = $userMessage->created_at->format('g:i A');
                unset($message['isOptimistic']);
                break;
            }
        }

        $this->dispatch('generate-ai-response', [
            'content' => $data['content'],
            'sessionId' => $data['sessionId']
        ]);
    }

    #[On('generate-ai-response')]
    public function generateAiResponse($data)
    {
        try {
            $aiResponse = app(AiChatService::class)->generateResponse($data['content'], $data['sessionId']);

            $this->isTyping = false;

            $aiMessage = Message::create([
                'conversation_id' => $data['sessionId'],
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
            $this->updateConversationMeta($data['sessionId'], $data['content']);

        } catch (\Exception $e) {
            $this->isTyping = false;
            $this->messages[] = [
                'id' => 'error_' . time(),
                'content' => 'Sorry, I encountered an error. Please try again.',
                'isAi' => true,
                'timestamp' => now()->format('g:i A'),
                'isError' => true
            ];
            $this->dispatch('messages-updated');
        }
    }

    private function updateConversationMeta($sessionId, $userContent)
    {
        $conversation = Conversation::find($sessionId);
        if (!$conversation)
            return;

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

    //nobody is using you bro
    #[On('delete-session-async')]
    public function deleteSessionAsync($sessionId)
    {
        Conversation::destroy($sessionId);
    }

    public function render()
    {
        return view('livewire.chat-interface');
    }
}