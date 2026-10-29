<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntryEmbedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'entry_id',
        'model',
        'dimensions',
        'embedding',
    ];

    protected $casts = [
        'embedding' => 'array',
        'dimensions' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entry()
    {
        return $this->belongsTo(Entry::class);
    }
}
