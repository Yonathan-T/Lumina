<?php

namespace App\Livewire;

use App\Services\PolarBillingService;
use Livewire\Component;

class Sidebar extends Component
{
    public string $currentPlan = 'free';

    public array $currentPlanMeta = [];

    public ?array $nextPlan = null;

    public function mount(PolarBillingService $billing): void
    {
        $user = auth()->user();
        $this->currentPlan = $user?->getCurrentPlan() ?? 'free';
        $this->currentPlanMeta = $billing->getPlanMeta($this->currentPlan);
        $this->nextPlan = $billing->resolveNextPlan($this->currentPlan);
    }

    public function render()
    {
        return view('livewire.sidebar');
    }
}
