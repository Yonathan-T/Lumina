<?php

namespace App\Livewire\Dashboard;

use App\Models\Entry;
use App\Models\Tag;
use App\Models\User;
use App\Services\StreakService;
use App\Services\UserDataService;
use DB;
use Illuminate\Notifications\DatabaseNotification;
use Livewire\Component;

class DashboardStats extends Component
{
    public $totalEntries;

    public $entriesFromLastWeek;

    public $recentEntries;

    public $loading = true;

    public $mostUsedTag;

    public $mostUsedTagCount;

    public $longestEntry;

    public $longestEntryCharCount;

    public $longestEntryDate;

    public $currentStreak;

    public $streakMessage;

    public $notifications;

    public $unreadCount;

    public function getPollingIntervalProperty()
    {
        return 30000; // 30 seconds
    }

    // Remove the dynamic event listener that was causing issues
    // #[On('echo:users.{auth()->id()},.Illuminate\\Notifications\\Events\\NotificationSent')]
    // public function refreshNotifications()
    // {
    //     $this->loadNotifications();
    // }

    public function mount()
    {
        $this->loadNotifications();
        $this->loadStats();
        $this->currentStreak = StreakService::getCurrentStreak(auth()->id());
        if ($this->currentStreak === 0) {
            $this->streakMessage = 'Start your streak today!';
        } elseif ($this->currentStreak === 1) {
            $this->streakMessage = 'First day of your streak!';
        } else {
            $this->streakMessage = 'Keep it going!';
        }
    }

    public function loadStats()
    {
        $this->loading = true;
        $userId = auth()->id();
        $userDataService = app(UserDataService::class);

        $thisWeekStart = now()->startOfWeek();
        $thisWeekEnd = now()->endOfWeek();
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();

        $entryStats = Entry::where('user_id', $userId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(CASE WHEN created_at >= ? AND created_at <= ? THEN 1 END) as this_week', [$thisWeekStart, $thisWeekEnd])
            ->selectRaw('COUNT(CASE WHEN created_at >= ? AND created_at <= ? THEN 1 END) as last_week', [$lastWeekStart, $lastWeekEnd])
            ->first();

        $this->totalEntries = (int) ($entryStats->total ?? 0);
        $thisWeekEntries = (int) ($entryStats->this_week ?? 0);
        $lastWeekEntries = (int) ($entryStats->last_week ?? 0);
        $this->entriesFromLastWeek = $thisWeekEntries - $lastWeekEntries;

        // Tag Section
        $this->mostUsedTag = Tag::join('entry_tag', 'tags.id', '=', 'entry_tag.tag_id')
            ->join('entries', 'entry_tag.entry_id', '=', 'entries.id')
            ->where('entries.user_id', $userId)
            ->groupBy('tags.id', 'tags.name')
            ->select('tags.id', 'tags.name', DB::raw('COUNT(*) as usage_count'))
            ->orderByRaw('COUNT(*) DESC')
            ->first();

        $this->mostUsedTagCount = $this->mostUsedTag ? (int) $this->mostUsedTag->usage_count : 0;

        // Longest entry section (computed in DB rather than hydrating all full entries)
        $longest = Entry::where('user_id', $userId)
            ->select(['id', 'created_at'])
            ->selectRaw('LENGTH(content) as char_count')
            ->orderByRaw('LENGTH(content) DESC')
            ->first();

        if (! $longest) {
            $this->longestEntry = null;
            $this->longestEntryCharCount = 0;
            $this->longestEntryDate = null;
        } else {
            $this->longestEntry = [
                'entry' => $longest,
                'char_count' => (int) $longest->char_count,
            ];
            $this->longestEntryCharCount = (int) $longest->char_count;
            $this->longestEntryDate = $longest->created_at?->format('M d, Y');
        }

        // recent entry sec
        $lastEntry = $userDataService->getLastEntry();
        $this->recentEntries = $lastEntry ? collect([$lastEntry]) : collect();
        $this->loading = false;
    }

    /**
     * Marks a specific notification as read.
     *
     * @return void
     */
    public function markAsRead(string $notificationId)
    {
        auth()->user()->notifications->where('id', $notificationId)->markAsRead();
        $this->loadNotifications();
    }

    /**
     * Marks all unread notifications as read.
     *
     * @return void
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    /**
     * Deletes a specific notification.
     *
     * @return void
     */
    public function deleteNotification(string $notificationId)
    {
        DatabaseNotification::find($notificationId)->delete();
        $this->loadNotifications();
    }

    /**
     * Deletes all notifications.
     *
     * @return void
     */
    public function deleteAllNotifications()
    {
        auth()->user()->notifications()->delete();
        $this->loadNotifications();
    }

    /**
     * Fetches the user's notifications and updates the component properties.
     *
     * @return void
     */
    protected function loadNotifications()
    {
        $user = auth()->user();
        $this->notifications = $user->notifications()->latest()->take(10)->get();
        $this->unreadCount = $this->notifications->whereNull('read_at')->count();
    }

    /**
     * Refreshes the component data including notifications and stats.
     *
     * @return void
     */
    public function refresh()
    {
        $this->loadNotifications();
        $this->loadStats();
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-stats');
    }
}
