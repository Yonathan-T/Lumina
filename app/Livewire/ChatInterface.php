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
            ->orderBy('last_activity', 'desc')
            ->orderBy('created_at', 'desc')
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
        $this->activeSession = collect($this->sessions)->firstWhere('id', $sessionId);
        $this->loadMessages($sessionId);
    }

    public function loadMessages($sessionId)
    {
        $this->messages = Message::where('conversation_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'isAi' => $message->is_ai,
                    'timestamp' => $message->created_at->format('g:i A'),
                ];
            })->toArray();
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
            'title' => $conversation->title,
            'lastActivity' => $conversation->last_activity ? $conversation->last_activity->diffForHumans() : $conversation->created_at->diffForHumans(),
            'messageCount' => $conversation->message_count ?? 0,
            'type' => $conversation->type ?? 'general',
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

        if (!$this->activeSession) {
            $this->createNewSession();
        }

        $this->isLoading = true;

        $userMessage = Message::create([
            'conversation_id' => $this->activeSession['id'],
            'content' => $this->newMessage,
            'is_ai_response' => false,
        ]);

        $this->messages[] = [
            'id' => $userMessage->id,
            'content' => $userMessage->content,
            'isAi' => $userMessage->is_ai,
            'timestamp' => $userMessage->created_at->format('g:i A'),
        ];

        // AI response 
        $aiResponse = app(AiChatService::class)->generateResponse($this->newMessage, $this->activeSession['id']);

        $aiMessage = Message::create([
            'conversation_id' => $this->activeSession['id'],
            'content' => $aiResponse,
            'is_ai_response' => true,
        ]);

        $this->dispatch('add-ai-message', [
            'id' => $aiMessage->id,
            'content' => $aiMessage->content,
            'isAi' => $aiMessage->is_ai,
            'timestamp' => $aiMessage->created_at->format('g:i A'),
        ]);

        $conversation = Conversation::find($this->activeSession['id']);
        if ($conversation->title === 'New Conversation' && strlen($this->newMessage) > 3) {
            $conversation->title = app(AiChatService::class)->generateTitleFromChat($this->newMessage);
            $conversation->save();
            $this->activeSession['title'] = $conversation->title;
            foreach ($this->sessions as &$session) {
                if ($session['id'] === $this->activeSession['id']) {
                    $session['title'] = $conversation->title;
                    break;
                }
            }
        }
        $conversation->message_count = count($this->messages) + 1;
        $conversation->last_activity = now();
        $conversation->save();

        $this->newMessage = '';
        $this->isLoading = false;
        $this->dispatch('scroll-to-bottom');
    }


    public function deleteSession($sessionId)
    {
        Conversation::destroy($sessionId);
        $this->sessions = array_filter($this->sessions, fn($session) => $session['id'] !== $sessionId);
        if ($this->activeSession && $this->activeSession['id'] === $sessionId) {
            if (!empty($this->sessions)) {
                $this->selectSession($this->sessions[0]['id']);
            } else {
                $this->activeSession = null;
                $this->messages = [];
            }
        }
    }

    public function render()
    {
        return view('livewire.chat-interface');
    }
}