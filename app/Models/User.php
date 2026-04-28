<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Payment\PolarSubscription;
use App\Notifications\CustomResetPassword;
use App\Services\PolarBillingService;
use App\Services\StreakService;
use Danestves\LaravelPolar\Billable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Billable, HasFactory, HasPushSubscriptions, Notifiable;

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
        'api_key',
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

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => is_string($value) ? strtolower(trim($value)) : $value,
        );
    }

    public static function normalizeEmailValue(?string $email): ?string
    {
        return is_string($email) ? strtolower(trim($email)) : null;
    }

    public static function normalizeStoredEmailRecord(?string $email): void
    {
        $normalizedEmail = static::normalizeEmailValue($email);

        if (! $normalizedEmail) {
            return;
        }

        $matches = static::query()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->get(['id', 'email']);

        if ($matches->count() !== 1) {
            return;
        }

        $user = $matches->first();

        if ($user && $user->email !== $normalizedEmail) {
            $user->forceFill(['email' => $normalizedEmail])->save();
        }
    }

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

    protected function qualifyingSubscriptionQuery(): MorphMany
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($query) {
                $query->where('current_period_end', '>', now())
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereNull('current_period_end')
                            ->where(function ($endQuery) {
                                $endQuery->whereNull('ends_at')
                                    ->orWhere('ends_at', '>', now());
                            });
                    });
            });
    }

    protected function refreshBillingStateIfNeeded(): void
    {
        $hasLocalSubscription = $this->qualifyingSubscriptionQuery()->exists();

        if ($hasLocalSubscription) {
            return;
        }

        app(PolarBillingService::class)->syncUserState($this);
        $this->unsetRelation('subscriptions');
    }

    /**
     * Check if user has an active subscription
     */
    public function hasActiveSubscription(): bool
    {
        $this->refreshBillingStateIfNeeded();

        $subscription = $this->qualifyingSubscriptionQuery()
            ->latest()
            ->first();

        return $subscription !== null;
    }

    /**
     * Check if user has a specific plan by product ID
     */
    public function hasPlan(string $planId): bool
    {
        $this->refreshBillingStateIfNeeded();

        return $this->qualifyingSubscriptionQuery()
            ->where('product_id', $planId)
            ->exists();
    }

    /**
     * Get the user's current plan key (free, standard, pro)
     */
    public function getCurrentPlan(): string
    {
        $this->refreshBillingStateIfNeeded();

        $subscription = $this->qualifyingSubscriptionQuery()
            ->latest()
            ->first();

        if (! $subscription) {
            return 'free';
        }

        $productId = $subscription->product_id;

        // Simple mapping using .env variables
        if ($productId === env('POLAR_STANDARD_PRODUCT_ID')) {
            return 'standard';
        }

        if ($productId === env('POLAR_PRO_PRODUCT_ID')) {
            return 'pro';
        }

        // Default to standard if it's an active subscription but unknown product ID
        return 'standard';
    }

    /**
     * Check if user has specific plan level or higher
     */
    public function hasMinimumPlan(string $requiredPlan): bool
    {
        $currentPlan = $this->getCurrentPlan();

        // Simple tier hierarchy: free=0, standard=1, pro=2
        $tiers = ['free' => 0, 'standard' => 1, 'pro' => 2];

        $currentTier = $tiers[$currentPlan] ?? 0;
        $requiredTier = $tiers[$requiredPlan] ?? 0;

        return $currentTier >= $requiredTier;
    }

    /**
     * Check if user has unlimited entries (Pro plan only)
     */
    public function hasUnlimitedEntries(): bool
    {
        return $this->getCurrentPlan() === 'pro';
    }

    /**
     * Get monthly entry limit based on plan
     */
    public function getMonthlyEntryLimit(): int
    {
        $plan = $this->getCurrentPlan();

        return match ($plan) {
            'free' => 10,
            'standard' => 100,
            'pro' => -1, // Unlimited
            default => 10
        };
    }
}
