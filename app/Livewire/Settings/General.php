<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class General extends Component
{
    public $sessions = [];

    public function mount()
    {
        $this->sessions = DB::table('sessions')
            ->where('user_id', auth()->id())
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'ip_address' => $session->ip_address,
                    'user_agent' => $session->user_agent,
                    'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                    'is_current_device' => $session->id === session()->getId(),
                    'device' => Str::limit($session->user_agent, 40),
                ];
            });
    }

    public function logoutSession($sessionId)
    {
        DB::table('sessions')->where('id', $sessionId)->delete();
        $this->mount();
    }

    public function render()
    {
        return view('livewire.settings.general');
    }
}
