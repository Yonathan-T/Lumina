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
            // Delete all messages when conversation is deleted
            $conversation->messages()->delete();
        });
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }


}
