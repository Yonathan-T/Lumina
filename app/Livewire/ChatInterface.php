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

    public $greeting = 'Ready when you are';

    public $starterPrompts = [];

    public function getRandomGreeting(): string
    {
        $greetings = [
            'Ready when you are',
            'What\'s on your mind?',
            'Take your time',
            'Here whenever you\'re ready',
            'Let\'s talk through it',
            'A quiet space for your thoughts',
        ];

        // Ensure we pick a greeting different from the current one on update
        $candidates = array_values(array_filter($greetings, fn ($g) => $g !== $this->greeting));
        if (empty($candidates)) {
            $candidates = $greetings;
        }

        return $candidates[array_rand($candidates)];
    }

    public function getRotatedStarterPrompts(): array
    {
        $all = [
            [
                'title' => 'Reflect on today',
                'subtitle' => 'Review key feelings and lessons from today',
                'action' => 'Help me reflect on how my day went and what I learned.',
            ],
            [
                'title' => 'Clear my head',
                'subtitle' => 'Untangle racing thoughts and find calm',
                'action' => 'I have a lot on my mind right now. Can we break it down step by step?',
            ],
            [
                'title' => 'Work through a choice',
                'subtitle' => 'Weigh options with objective guidance',
                'action' => 'I need help making a decision. Can you ask me guiding questions?',
            ],
            [
                'title' => 'Fresh perspective',
                'subtitle' => 'Gain clarity on stress or challenges',
                'action' => 'What is a fresh perspective on dealing with everyday stress?',
            ],
            [
                'title' => 'Unpack an interaction',
                'subtitle' => 'Talk through a conversation or event',
                'action' => 'I had a conversation that stuck with me. Can we unpack it?',
            ],
            [
                'title' => 'Set an intention',
                'subtitle' => 'Focus on what matters for tomorrow',
                'action' => 'Help me set a clear, grounded intention for tomorrow.',
            ],
        ];

        shuffle($all);

        return array_slice($all, 0, 4);
    }

    public function mount()
    {
        $this->greeting = $this->getRandomGreeting();
        $this->starterPrompts = $this->getRotatedStarterPrompts();
        $this->loadSessions();

        $requestedId = request()->query('conversation');
        if (! $requestedId && session()->has('active_chat_conversation_id')) {
            $candidateId = (int) session('active_chat_conversation_id');
            if (collect($this->sessions)->contains('id', $candidateId)) {
                $requestedId = $candidateId;
            }
        }

        if ($requestedId && collect($this->sessions)->contains('id', (int) $requestedId)) {
            $this->selectSession((int) $requestedId);
        } else {
            // Default: do not force-select old conversation; show fresh greeting screen
            $this->activeSession = null;
            $this->messages = [];
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
        $sessionId = (int) $sessionId;

        $this->messages = [];
        $this->isTyping = false;
        $this->isSwitchingSession = true;
        $this->isLoadingMessages = true;
        $this->messagesLoaded = false;

        $conversation = Conversation::where('user_id', auth()->id())->find($sessionId);

        if ($conversation) {
            $this->activeSession = [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'lastActivity' => $conversation->last_activity ? $conversation->last_activity->diffForHumans() : $conversation->created_at->diffForHumans(),
                'messageCount' => $conversation->message_count ?? 0,
                'type' => $conversation->type ?? 'general',
            ];
            $this->loadMessages($conversation->id);
            session(['active_chat_conversation_id' => $conversation->id]);
        } else {
            $this->activeSession = null;
            $this->messages = [];
            session()->forget('active_chat_conversation_id');
        }

        $this->dispatch('session-selected', sessionId: $sessionId);
    }

    public function loadMessages($sessionId)
    {
        $this->messages = Message::where('conversation_id', $sessionId)
            ->select(['id', 'conversation_id', 'content', 'is_ai_response', 'created_at', 'metadata'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'isAi' => $message->is_ai_response,
                    'createdAt' => $message->created_at->toIso8601String(),
                    'metadata' => is_array($message->metadata) ? $message->metadata : (is_string($message->metadata) ? json_decode($message->metadata, true) : null),
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
        $this->activeSession = null;
        $this->messages = [];
        $this->greeting = $this->getRandomGreeting();
        $this->starterPrompts = $this->getRotatedStarterPrompts();
        $this->isTyping = false;
        $this->isLoadingMessages = false;
        $this->messagesLoaded = false;
        session()->forget('active_chat_conversation_id');
        $this->dispatch('session-cleared');
    }

    public function ensureSessionForMessage()
    {
        if (! $this->activeSession) {
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
            $this->messagesLoaded = true;
            $this->isLoadingMessages = false;
            session(['active_chat_conversation_id' => $conversation->id]);
            $this->dispatch('session-selected', sessionId: $conversation->id);
        }

        return $this->activeSession['id'] ?? null;
    }

    public function completeStreamingResponse($sessionId)
    {
        $this->isTyping = false;
        $this->loadSessions();

        $sessionId = (int) $sessionId;
        $selectedSession = collect($this->sessions)->firstWhere('id', $sessionId);

        if (! $selectedSession) {
            $conversation = Conversation::where('user_id', auth()->id())->find($sessionId);
            if ($conversation) {
                $selectedSession = [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'lastActivity' => $conversation->last_activity ? $conversation->last_activity->diffForHumans() : $conversation->created_at->diffForHumans(),
                    'messageCount' => $conversation->message_count ?? 0,
                    'type' => $conversation->type ?? 'general',
                ];
            }
        }

        if ($selectedSession) {
            $this->activeSession = $selectedSession;
        }

        if ($this->activeSession) {
            $this->loadMessages($this->activeSession['id']);
        }
    }

    public function failStreamingResponse($sessionId = null, $errorMessage = 'Sorry, I encountered an error. Please try again.', $userMessage = null)
    {
        $this->isTyping = false;

        if ($sessionId) {
            $this->loadSessions();

            $sessionId = (int) $sessionId;
            $selectedSession = collect($this->sessions)->firstWhere('id', $sessionId);

            if (! $selectedSession) {
                $conversation = Conversation::where('user_id', auth()->id())->find($sessionId);
                if ($conversation) {
                    $selectedSession = [
                        'id' => $conversation->id,
                        'title' => $conversation->title,
                        'lastActivity' => $conversation->last_activity ? $conversation->last_activity->diffForHumans() : $conversation->created_at->diffForHumans(),
                        'messageCount' => $conversation->message_count ?? 0,
                        'type' => $conversation->type ?? 'general',
                    ];
                }
            }

            if ($selectedSession) {
                $this->activeSession = $selectedSession;
                $this->loadMessages($sessionId);
            }
        }

        // Ensure user message is not lost even if stream failed before or during DB persistence
        if ($userMessage) {
            $lastMsg = !empty($this->messages) ? end($this->messages) : null;
            if (!$lastMsg || $lastMsg['isAi'] || trim($lastMsg['content']) !== trim($userMessage)) {
                $this->messages[] = [
                    'id' => 'user_pending_'.time(),
                    'content' => $userMessage,
                    'isAi' => false,
                    'createdAt' => now()->toIso8601String(),
                ];
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
        $sessionId = (int) $sessionId;

        Conversation::where('id', $sessionId)
            ->where('user_id', auth()->id())
            ->delete();

        // In-memory removal for instant feedback
        $this->sessions = array_values(array_filter($this->sessions, function ($session) use ($sessionId) {
            return (int) $session['id'] !== $sessionId;
        }));

        // If the deleted session was currently active, show fresh greeting screen
        if ($this->activeSession && (int) $this->activeSession['id'] === $sessionId) {
            $this->activeSession = null;
            $this->messages = [];
            $this->greeting = $this->getRandomGreeting();
            session()->forget('active_chat_conversation_id');
            $this->dispatch('session-cleared');
        }
    }

    public function renderMessageContent(array $message): string
    {
        $rawContent = (string) ($message['content'] ?? '');
        if (!($message['isAi'] ?? false)) {
            return e($rawContent);
        }

        // Clean up em-dashes and double dashes
        $cleaned = preg_replace('/\s*[—–]\s*/u', ', ', $rawContent);
        $cleaned = preg_replace('/\s*--\s*/', ', ', $cleaned);

        $tools = $message['metadata']['tools_used'] ?? [];
        $entries = [];

        foreach ($tools as $tool) {
            if (($tool['tool'] ?? '') === 'search_journal' && !empty($tool['result']['entries'])) {
                foreach ($tool['result']['entries'] as $item) {
                    if (!empty($item['title']) && !empty($item['id'])) {
                        $entries[] = $item;
                    }
                }
            }
        }

        $processed = e($cleaned);
        $placeholders = [];
        $placeholderIndex = 0;

        if (!empty($entries)) {
            // Deduplicate entries by ID
            $uniqueEntries = [];
            foreach ($entries as $entry) {
                $uniqueEntries[$entry['id']] = $entry;
            }
            $entries = array_values($uniqueEntries);

            $targets = [];
            foreach ($entries as $entry) {
                $title = (string) ($entry['title'] ?? '');
                if ($title === '') {
                    continue;
                }

                $cleanTitle = trim(preg_replace('/^[\p{Emoji}\p{So}\p{Sk}\p{Sm}\p{Mn}\p{Me}\p{C}\s\p{P}]+|[\p{Emoji}\p{So}\p{Sk}\p{Sm}\p{Mn}\p{Me}\p{C}\s\p{P}]+$/u', '', $title));
                $subparts = preg_split('/\s+[—–\-:]\s+/u', $cleanTitle);
                $mainTitle = trim($subparts[0] ?? '');

                $candidates = array_unique(array_filter([$title, $cleanTitle, $mainTitle], fn($t) => mb_strlen($t) >= 3));

                foreach ($candidates as $cand) {
                    $targets[] = [
                        'candidate' => $cand,
                        'entry' => $entry,
                        'length' => mb_strlen($cand),
                    ];
                }
            }

            // Sort candidates by length descending so longer titles match first
            usort($targets, fn ($a, $b) => $b['length'] <=> $a['length']);
            $matchedEntryIds = [];

            foreach ($targets as $tgt) {
                $entryId = $tgt['entry']['id'];
                if (isset($matchedEntryIds[$entryId])) {
                    continue;
                }

                $cand = $tgt['candidate'];
                $escapedTitle = preg_quote(e($cand), '/');
                $leadBoundary = preg_match('/^\w/u', $cand) ? '(?<=\s|^|[\*_"\']|&quot;)' : '';
                $trailBoundary = preg_match('/\w$/u', $cand) ? '(?=\s|$|[\*_"\',.;:!?]|&quot;|\'s|’s)' : '';
                $pattern = '/' . $leadBoundary . '(?:[\*_"]|&quot;)*' . $escapedTitle . '(?:\'s|’s)?(?:[\*_"]|&quot;)*' . $trailBoundary . '/iu';

                if (preg_match($pattern, $processed)) {
                    $b64 = base64_encode(json_encode($tgt['entry'], JSON_UNESCAPED_UNICODE));
                    $entryUrl = '/entries/' . urlencode((string) $entryId);
                    $buttonHtml = '<button type="button" ' .
                        'data-entry-preview="true" ' .
                        'data-entry-b64="' . $b64 . '" ' .
                        'onclick="window.location.href = \'' . $entryUrl . '\'" ' .
                        'class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-blue-500/15 hover:bg-blue-500/25 text-blue-300 hover:text-blue-200 border border-blue-500/30 transition-all font-medium cursor-pointer align-baseline text-xs mx-0.5 group shadow-sm">' .
                        '<svg class="w-3.5 h-3.5 text-blue-400 group-hover:scale-110 transition-transform flex-shrink-0" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path class="transition-transform group-hover:translate-x-0.5" d="M7 8h8"/><path class="transition-transform group-hover:-translate-x-0.5" d="M7 12h10"/><path class="transition-transform group-hover:translate-x-0.5" d="M7 16h6"/></svg>' .
                        '<span>' . e($tgt['entry']['title']) . '</span>' .
                        '</button>';

                    $token = '___ENTRY_PILL_' . ($placeholderIndex++) . '___';
                    $placeholders[$token] = $buttonHtml;
                    $processed = preg_replace($pattern, $token, $processed, 1);
                    $matchedEntryIds[$entryId] = true;
                }
            }
        }

        // Format bullet lines (e.g. "* Title" or "- Title")
        $lines = explode("\n", $processed);
        $formattedLines = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^[\*\-]\s+(.+)$/u', $trimmed, $m)) {
                $formattedLines[] = '<span class="inline-flex items-start gap-2 my-0.5"><span class="w-1.5 h-1.5 rounded-full bg-blue-400 mt-2 shrink-0"></span><span>' . $m[1] . '</span></span>';
            } else {
                $formattedLines[] = $line;
            }
        }
        $processed = implode("\n", $formattedLines);

        // Format bold: **text**
        $processed = preg_replace('/\*\*([^*]+)\*\*/u', '<strong class="font-semibold text-white">$1</strong>', $processed);

        // Format italics: *text* (when not preceded or followed by another asterisk)
        $processed = preg_replace('/(?<=\s|^)\*([^*]+)\*(?=\s|$|[,.;:!?])/u', '<em class="italic text-gray-200">$1</em>', $processed);

        // Clean up any remaining lone asterisks
        $processed = preg_replace('/(?<=\s|^)\*(?=\s|$)/u', '', $processed);

        if (!empty($placeholders)) {
            $processed = strtr($processed, $placeholders);
        }

        return $processed;
    }

    public function render()
    {
        return view('livewire.chat-interface');
    }
}
