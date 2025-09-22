<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        // Add other fields from your 'plans' table here if needed
    ];

    /**
     * Get the users that belong to the plan.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
