<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    /** @use HasFactory<\Database\Factories\EntryFactory> */
    use HasFactory;
    protected $fillable = [
        "title",
        "content",
        'user_id',
    ];
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
