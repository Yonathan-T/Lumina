<?php

namespace App\Models\Payment;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
/**
 * @property string $polar_subscription_id Unique ID from Polar
 * @property string $polar_customer_id Foreign key to PolarCustomer
 * @property string $status Status (e.g., active, canceled, revoked, paused)
 * @property string $product_id Polar's ID for the product plan
 * @property \DateTimeInterface|null $current_period_end Subscription renewal date
 */
class PolarSubscription extends Model
{
    use HasFactory;

    protected $table = 'polar_subscriptions';

    // Use default auto-increment primary key
    protected $fillable = [
        'billable_type',
        'billable_id',
        'polar_id',
        'type',
        'status',
        'product_id',
        'current_period_end',
        'trial_ends_at',
        'ends_at',
        // Add other fields from your subscriptions table
    ];

    protected $casts = [
        'current_period_end' => 'datetime',
    ];

    /**
     * Get the customer that owns the subscription.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(PolarCustomer::class, 'customer_id', 'polar_id');
    }
    public function isActive(): bool
    {
        return $this->status === 'active' &&
            $this->current_period_end instanceof Carbon &&
            $this->current_period_end->isFuture();
    }

}
