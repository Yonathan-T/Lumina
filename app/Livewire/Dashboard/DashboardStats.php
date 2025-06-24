<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Entry;
use App\Models\Tag;
use DB;

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

    public function mount()
    {
        $this->loadStats();
        $this->calculateStreak();
    }

    public function loadStats()
    {
        $this->loading = true;

        $this->totalEntries = Entry::where('user_id', auth()->id())->count();


        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();
        $lastWeekEntries = Entry::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();

        $twoWeeksAgoStart = now()->subWeeks(2)->startOfWeek();
        $twoWeeksAgoEnd = now()->subWeeks(2)->endOfWeek();
        $twoWeeksAgoEntries = Entry::whereBetween('created_at', [$twoWeeksAgoStart, $twoWeeksAgoEnd])->count();

        $this->entriesFromLastWeek = $lastWeekEntries - $twoWeeksAgoEntries;
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

    public function calculateStreak()
    {
        $today = now()->startOfDay();
        $streak = 0;
        $lastEntryDate = null;

        $entries = Entry::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($entries as $entry) {
            $entryDate = $entry->created_at->startOfDay();

            if ($lastEntryDate === null) {
                if ($entryDate->isSameDay($today) || $entryDate->isSameDay($today->copy()->subDay())) {
                    $streak = 1;
                    $lastEntryDate = $entryDate;
                } else {
                    break;
                }
            } else {
                if ($entryDate->isSameDay($lastEntryDate->copy()->subDay())) {
                    $streak++;
                    $lastEntryDate = $entryDate;
                } else {
                    break;
                }
            }
        }

        $this->currentStreak = $streak;


        if ($streak === 0) {
            $this->streakMessage = "Start your streak today!";
        } elseif ($streak === 1) {
            $this->streakMessage = "First day of your streak!";
        } else {
            $this->streakMessage = "Keep it going!";
        }
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-stats');
    }
}