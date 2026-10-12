<?php

namespace App\Livewire\Dashboard;

use App\Models\Conversation;
use App\Models\Entry;
use App\Models\Message;
use App\Services\AiChatService;
use App\Services\UserDataService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AiQuickChat extends Component
{
    public $showSummaryModal = false;

    public $showQuickChatModal = false;

    public $weeklySummary = '';

    public $summaryLoading = false;

    public $quickChatMessages = [];

    public $quickChatInput = '';

    public $quickChatLoading = false;

    public $isProcessing = null;

    public function mount()
    {
        // Force complete refresh of user and subscriptions
        if (auth()->check()) {
            $user = auth()->user();
            $user->refresh();
            $user->unsetRelation('subscriptions');
            $user->unsetRelation('latestSubscription');
        }
    }

    /**
     * Start guided reflection - creates a conversation rapidly and redirects to chat
     */
    public function startGuidedReflection()
    {
        $this->isProcessing = 'guided-reflection';
        Log::info('Starting guided reflection with fast handoff');
        try {
            $conversation = Conversation::create([
                'user_id' => auth()->id(),
                'title' => 'Guided Reflection',
                'type' => 'reflection',
                'message_count' => 1,
                'last_activity' => now(),
            ]);

            $userDataService = app(UserDataService::class);
            $recentEntries = $userDataService->getRecentEntries(3);

            if ($recentEntries->isNotEmpty()) {
                $latest = $recentEntries->first();
                $titleSnippet = !empty($latest->title) ? " around \"{$latest->title}\"" : '';
                $initialMessage = "Welcome to your guided reflection session. I'm Lumi, your personal reflection companion. Looking over your recent notes{$titleSnippet}, what thoughts or feelings feel most important for you to explore right now?";
            } else {
                $initialMessage = "Welcome to your guided reflection session. I'm Lumi, your personal reflection companion. Take a moment to pause and breathe. What's on your mind today that you'd like to reflect on together?";
            }

            Message::create([
                'conversation_id' => $conversation->id,
                'content' => $initialMessage,
                'is_ai_response' => true,
            ]);

            $this->isProcessing = null;

            return $this->redirect(route('chat.index', ['conversation' => $conversation->id]), navigate: true);

        } catch (\Exception $e) {
            Log::error('Guided Reflection Error: '.$e->getMessage());
            $this->isProcessing = null;
            session()->flash('error', 'Unable to start guided reflection. Please try again.');
        }
    }

    /**
     * Generate or open weekly summary with TLDR and insights
    /**
     * Sanitize weekly summary: completely strip emojis and leading bullet points
     */
    public function sanitizeSummary(string $summary): string
    {
        // Strip all unicode emojis and symbols
        $clean = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $summary);

        // Strip leading bullet dashes/asterisks/bullets from lines
        $clean = preg_replace('/^[ \t]*[-*•][ \t]+/m', '', $clean);

        return trim($clean);
    }

    /**
     * Computed property parsing weekly summary into structured sections for modern card rendering
     */
    public function getParsedWeeklySummaryProperty(): array
    {
        if (empty($this->weeklySummary)) {
            return [];
        }

        $raw = $this->sanitizeSummary($this->weeklySummary);
        $sections = [];

        // Split by markdown headers (# Title or ## Title)
        $parts = preg_split('/^#{1,3}\s+(.+)$/m', $raw, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (count($parts) <= 1) {
            return [
                [
                    'title' => 'Weekly Overview',
                    'content' => $raw,
                ]
            ];
        }

        $intro = trim($parts[0] ?? '');
        if (!empty($intro)) {
            $sections[] = [
                'title' => 'Executive Overview',
                'content' => $intro,
            ];
        }

        for ($i = 1; $i < count($parts); $i += 2) {
            $title = trim($parts[$i] ?? '');
            $content = trim($parts[$i + 1] ?? '');

            $title = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', '', $title);
            $title = trim($title, " #:\t\n\r\0\x0B");

            if (!empty($title)) {
                $sections[] = [
                    'title' => $title,
                    'content' => $content,
                ];
            }
        }

        return $sections;
    }

    /**
     * Generate or open weekly summary with TLDR and insights
     */
    public function summarizePastWeek($sync = false)
    {
        $this->showSummaryModal = true;
        $userId = auth()->id();
        $cacheKey = "weekly_summary_{$userId}_" . now()->startOfWeek()->format('Y_m_d');

        if (Cache::has($cacheKey)) {
            $this->weeklySummary = $this->sanitizeSummary(Cache::get($cacheKey));
            $this->summaryLoading = false;
            return;
        }

        // Fast path: If the user has written 0 entries in the past week, return the message instantly without waiting
        $recentCount = Entry::where('user_id', $userId)
            ->where('created_at', '>=', now()->subWeek())
            ->count();

        if ($recentCount === 0) {
            $this->weeklySummary = "## No Entries This Week\n\nYou haven't written any journal entries in the past week. Consider starting a new entry to track your thoughts and experiences!";
            $this->summaryLoading = false;
            return;
        }

        $this->weeklySummary = '';
        $this->summaryLoading = true;

        if ($sync) {
            $this->loadWeeklySummary();
        }
    }

    /**
     * Deferred loader for weekly summary
     */
    public function loadWeeklySummary()
    {
        $userId = auth()->id();
        $cacheKey = "weekly_summary_{$userId}_" . now()->startOfWeek()->format('Y_m_d');

        if (Cache::has($cacheKey)) {
            $this->weeklySummary = $this->sanitizeSummary(Cache::get($cacheKey));
            $this->summaryLoading = false;
            return;
        }

        $this->summaryLoading = true;
        Log::info('Generating weekly summary asynchronously');

        try {
            $userDataService = app(UserDataService::class);

            $entries = Entry::where('user_id', $userId)
                ->where('created_at', '>=', now()->subWeek())
                ->select(['id', 'title', 'content', 'created_at'])
                ->orderBy('created_at')
                ->get();

            if ($entries->isEmpty()) {
                $this->weeklySummary = "## No Entries This Week\n\nYou haven't written any journal entries in the past week. Consider starting a new entry to track your thoughts and experiences!";
                $this->summaryLoading = false;
                return;
            }

            $formattedEntries = $userDataService->formatEntriesForAI($entries);

            $prompt = '
Generate a clean, professional **Weekly Journal Summary Report** of the following journal entries in GitHub-flavored Markdown.

STRICT FORMATTING RULES:
- Do NOT use ANY emojis anywhere in the response. Absolutely no icons, emojis, or symbols.
- Do NOT use bullet points (no dashes "-", no asterisks "*", no bullet dots "•"). Write clear, cohesive narrative paragraphs, formatted tables, and numbered action items.
- Do NOT ask questions.
- Use clean Markdown headers ("## ") for each section.

Structure the report using this exact template:

## Executive Overview
A concise, thoughtful 2–3 sentence synthesis capturing the primary emotional tone, overarching mindset, and key focus areas across the week.

## Entries Reviewed
Discuss each journal entry by its exact Title and date, with a single clean sentence summarizing what was written without bullet points.

## Key Dimensions
| Dimension | Focus & Observation |
| :--- | :--- |
| Primary Themes | [Summary of core recurring themes] |
| Emotional Trajectory | [Summary of emotional tone, stability, and growth] |
| Notable Milestones | [Key events, shifts, or decisions mentioned] |

## Behavioral Patterns & Reflections
A well-structured narrative examining recurring habits, perspective shifts, and how challenges or uncertainties were navigated throughout the week without using bullet points.

## Strategic Takeaways
Core Realization: [Key personal takeaway or realization]
Growth Opportunity: [Constructive area for development or mindfulness]

## Recommended Next Steps
1. [Actionable, practical step 1]
2. [Actionable, practical step 2]
3. [Actionable, practical step 3]

Journal entries to analyze:
'.$formattedEntries;

            $summary = app(AiChatService::class)->generateResponse($prompt, null);
            $summary = $this->sanitizeSummary($summary);
            $this->weeklySummary = $summary;

            // Cache summary for 24 hours / weekly session
            Cache::put($cacheKey, $summary, now()->addHours(24));

        } catch (\Exception $e) {
            Log::error('Weekly Summary Error: '.$e->getMessage());
            $this->weeklySummary = 'Unable to generate summary. Please try again later.';
        } finally {
            $this->summaryLoading = false;
            $this->isProcessing = null;
        }
    }

    /**
     * Start quick chat session (ephemeral, not saved)
     */
    public function startQuickChat()
    {
        $this->showQuickChatModal = true;
        $this->quickChatMessages = [
            [
                'sender' => 'ai',
                'content' => "Hi there! I'm here for a quick, private chat. What's on your mind? This conversation won't be saved anywhere.",
                'timestamp' => now()->format('g:i A'),
            ],
        ];
        $this->quickChatInput = '';
    }

    /**
     * Send message in quick chat
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
        $this->quickChatLoading = true;

        try {
            $prompt = 'You are having a quick, temporary chat with a user. This conversation will not be saved. ';
            $prompt .= 'Be helpful, empathetic, and concise. If the user wants to save the conversation, suggest using the main chat feature. ';
            $prompt .= 'User message: '.$userMessage;

            $aiResponse = app(AiChatService::class)->generateResponse($prompt, null);

            $this->quickChatMessages[] = [
                'sender' => 'ai',
                'content' => $aiResponse,
                'timestamp' => now()->format('g:i A'),
            ];

        } catch (\Exception $e) {
            Log::error('Quick Chat Error: '.$e->getMessage());
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
     * Review past memos - analyze patterns over broader timeframe with fast handoff
     */
    public function reviewPastMemos()
    {
        $this->isProcessing = 'review-memos';
        Log::info('Starting review memos with fast handoff');
        try {
            $conversation = Conversation::create([
                'user_id' => auth()->id(),
                'title' => 'Memo Review & Analysis',
                'type' => 'analysis',
                'message_count' => 1,
                'last_activity' => now(),
            ]);

            $userDataService = app(UserDataService::class);
            $insights = $userDataService->getUserInsights();
            $entriesCount = $insights['total_entries'] ?? 0;
            $streak = $insights['current_streak'] ?? 0;
            $topTag = $insights['most_used_tag'] ?? null;

            if ($entriesCount > 0) {
                $tagClause = ($topTag && $topTag !== 'None') ? " with top tag #{$topTag}" : '';
                $initialMessage = "Welcome to your memo review and pattern analysis. Across your {$entriesCount} journal entries ({$streak} day streak{$tagClause}), I'm ready to help you analyze recurring themes, emotional patterns, and growth areas. What specific pattern or timeframe would you like to dive into first?";
            } else {
                $initialMessage = "I notice you don't have many entries to analyze yet. That's perfectly fine! As you continue journaling, I'll be able to provide deeper insights into your patterns and growth over time. Feel free to ask me anything about building a journaling habit!";
            }

            Message::create([
                'conversation_id' => $conversation->id,
                'content' => $initialMessage,
                'is_ai_response' => true,
            ]);

            $this->isProcessing = null;

            return $this->redirect(route('chat.index', ['conversation' => $conversation->id]), navigate: true);

        } catch (\Exception $e) {
            Log::error('Memo Review Error: '.$e->getMessage());
            $this->isProcessing = null;
            session()->flash('error', 'Unable to review memos. Please try again.');
        }
    }

    /**
     * Start therapy session - personalized based on recent patterns with fast handoff
     */
    public function startTherapySession()
    {
        $this->isProcessing = 'therapy-session';
        Log::info('Starting therapy session with fast handoff');
        try {
            $conversation = Conversation::create([
                'user_id' => auth()->id(),
                'title' => 'Therapy Session',
                'type' => 'therapy',
                'message_count' => 1,
                'last_activity' => now(),
            ]);

            $userDataService = app(UserDataService::class);
            $recentEntries = $userDataService->getRecentEntries(2);

            if ($recentEntries->isNotEmpty()) {
                $latest = $recentEntries->first();
                $titleSnippet = !empty($latest->title) ? " mentioning \"{$latest->title}\"" : '';
                $initialMessage = "Welcome to your therapy session. I'm here to offer a safe, compassionate space to unpack whatever you're experiencing. Reflecting on your recent notes{$titleSnippet}, how are you feeling in this moment?";
            } else {
                $initialMessage = "Welcome to your therapy session. I'm here to offer a supportive, compassionate space for you. How are you feeling today, and what brought you here?";
            }

            Message::create([
                'conversation_id' => $conversation->id,
                'content' => $initialMessage,
                'is_ai_response' => true,
            ]);

            $this->isProcessing = null;

            return $this->redirect(route('chat.index', ['conversation' => $conversation->id]), navigate: true);

        } catch (\Exception $e) {
            Log::error('Therapy Session Error: '.$e->getMessage());
            $this->isProcessing = null;
            session()->flash('error', 'Unable to start therapy session. Please try again.');
        }
    }

    /**
     * Close modals
     */
    public function closeSummaryModal()
    {
        $this->showSummaryModal = false;
        $this->summaryLoading = false;
    }

    public function closeQuickChatModal()
    {
        $this->showQuickChatModal = false;
        $this->quickChatMessages = [];
        $this->quickChatInput = '';
    }

    public function render()
    {
        return view('livewire.dashboard.ai-quick-chat');
    }
}
