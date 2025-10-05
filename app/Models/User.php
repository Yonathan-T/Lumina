<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Danestves\LaravelPolar\Billable;
use App\Notifications\CustomResetPassword;
use App\Services\StreakService;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Illuminate\Database\Eloquent\Relations\MorphMany;
   use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Payment\PolarSubscription;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasPushSubscriptions, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'reminder_time',
        'settings',
        // --- CRITICAL POLAR WEBHOOK FIELDS ADDED BELOW ---
        'polar_customer_id',
        'is_subscribed',
        // ---
    ];
    
    public function hasEntryToday()
    {
        return StreakService::getCurrentStreak($this) > 0 && now()->isSameDay(StreakService::getLastEntryDate($this));
    }

    public function getCurrentStreak()
    {
        return StreakService::getCurrentStreak($this);
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array',
            'is_subscribed' => 'boolean',
        ];
    }
    public function entries()
    {
        return $this->hasMany(Entry::class);
    }
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }

    /**
     * Subscriptions linked to this user via the billable morph.
     */
    public function subscriptions(): MorphMany
    {
        return $this->morphMany(PolarSubscription::class, 'billable');
    }
    public function latestSubscription(): MorphOne
    {
        return $this->morphOne(PolarSubscription::class, 'billable')->latestOfMany();
    }

    /**
     * Check if user has an active subscription
     */
    public function hasActiveSubscription(): bool
    {
        $subscription = $this->latestSubscription;
        return $subscription
            && $subscription->status === 'active'
            && $subscription->current_period_end?->isFuture();
    }

    /**
     * Check if user has a specific plan by product ID
     */
    public function hasPlan(string $planId): bool
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('product_id', $planId)
            ->where('current_period_end', '>', now())
            ->exists();
    }

    /**
     * Get the user's current plan key (free, reflector_pro, mindful_master)
     */
    public function getCurrentPlan(): string
    {
        if (!$this->hasActiveSubscription()) {
            return 'free';
        }

        $subscription = $this->latestSubscription;
        $productId = $subscription->product_id;

        // Check which plan this product_id belongs to
        $plans = config('subscription_plans.plans');
        foreach ($plans as $planKey => $planConfig) {
            if (in_array($productId, $planConfig['product_ids'] ?? [])) {
                return $planKey;
            }
        }

        // Default to free if no matching plan found
        return 'free';
    }

    /**
     * Check if user can access a specific feature
     */
    public function canAccess(string $feature): bool
    {
        $currentPlan = $this->getCurrentPlan();
        $planConfig = config("subscription_plans.plans.{$currentPlan}");
        
        return $planConfig['features'][$feature] ?? false;
    }

    /**
     * Get the user's plan configuration
     */
    public function getPlanConfig(): array
    {
        $currentPlan = $this->getCurrentPlan();
        return config("subscription_plans.plans.{$currentPlan}", []);
    }

    /**
     * Check if user has reached their monthly entry limit
     */
    public function hasReachedEntryLimit(): bool
    {
        if ($this->canAccess('unlimited_entries')) {
            return false;
        }

        $limit = $this->getPlanConfig()['features']['entries_per_month'] ?? 0;
        if ($limit === -1) { // Unlimited
            return false;
        }

        $entriesThisMonth = $this->entries()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return $entriesThisMonth >= $limit;
    }

    /**
     * Get remaining entries for this month
     */
    public function getRemainingEntries(): int
    {
        if ($this->canAccess('unlimited_entries')) {
            return -1; // Unlimited
        }

        $limit = $this->getPlanConfig()['features']['entries_per_month'] ?? 0;
        if ($limit === -1) {
            return -1;
        }

        $entriesThisMonth = $this->entries()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return max(0, $limit - $entriesThisMonth);
    }

    /**
     * Check if user has specific plan level or higher
     */
    public function hasMinimumPlan(string $requiredPlan): bool
    {
        $planHierarchy = ['free', 'reflector_pro', 'mindful_master'];
        $currentPlanLevel = array_search($this->getCurrentPlan(), $planHierarchy);
        $requiredPlanLevel = array_search($requiredPlan, $planHierarchy);
        
        return $currentPlanLevel !== false && $requiredPlanLevel !== false && $currentPlanLevel >= $requiredPlanLevel;
    }

}
