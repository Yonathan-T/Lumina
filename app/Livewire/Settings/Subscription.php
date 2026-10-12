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
            try {
                $billing->syncCheckoutForUser($user, (string) request()->query('checkout_id'));
                $user->refresh();
            } catch (\Throwable $e) {
                \Log::warning('Checkout sync failed: ' . $e->getMessage());
            }
        }

        try {
            $this->currentPlan = $user?->getCurrentPlan() ?? 'free';
            $this->products = $billing->normalizeProducts($billing->fetchProducts());
            $this->currentPlanMeta = $billing->getPlanMeta($this->currentPlan);
            $this->currentProduct = $billing->resolveCurrentPlanProduct($this->currentPlan, $this->products);
            $this->nextPlan = $billing->resolveNextPlan($this->currentPlan, $this->products);
        } catch (\Throwable $e) {
            \Log::warning('Subscription mount failed: ' . $e->getMessage());
            $this->currentPlan = 'free';
            $this->products = [];
            $this->currentPlanMeta = $billing->getPlanMeta('free');
            $this->currentProduct = null;
            $this->nextPlan = null;
        }
    }

    public function render()
    {
        return view('livewire.settings.subscription');
    }
}
