<?php

namespace App\Livewire\Settings;

use App\Services\PolarBillingService;
use Livewire\Component;

class Subscription extends Component
{
    public string $currentPlan = 'free';

    public array $products = [];

    public array $currentPlanMeta = [];

    public ?array $currentProduct = null;

    public ?array $nextPlan = null;

    public function mount(PolarBillingService $billing): void
    {
        $user = auth()->user();
        if ($user && request()->boolean('refreshBilling') && request()->query('checkout_id')) {
            $billing->syncCheckoutForUser($user, (string) request()->query('checkout_id'));
            $user->refresh();
        }

        $this->currentPlan = $user?->getCurrentPlan() ?? 'free';
        $this->products = $billing->normalizeProducts($billing->fetchProducts());
        $this->currentPlanMeta = $billing->getPlanMeta($this->currentPlan);
        $this->currentProduct = $billing->resolveCurrentPlanProduct($this->currentPlan, $this->products);
        $this->nextPlan = $billing->resolveNextPlan($this->currentPlan, $this->products);
    }

    public function render()
    {
        return view('livewire.settings.subscription');
    }
}
