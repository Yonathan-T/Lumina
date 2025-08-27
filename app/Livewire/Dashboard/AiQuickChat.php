<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Entry;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\AiChatService;
use App\Services\UserDataService;
use Illuminate\Support\Facades\Log;

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
    }

    /**
     * Start guided reflection - creates a conversation and redirects to chat
     */
    public function startGuidedReflection()
    {
        $this->isProcessing = 'guided-reflection';
        $this->dispatch('start-guided-reflection-async');
    }

    #[On('start-guided-reflection-async')]
    public function startGuidedReflectionAsync()
    {
        \Log::info('Starting guided reflection async');
        try {
            $conversation = Conversation::create([
                'user_id' => auth()->id(),
                'title' => 'Guided Reflection',
                'type' => 'reflection',
                'message_count' => 0,
                'last_activity' => now(),
            ]);

            $userDataService = app(UserDataService::class);
            $recentEntries = $userDataService->getRecentEntries(3);
            $formattedEntries = $userDataService->formatEntriesForAI($recentEntries);

            $prompt = "Based on recent entries, start a gentle reflection. Ask one thoughtful question. Keep it warm and brief.\n\n" . $formattedEntries;

            $aiResponse = app(AiChatService::class)->generateResponse($prompt, $conversation->id);

            Message::create([
                'conversation_id' => $conversation->id,
                'content' => $aiResponse,
                'is_ai_response' => true,
            ]);

            $conversation->update(['message_count' => 1, 'last_activity' => now()]);

            $this->isProcessing = null;
            return redirect()->route('chat.index');

        } catch (\Exception $e) {
            Log::error('Guided Reflection Error: ' . $e->getMessage());
            $this->isProcessing = null;
            session()->flash('error', 'Unable to start guided reflection. Please try again.');
        }
    }

    /**
     * Generate weekly summary with TLDR and insights
     */
    public function summarizePastWeek()
    {
        $this->summaryLoading = true;
        $this->isProcessing = 'weekly-summary';
        $this->weeklySummary = '';
        $this->showSummaryModal = true;

        $this->dispatch('generate-weekly-summary-async');
    }

    #[On('generate-weekly-summary-async')]
    public function generateWeeklySummaryAsync()
    {
        \Log::info('Starting weekly summary async');
        try {
            $userDataService = app(UserDataService::class);

            $entries = Entry::where('user_id', auth()->id())
                ->where('created_at', '>=', now()->subWeek())
                ->select(['id', 'title', 'content', 'created_at'])
                ->orderBy('created_at')
                ->get();

            if ($entries->isEmpty()) {
                $this->weeklySummary = "## No Entries This Week\n\nYou haven't written any journal entries in the past week. Consider starting a new entry to track your thoughts and experiences!";
                $this->summaryLoading = false;
                $this->isProcessing = null;
                return;
            }

            $formattedEntries = $userDataService->formatEntriesForAI($entries);

            $prompt = "Create a weekly summary with:\n1. **TLDR** (2-3 sentences)\n2. **Key Themes**\n3. **Patterns**\n4. **Insights**\n5. **Action Items** (3 suggestions)\n\nEntries:\n" . $formattedEntries;

            $summary = app(AiChatService::class)->generateResponse($prompt, null);
            $this->weeklySummary = $summary;

        } catch (\Exception $e) {
            Log::error('Weekly Summary Error: ' . $e->getMessage());
            $this->weeklySummary = "Unable to generate summary. Please try again later.";
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
                'timestamp' => now()->format('g:i A')
            ]
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
            'timestamp' => now()->format('g:i A')
        ];

        $this->quickChatInput = '';
        $this->quickChatLoading = true;

        try {
            $prompt = "You are having a quick, supportive chat with a user. Be helpful, empathetic, and concise. User message: " . $userMessage;
            $aiResponse = app(AiChatService::class)->generateResponse($prompt, null);

            $this->quickChatMessages[] = [
                'sender' => 'ai',
                'content' => $aiResponse,
                'timestamp' => now()->format('g:i A')
            ];

        } catch (\Exception $e) {
            Log::error('Quick Chat Error: ' . $e->getMessage());
            $this->quickChatMessages[] = [
                'sender' => 'ai',
                'content' => "I'm having trouble connecting right now. Please try again in a moment.",
                'timestamp' => now()->format('g:i A')
            ];
        } finally {
            $this->quickChatLoading = false;
        }
    }

    /**
     * Review past memos - analyze patterns over broader timeframe
     */
    public function reviewPastMemos()
    {
        $this->isProcessing = 'review-memos';
        $this->dispatch('review-memos-async');
    }

    #[On('review-memos-async')]
    public function reviewMemosAsync()
    {
        \Log::info('Starting review memos async');
        try {
            $conversation = Conversation::create([
                'user_id' => auth()->id(),
                'title' => 'Memo Review & Analysis',
                'type' => 'analysis',
                'message_count' => 0,
                'last_activity' => now(),
            ]);

            $userDataService = app(UserDataService::class);

            $entries = $userDataService->getRecentEntries(15);

            if ($entries->isEmpty()) {
                $aiResponse = "I notice you don't have many entries to analyze yet. That's perfectly fine! As you continue journaling, I'll be able to provide deeper insights into your patterns and growth over time.";
            } else {
                $formattedEntries = $userDataService->formatEntriesForAI($entries);
                $insights = $userDataService->getUserInsights();

                $prompt = "Analyze entries for:\n1. **Themes**\n2. **Emotional Patterns**\n3. **Growth Areas**\n4. **Triggers**\n5. **Strengths**\n6. **Recommendations**\n\nStats: {$insights['total_entries']} entries, {$insights['current_streak']} day streak, top tag: {$insights['most_used_tag']}\n\nEntries:\n" . $formattedEntries;

                $aiResponse = app(AiChatService::class)->generateResponse($prompt, $conversation->id);
            }

            Message::create([
                'conversation_id' => $conversation->id,
                'content' => $aiResponse,
                'is_ai_response' => true,
            ]);

            $conversation->update(['message_count' => 1, 'last_activity' => now()]);

            $this->isProcessing = null;
            return redirect()->route('chat.index');

        } catch (\Exception $e) {
            Log::error('Memo Review Error: ' . $e->getMessage());
            $this->isProcessing = null;
            session()->flash('error', 'Unable to review memos. Please try again.');
        }
    }

    /**
     * Start therapy session - personalized based on recent patterns
     */
    public function startTherapySession()
    {
        $this->isProcessing = 'therapy-session';
        $this->dispatch('start-therapy-async');
    }

    #[On('start-therapy-async')]
    public function startTherapyAsync()
    {
        \Log::info('Starting therapy session async');
        try {
            $conversation = Conversation::create([
                'user_id' => auth()->id(),
                'title' => 'Therapy Session',
                'type' => 'therapy',
                'message_count' => 0,
                'last_activity' => now(),
            ]);

            $userDataService = app(UserDataService::class);
            $recentEntries = $userDataService->getRecentEntries(2);

            if ($recentEntries->isEmpty()) {
                $prompt = "Start a warm therapeutic conversation. Ask one open question about what brought them here today.";
            } else {
                $formattedEntries = $userDataService->formatEntriesForAI($recentEntries);
                $prompt = "Based on recent entries, start a therapeutic conversation. Reference experiences subtly, ask one thoughtful question. Be warm and supportive.\n\n" . $formattedEntries;
            }

            $aiResponse = app(AiChatService::class)->generateResponse($prompt, $conversation->id);

            Message::create([
                'conversation_id' => $conversation->id,
                'content' => $aiResponse,
                'is_ai_response' => true,
            ]);

            $conversation->update(['message_count' => 1, 'last_activity' => now()]);

            $this->isProcessing = null;
            return redirect()->route('chat.index');

        } catch (\Exception $e) {
            Log::error('Therapy Session Error: ' . $e->getMessage());
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
        $this->weeklySummary = '';
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
