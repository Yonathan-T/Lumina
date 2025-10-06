<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PolarCustomer extends Model
{
    
    protected $table = 'polar_customers';
    
    // Primary key is assumed to be 'id'

    /**
     * The attributes that are mass assignable.
     * These fields align strictly with the minimalist Polar customers migration.
     */
    protected $fillable = [
        // Polymorphic Linkage (REQUIRED by morphs('billable'))
        'billable_id',
        'billable_type',

        // External IDs and Fields
        'polar_id',
        'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    /**
     * Get the owning “billable” model (e.g., User) linked to this Polar customer.
     */
    public function billable(): MorphTo
    {
        return $this->morphTo();
    }
}
