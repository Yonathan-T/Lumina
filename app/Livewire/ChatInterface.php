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
        if (empty($this->sessions)) {
            $this->createNewSession();
        } else {
            $this->selectSession($this->sessions[0]['id']);
        }
    }

    public function loadSessions()
    {
        // Hardcoded sessions for now - replace with database later
        $this->sessions = [
            [
                'id' => 'session-1',
                'title' => 'Guided Reflection Session',
                'lastActivity' => '2 hours ago',
                'messageCount' => 8,
                'type' => 'guided-reflection'
            ],
            [
                'id' => 'session-2',
                'title' => 'Weekly Summary Discussion',
                'lastActivity' => '1 day ago',
                'messageCount' => 12,
                'type' => 'weekly-summary'
            ],
            [
                'id' => 'session-3',
                'title' => 'Stress Management Chat',
                'lastActivity' => '3 days ago',
                'messageCount' => 15,
                'type' => 'general'
            ]
        ];
    }

    public function selectSession($sessionId)
    {
        $this->activeSession = collect($this->sessions)->firstWhere('id', $sessionId);
        $this->loadMessages($sessionId);
    }

    public function loadMessages($sessionId)
    {
        // Hardcoded messages for now - replace with database later
        $messageData = [
            'session-1' => [
                [
                    'id' => 1,
                    'content' => "I've been feeling overwhelmed with work lately and struggling to find balance.",
                    'isAi' => false,
                    'timestamp' => '2:30 PM'
                ],
                [
                    'id' => 2,
                    'content' => "I understand that feeling overwhelmed at work can be really challenging. Based on your recent journal entries, I noticed you mentioned similar feelings about work-life balance. Can you tell me more about what specifically is making you feel overwhelmed?",
                    'isAi' => true,
                    'timestamp' => '2:31 PM'
                ]
            ],
            'session-2' => [
                [
                    'id' => 1,
                    'content' => "Can you help me understand my mood patterns from this week?",
                    'isAi' => false,
                    'timestamp' => '10:15 AM'
                ],
                [
                    'id' => 2,
                    'content' => "Of course! Looking at your journal entries from this week, I can see some interesting patterns. You tend to feel more positive in the mornings, especially on days when you mention exercise or meditation. Would you like me to dive deeper into any specific patterns?",
                    'isAi' => true,
                    'timestamp' => '10:16 AM'
                ]
            ],
            'session-3' => [
                [
                    'id' => 1,
                    'content' => "I need some strategies for managing stress better.",
                    'isAi' => false,
                    'timestamp' => '4:45 PM'
                ]
            ]
        ];

        $this->messages = $messageData[$sessionId] ?? [];
    }

    public function createNewSession()
    {
        $newSession = [
            'id' => 'session-' . \Str::random(8),
            'title' => 'New Conversation',
            'lastActivity' => 'Just now',
            'messageCount' => 0,
            'type' => 'general'
        ];

        array_unshift($this->sessions, $newSession);
        $this->activeSession = $newSession;
        $this->messages = [];
    }

    public function sendMessage()
    {
        if (empty(trim($this->newMessage))) {
            return;
        }

        $this->isLoading = true;

        // Add user message
        $userMessage = [
            'id' => count($this->messages) + 1,
            'content' => $this->newMessage,
            'isAi' => false,
            'timestamp' => now()->format('g:i A')
        ];

        $this->messages[] = $userMessage;

        // Generate AI response based on message content
        $aiResponse = $this->generateAiResponse($this->newMessage);

        // Add AI message after a short delay
        $this->dispatch('add-ai-message', [
            'id' => count($this->messages) + 1,
            'content' => $aiResponse,
            'isAi' => true,
            'timestamp' => now()->addSeconds(2)->format('g:i A')
        ]);

        // Update session title if it's a new conversation
        if ($this->activeSession['title'] === 'New Conversation') {
            $this->activeSession['title'] = $this->generateSessionTitle($this->newMessage);
            // Update in sessions array
            foreach ($this->sessions as &$session) {
                if ($session['id'] === $this->activeSession['id']) {
                    $session['title'] = $this->activeSession['title'];
                    break;
                }
            }
        }

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
        $this->sessions = array_filter($this->sessions, fn($session) => $session['id'] !== $sessionId);

        if ($this->activeSession && $this->activeSession['id'] === $sessionId) {
            if (!empty($this->sessions)) {
                $this->selectSession($this->sessions[0]['id']);
            } else {
                $this->createNewSession();
            }
        }
    }

    public function render()
    {
        return view('livewire.chat-interface');
    }
}