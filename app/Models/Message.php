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

    public function getUserEncrypter(): ?\Illuminate\Encryption\Encrypter
    {
        $conv = $this->relationLoaded('conversation') ? $this->conversation : $this->conversation()->first();
        return $conv?->getUserEncrypter();
    }

    public function getContentAttribute($value)
    {
        if (empty($value)) {
            return '';
        }

        // 1. Try decrypting with user's isolated DEK
        $userEnc = $this->getUserEncrypter();
        if ($userEnc) {
            try {
                return $userEnc->decryptString($value);
            } catch (\Throwable $e) { }
        }

        // 2. Fallback to master APP_KEY / APP_PREVIOUS_KEYS
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
        $userEnc = $this->getUserEncrypter();
        if ($userEnc) {
            $this->attributes['content'] = $userEnc->encryptString((string) $value);
        } else {
            $this->attributes['content'] = \Illuminate\Support\Facades\Crypt::encryptString((string) $value);
        }
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
