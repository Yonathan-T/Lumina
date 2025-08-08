<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Entry;
use App\Models\Tag;
use App\Models\User;
use DB;
use App\Services\StreakService;
use Illuminate\Notifications\DatabaseNotification;

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
    public $isModalOpen = false;

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
            $this->streakMessage = "Start your streak today!";
        } elseif ($this->currentStreak === 1) {
            $this->streakMessage = "First day of your streak!";
        } else {
            $this->streakMessage = "Keep it going!";
        }
    }

    public function loadStats()
    {
        $this->loading = true;

        $this->totalEntries = Entry::where('user_id', auth()->id())->count();

        $thisWeekStart = now()->startOfWeek();
        $thisWeekEnd = now()->endOfWeek();
        $thisWeekEntries = Entry::whereBetween('created_at', [$thisWeekStart, $thisWeekEnd])->count();

        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();
        $lastWeekEntries = Entry::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();

        $this->entriesFromLastWeek = $thisWeekEntries - $lastWeekEntries;

        //Tag Section
        $this->mostUsedTag = Tag::select('tags.*')
            ->join('entry_tag', 'tags.id', '=', 'entry_tag.tag_id')
            ->join('entries', 'entry_tag.entry_id', '=', 'entries.id')
            ->where('entries.user_id', auth()->id())
            ->groupBy('tags.id')
            ->orderByRaw('COUNT(*) DESC')
            ->first();

        $this->mostUsedTagCount = $this->mostUsedTag
            ? DB::table('entry_tag')
                ->join('entries', 'entry_tag.entry_id', '=', 'entries.id')
                ->where('entry_tag.tag_id', $this->mostUsedTag->id)
                ->where('entries.user_id', auth()->id())
                ->count()
            : 0;

        // longest entry sec
        $entries = Entry::where('user_id', auth()->id())->get();

        if ($entries->isEmpty()) {
            $this->longestEntry = null;
            $this->longestEntryCharCount = 0;
            $this->longestEntryDate = null;
            return;
        }

        $this->longestEntry = $entries
            ->map(function ($entry) {
                $cleaned = preg_replace('/\s+/', '', $entry->content ?? '');
                return [
                    'entry' => $entry,
                    'char_count' => strlen($cleaned)
                ];
            })
            ->sortByDesc('char_count')
            ->first();

        $this->longestEntryCharCount = $this->longestEntry['char_count'];
        $this->longestEntryDate = $this->longestEntry['entry']->created_at->format('M d, Y');


        //recent entry sec
        $this->recentEntries = Entry::where('user_id', auth()->id())
            ->with('tags')
            ->latest()
            ->take(1)
            ->get();
        $this->loading = false;
    }

    /**
     * Toggles the notification modal.
     */
    public function toggleNotificationsModal()
    {
        $this->isModalOpen = !$this->isModalOpen;
    }

    /**
     * Marks a specific notification as read.
     *
     * @param  string $notificationId
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
     * @param  string $notificationId
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
        // Fetch all notifications, but we'll show unread ones first
        $this->notifications = $user->notifications()->latest()->take(10)->get();
        $this->unreadCount = $user->unreadNotifications()->count();
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
