<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Entry;

class DashboardStats extends Component
{
    public $totalEntries;
    public $recentEntries;
    public $loading = true;

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $this->totalEntries = Entry::where('user_id', auth()->id())->count();
        $this->recentEntries = Entry::where('user_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();
        $this->loading = false;
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-stats');
    }
}