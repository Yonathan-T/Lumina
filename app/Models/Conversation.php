<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'type',
        'message_count',
        'last_activity',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
    ];

    public function getUserEncrypter(): ?\Illuminate\Encryption\Encrypter
    {
        $userId = $this->user_id ?? (auth()->check() ? auth()->id() : null);
        if ($userId) {
            $user = $this->relationLoaded('user') ? $this->user : User::find($userId);
            if ($user && method_exists($user, 'getEncrypter')) {
                return $user->getEncrypter();
            }
        }
        return null;
    }

    public function getTitleAttribute($value)
    {
        if (empty($value)) {
            return 'Conversation';
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
                return 'Conversation (Archived)';
            }
            return (string) $value;
        }
    }

    public function setTitleAttribute($value)
    {
        $userEnc = $this->getUserEncrypter();
        if ($userEnc) {
            $this->attributes['title'] = $userEnc->encryptString((string) $value);
        } else {
            $this->attributes['title'] = \Illuminate\Support\Facades\Crypt::encryptString((string) $value);
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    protected static function booted()
    {
        static::deleting(function ($conversation) {
            $conversation->messages()->delete();
        });
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }


}
