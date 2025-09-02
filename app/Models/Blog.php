<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'external_url',
        'source_name',
        'source_url',
        'published_at',
        'category',
        'tags',
        'image_url',
        'cached_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'cached_at' => 'datetime',
        'tags' => 'array'
    ];

    public function scopeRecent($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source_name', $source);
    }

    public function getTimeAgoAttribute()
    {
        return $this->published_at->diffForHumans();
    }

    public function getExcerptAttribute()
    {
        return strlen($this->description) > 150
            ? substr($this->description, 0, 150) . '...'
            : $this->description;
    }

    public function isFresh()
    {
        return $this->cached_at && $this->cached_at->gt(now()->subHours(24));
    }
}
