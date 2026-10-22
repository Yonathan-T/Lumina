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
        // 1. If conversation relation is loaded
        if ($this->relationLoaded('conversation') && $this->conversation) {
            return $this->conversation->getUserEncrypter();
        }

        // 2. If conversation_id is present
        if (!empty($this->conversation_id)) {
            $conv = Conversation::find($this->conversation_id);
            if ($conv) {
                return $conv->getUserEncrypter();
            }
        }

        // 3. Fallback to active authenticated user in current request
        if (auth()->check()) {
            $user = auth()->user();
            if ($user && method_exists($user, 'getEncrypter')) {
                return $user->getEncrypter();
            }
        }

        return null;
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
