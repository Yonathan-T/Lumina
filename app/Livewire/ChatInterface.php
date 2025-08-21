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
        if ($conversation->title === 'New Conversation') {
            $conversation->title = $this->generateSessionTitle($this->newMessage);
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

    private function generateAiResponse($userMessage)
    {
        $responses = [
            'stress' => "I can hear that you're dealing with stress right now. From your recent journal entries, I've noticed you often mention feeling overwhelmed during busy periods. What specific situations or thoughts tend to trigger these feelings for you?",
            'work' => "Work-related challenges can really impact our well-being. I see in your previous entries that you've mentioned work stress before. Have you noticed any patterns in when these feelings are strongest?",
            'anxiety' => "Anxiety can be really difficult to manage. Based on your journal patterns, it seems like you experience this more during certain times or situations. Would it help to explore some coping strategies that align with what you've found helpful before?",
            'mood' => "I've been analyzing your mood patterns from your recent entries. You seem to have more positive days when you mention activities like exercise or spending time outdoors. How are you feeling about incorporating more of these activities?",
            'sleep' => "Sleep issues can affect so many aspects of our lives. Looking at your journal entries, I notice you mention sleep troubles correlating with stressful periods. What does your current bedtime routine look like?",
            'default' => "Thank you for sharing that with me. I can see from your recent journal entries that you've been reflecting on similar themes. What would be most helpful for you to explore right now?"
        ];

        $message = strtolower($userMessage);

        foreach ($responses as $keyword => $response) {
            if (str_contains($message, $keyword)) {
                return $response;
            }
        }

        return $responses['default'];
    }

    private function generateSessionTitle($firstMessage)
    {
        $message = strtolower($firstMessage);

        if (str_contains($message, 'stress'))
            return 'Stress Management Discussion';
        if (str_contains($message, 'work'))
            return 'Work-Life Balance Chat';
        if (str_contains($message, 'anxiety'))
            return 'Anxiety Support Session';
        if (str_contains($message, 'mood'))
            return 'Mood Pattern Analysis';
        if (str_contains($message, 'sleep'))
            return 'Sleep & Wellness Chat';

        return 'Therapy Session';
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