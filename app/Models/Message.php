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
    ];

    public function getContentAttribute($value)
    {
        if (empty($value)) {
            return '';
        }

        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Throwable $e) {
            if (is_string($value) && (str_starts_with($value, 'eyJ') || (str_contains($value, '"iv"') && str_contains($value, '"mac"')))) {
                return 'This message was encrypted with a previous key.';
            }
            return (string) $value;
        }
    }

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = \Illuminate\Support\Facades\Crypt::encryptString((string) $value);
    }

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
