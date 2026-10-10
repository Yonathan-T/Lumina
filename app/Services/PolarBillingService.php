<?php

namespace App\Services;

use App\Models\Payment\PolarCustomer;
use App\Models\Payment\PolarSubscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PolarBillingService
{
    protected string $baseUrl = 'https://sandbox-api.polar.sh/v1';

    protected ?array $memoizedNormalizedProducts = null;
    protected ?string $memoizedProductsHash = null;

    public function fetchProducts(): array
    {
        return Cache::remember('polar_products_v1', now()->addHours(12), function () {
            $apiKey = env('POLAR_ACCESS_TOKEN');

            if (! $apiKey) {
                return [];
            }

            try {
                $response = $this->api()->get($this->baseUrl.'/products', [
                    'is_archived' => false,
                ]);

                if (! $response->ok()) {
                    Log::warning('Unable to fetch Polar products.', [
                        'status' => $response->status(),
                    ]);

                    return [];
                }

                $data = $response->json();

                return $data['items'] ?? [];
            } catch (\Throwable $e) {
                Log::warning('Exception fetching Polar products: '.$e->getMessage());

                return [];
            }
        });
    }

    public function getPlanMeta(string $planKey): array
    {
        $products = $this->normalizeProducts($this->fetchProducts());
        $currentProduct = $this->resolveCurrentPlanProduct($planKey, $products);

        $meta = [
            'free' => [
                'label' => 'Free',
                'description' => 'A calm place to begin journaling with the essentials.',
            ],
            'standard' => [
                'label' => 'Standard',
                'description' => 'More room, more reflection, and richer daily support.',
            ],
            'pro' => [
                'label' => 'Pro',
                'description' => 'Your full Lumina experience with everything unlocked.',
            ],
        ];

        $resolved = $meta[$planKey] ?? $meta['free'];
        $resolved['price'] = $currentProduct['price'] ?? match ($planKey) {
            'standard' => '$9/mo',
            'pro' => '$19/mo',
            default => '$0/mo',
        };
        $resolved['benefits'] = $currentProduct['benefits'] ?? [];
        $resolved['name'] = $currentProduct['name'] ?? $resolved['label'];

        return $resolved;
    }

    public function normalizeProducts(array $products): array
    {
        if (empty($products)) {
            return $this->defaultProducts();
        }

        $hash = md5(serialize($products));
        if ($this->memoizedProductsHash === $hash && $this->memoizedNormalizedProducts !== null) {
            return $this->memoizedNormalizedProducts;
        }

        $planOrder = ['free' => 0, 'standard' => 1, 'pro' => 2];

        $normalized = collect($products)
            ->map(function (array $product) {
                $name = strtolower($product['name'] ?? '');

                $key = str_contains($name, 'pro')
                    ? 'pro'
                    : (str_contains($name, 'standard') ? 'standard' : 'free');

                $price = collect($product['prices'] ?? [])->firstWhere('type', 'recurring')
                    ?? collect($product['prices'] ?? [])->first();

                $currency = strtoupper($price['price_currency'] ?? 'USD');
                $amount = (int) ($price['price_amount'] ?? 0);

                return [
                    'key' => $key,
                    'name' => $product['name'] ?? ucfirst($key),
                    'description' => $product['description'] ?? '',
                    'benefits' => collect($product['benefits'] ?? [])->pluck('description')->filter()->values()->all(),
                    'price_amount' => $amount,
                    'price_currency' => $currency,
                    'price' => $this->formatMoney($amount, $currency),
                    'checkout_url' => isset($price['id']) ? url('/checkout?priceId='.$price['id']) : route('pricing'),
                    'product_id' => $product['id'] ?? null,
                ];
            })
            ->sortBy(fn (array $product) => $planOrder[$product['key']] ?? 99)
            ->values()
            ->all();

        $this->memoizedProductsHash = $hash;
        $this->memoizedNormalizedProducts = $normalized;

        return $normalized;
    }

    public function defaultProducts(): array
    {
        return [
            [
                'key' => 'free',
                'name' => 'Free Journaler',
                'description' => 'A calm place to begin journaling with the essentials.',
                'benefits' => [
                    '10 entries per month',
                    'Core AI chat reflections',
                    'Light and dark sanctuary themes',
                ],
                'price_amount' => 0,
                'price_currency' => 'USD',
                'price' => '$0/mo',
                'checkout_url' => route('pricing'),
                'product_id' => 'free',
            ],
            [
                'key' => 'standard',
                'name' => 'Standard Journaler',
                'description' => 'More room, more reflection, and richer daily support.',
                'benefits' => [
                    'Unlimited entries',
                    'Guided reflection sessions',
                    'Continuous ambient sanctuary music',
                ],
                'price_amount' => 900,
                'price_currency' => 'USD',
                'price' => '$9/mo',
                'checkout_url' => route('pricing'),
                'product_id' => 'standard',
            ],
            [
                'key' => 'pro',
                'name' => 'Pro Thinker',
                'description' => 'Your full Lumina experience with everything unlocked.',
                'benefits' => [
                    'Everything in Standard',
                    'Deep memo pattern analysis',
                    'ElevenLabs voice audio synthesis',
                    'Priority AI reasoning speed',
                ],
                'price_amount' => 1900,
                'price_currency' => 'USD',
                'price' => '$19/mo',
                'checkout_url' => route('pricing'),
                'product_id' => 'pro',
            ],
        ];
    }

    public function clearNormalizedProductsMemo(): void
    {
        $this->memoizedNormalizedProducts = null;
        $this->memoizedProductsHash = null;
    }

    public function resolveCurrentPlanProduct(string $currentPlan, ?array $products = null): ?array
    {
        $products ??= $this->normalizeProducts($this->fetchProducts());

        return collect($products)->firstWhere('key', $currentPlan);
    }

    public function resolveNextPlan(string $currentPlan, ?array $products = null): ?array
    {
        $planOrder = ['free' => 0, 'standard' => 1, 'pro' => 2];
        $products ??= $this->normalizeProducts($this->fetchProducts());
        $currentTier = $planOrder[$currentPlan] ?? 0;

        return collect($products)->first(fn (array $product) => ($planOrder[$product['key']] ?? 99) > $currentTier);
    }

    public function syncCheckoutForUser(User $user, string $checkoutId): bool
    {
        $checkout = $this->fetchCheckout($checkoutId);

        if (! $checkout) {
            return false;
        }

        $customerId = $checkout['customer_id'] ?? null;

        if (! $customerId && ! empty($user->polar_customer_id)) {
            $customerId = $user->polar_customer_id;
        }

        if ($customerId) {
            $this->ensureCustomerLink($user, $customerId);
        }

        $subscription = null;
        $subscriptionId = $checkout['subscription_id'] ?? null;

        if ($subscriptionId) {
            $subscription = $this->fetchSubscription($subscriptionId);
        }

        if (! $subscription && $customerId) {
            $subscription = $this->fetchLatestActiveSubscription($customerId);
        }

        if (! $subscription) {
            Log::info('Polar checkout synced without subscription yet.', [
                'checkout_id' => $checkoutId,
                'user_id' => $user->id,
                'customer_id' => $customerId,
            ]);

            return false;
        }

        $this->syncSubscriptionRecord($user, $subscription, $customerId);

        return true;
    }

    public function syncUserState(User $user, bool $force = false): bool
    {
        $cacheKey = 'polar_customer_state_sync_user_'.$user->getKey();

        if (! $force && Cache::has($cacheKey)) {
            return (bool) Cache::get($cacheKey);
        }

        $result = $this->performUserStateSync($user);

        Cache::put($cacheKey, $result, now()->addSeconds(45));

        return $result;
    }

    protected function fetchCheckout(string $checkoutId): ?array
    {
        try {
            $response = $this->api()->get($this->baseUrl.'/checkouts/'.$checkoutId);

            if (! $response->ok()) {
                Log::warning('Unable to fetch Polar checkout session.', [
                    'checkout_id' => $checkoutId,
                    'status' => $response->status(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::warning('Error fetching Polar checkout session: '.$e->getMessage(), ['checkout_id' => $checkoutId]);

            return null;
        }
    }

    protected function fetchSubscription(string $subscriptionId): ?array
    {
        try {
            $response = $this->api()->get($this->baseUrl.'/subscriptions/'.$subscriptionId);

            if (! $response->ok()) {
                Log::warning('Unable to fetch Polar subscription.', [
                    'subscription_id' => $subscriptionId,
                    'status' => $response->status(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::warning('Error fetching Polar subscription: '.$e->getMessage(), ['subscription_id' => $subscriptionId]);

            return null;
        }
    }

    protected function performUserStateSync(User $user): bool
    {
        $state = $this->fetchCustomerStateByExternalId((string) $user->getKey());

        if (! $state && ! empty($user->polar_customer_id)) {
            $state = $this->fetchCustomerStateById((string) $user->polar_customer_id);
        }

        if (! $state && ! empty($user->email)) {
            $customer = $this->findCustomerByEmail($user->email);
            if ($customer) {
                $state = $this->fetchCustomerStateById((string) $customer['id']);
            }
        }

        if (! $state) {
            return false;
        }

        $customerId = $state['id'] ?? $user->polar_customer_id;

        if ($customerId) {
            $this->ensureCustomerLink($user, (string) $customerId);
        }

        $activeSubscription = collect($state['active_subscriptions'] ?? [])
            ->sortByDesc(fn (array $subscription) => $subscription['current_period_end'] ?? '')
            ->first();

        if (! $activeSubscription) {
            $user->forceFill(['is_subscribed' => false])->save();

            return false;
        }

        $this->syncSubscriptionRecord($user, $activeSubscription, $customerId ? (string) $customerId : null);

        return true;
    }

    protected function fetchCustomerStateByExternalId(string $externalId): ?array
    {
        try {
            $response = $this->api()->get($this->baseUrl.'/customers/external/'.$externalId.'/state');

            if ($response->status() === 404) {
                return null;
            }

            if (! $response->ok()) {
                Log::warning('Unable to fetch Polar customer state by external ID.', [
                    'external_id' => $externalId,
                    'status' => $response->status(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::warning('Error fetching Polar customer state by external ID: '.$e->getMessage(), ['external_id' => $externalId]);

            return null;
        }
    }

    protected function fetchCustomerStateById(string $customerId): ?array
    {
        try {
            $response = $this->api()->get($this->baseUrl.'/customers/'.$customerId.'/state');

            if ($response->status() === 404) {
                return null;
            }

            if (! $response->ok()) {
                Log::warning('Unable to fetch Polar customer state by customer ID.', [
                    'customer_id' => $customerId,
                    'status' => $response->status(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::warning('Error fetching Polar customer state by customer ID: '.$e->getMessage(), ['customer_id' => $customerId]);

            return null;
        }
    }

    protected function findCustomerByEmail(string $email): ?array
    {
        try {
            $response = $this->api()->get($this->baseUrl.'/customers', [
                'email' => $email,
                'limit' => 1,
            ]);

            if (! $response->ok()) {
                Log::warning('Unable to lookup Polar customer by email.', [
                    'email' => $email,
                    'status' => $response->status(),
                ]);

                return null;
            }

            return data_get($response->json(), 'items.0');
        } catch (\Throwable $e) {
            Log::warning('Error looking up Polar customer by email: '.$e->getMessage(), ['email' => $email]);

            return null;
        }
    }

    protected function fetchLatestActiveSubscription(string $customerId): ?array
    {
        try {
            $response = $this->api()->get($this->baseUrl.'/subscriptions', [
                'customer_id' => $customerId,
                'active' => true,
                'limit' => 1,
            ]);

            if (! $response->ok()) {
                Log::warning('Unable to list Polar subscriptions for customer.', [
                    'customer_id' => $customerId,
                    'status' => $response->status(),
                ]);

                return null;
            }

            return data_get($response->json(), 'items.0');
        } catch (\Throwable $e) {
            Log::warning('Error listing Polar subscriptions for customer: '.$e->getMessage(), ['customer_id' => $customerId]);

            return null;
        }
    }

    protected function syncSubscriptionRecord(User $user, array $subscription, ?string $customerId = null): void
    {
        $customerId ??= $subscription['customer_id'] ?? $user->polar_customer_id;

        if ($customerId) {
            $this->ensureCustomerLink($user, $customerId);
        }

        PolarSubscription::updateOrCreate(
            ['polar_id' => $subscription['id']],
            [
                'billable_type' => $user->getMorphClass(),
                'billable_id' => $user->getKey(),
                'type' => ! empty($subscription['recurring_interval']) ? 'recurring' : 'one_time',
                'status' => $subscription['status'] ?? 'active',
                'product_id' => $subscription['product_id'] ?? null,
                'current_period_end' => ! empty($subscription['current_period_end'])
                    ? Carbon::parse($subscription['current_period_end'])
                    : null,
                'trial_ends_at' => ! empty($subscription['trial_end'])
                    ? Carbon::parse($subscription['trial_end'])
                    : null,
                'ends_at' => ! empty($subscription['ends_at'])
                    ? Carbon::parse($subscription['ends_at'])
                    : null,
            ]
        );

        $user->forceFill([
            'polar_customer_id' => $customerId,
            'is_subscribed' => in_array($subscription['status'] ?? null, ['active', 'trialing'], true),
        ])->save();
    }

    protected function ensureCustomerLink(User $user, string $customerId): void
    {
        PolarCustomer::updateOrCreate(
            ['polar_id' => $customerId],
            [
                'billable_type' => $user->getMorphClass(),
                'billable_id' => $user->getKey(),
            ]
        );

        if ($user->polar_customer_id !== $customerId) {
            $user->forceFill(['polar_customer_id' => $customerId])->save();
        }
    }

    protected function api()
    {
        return Http::timeout(3.0)
            ->connectTimeout(2.0)
            ->withToken(env('POLAR_ACCESS_TOKEN'))
            ->acceptJson();
    }

    protected function formatMoney(int $amount, string $currency): string
    {
        if ($amount <= 0) {
            return '$0/mo';
        }

        $symbol = match ($currency) {
            'EUR' => 'EUR ',
            'GBP' => 'GBP ',
            default => '$',
        };

        return $symbol.number_format($amount / 100, $amount % 100 === 0 ? 0 : 2).'/mo';
    }
}
