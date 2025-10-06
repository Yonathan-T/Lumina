<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PolarOrder extends Model
{
    use HasFactory;

    protected $table = 'polar_orders';

    /**
     * The attributes that are mass assignable.
     * Includes all fields mapped from the Polar webhook payload and database schema.
     */
    protected $fillable = [
        // Polymorphic Linkage (REQUIRED by morphs('billable'))
        'billable_type',
        'billable_id', 
        
        // External IDs
        'polar_id',
        'customer_id',
        'product_id',

        // Order/Payment Details
        'status',
        'amount',
        'tax_amount',
        'refunded_amount',
        'refunded_tax_amount',
        'currency',
        'billing_reason',
        
        // Timestamps
        'refunded_at',
        'ordered_at',
    ];

    /**
     * The attributes that should be cast.
     * Ensures all amount fields are treated as integers and timestamps as Carbon instances.
     */
    protected $casts = [
        'ordered_at' => 'datetime',
        'refunded_at' => 'datetime',
        'amount' => 'integer',
        'tax_amount' => 'integer',
        'refunded_amount' => 'integer',
        'refunded_tax_amount' => 'integer',
    ];

    /**
     * Get the owning “billable” model (e.g., User) linked to this order via the polymorphic relationship.
     */
    public function billable(): MorphTo
    {
        return $this->morphTo();
    }
}
