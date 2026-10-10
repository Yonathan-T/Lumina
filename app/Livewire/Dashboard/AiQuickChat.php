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
     */
    public function summarizePastWeek($sync = false)
    {
        $this->showSummaryModal = true;
        $userId = auth()->id();
        $cacheKey = "weekly_summary_{$userId}_" . now()->startOfWeek()->format('Y_m_d');

        if (Cache::has($cacheKey)) {
            $this->weeklySummary = Cache::get($cacheKey);
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
            $this->weeklySummary = Cache::get($cacheKey);
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
Generate a **weekly summary** of the following journal entries in Markdown format.

Use this exact structure, with a blank line after each header:

# 🧭 TL;DR

2–3 sentences summarizing the main theme and emotional tone of the week.

# ✨ Key Themes

- List 3–5 recurring ideas or emotions in bullet points.

# 🔁 Patterns & Reflections

1 short paragraph describing repeating behaviors, thoughts, or insights.

# 💡 Insights

1 paragraph highlighting deeper takeaways.

# 🚀 Action Items

List 3 short, practical, motivating next steps.

Rules:
- Include blank lines after every header.
- Do NOT ask questions.
- Use proper Markdown spacing for readability.

Entries to summarize:'.$formattedEntries;

            $summary = app(AiChatService::class)->generateResponse($prompt, null);
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
        if (! Gate::allows('access-premium')) {
            abort(403, 'You must upgrade your plan to access this feature.');
        }
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
    public function sendQuickChat()
    {
        if (empty(trim($this->quickChatInput))) {
            return;
        }

        $userMessage = trim($this->quickChatInput);

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
