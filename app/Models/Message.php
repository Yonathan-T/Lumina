<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'content',
        'is_ai_response',
        'metadata',
    ];

    protected $casts = [
        'is_ai_response' => 'boolean',
        'metadata' => 'array',
        'content' => 'encrypted',
    ];

    // Add accessor for compatibility with ChatInterface
    public function getIsAiAttribute()
    {
        return $this->is_ai_response;
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
