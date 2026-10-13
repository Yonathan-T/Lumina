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
        'banner_path',
    ];

    protected $casts = [
        'content' => 'encrypted',
        'title' => 'encrypted',
    ];
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Resolve the storage disk for banners (s3/supabase if configured, else public).
     */
    public static function getStorageDisk(): string
    {
        if (app()->environment('testing')) {
            return config('filesystems.default') === 's3' ? 's3' : 'public';
        }

        if (config('filesystems.default') === 's3') {
            return 's3';
        }

        if (!empty(config('filesystems.disks.s3.key')) && !empty(config('filesystems.disks.s3.bucket'))) {
            return 's3';
        }

        return 'public';
    }

    /**
     * Get the accessible public URL for the entry's banner.
     */
    public function getBannerUrlAttribute(): ?string
    {
        if (empty($this->banner_path)) {
            return null;
        }

        if (str_starts_with($this->banner_path, 'http://') || str_starts_with($this->banner_path, 'https://')) {
            return $this->banner_path;
        }

        try {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->banner_path)) {
                return \Illuminate\Support\Facades\Storage::disk('public')->url($this->banner_path);
            }
        } catch (\Throwable $e) {}

        $disk = self::getStorageDisk();

        try {
            return \Illuminate\Support\Facades\Storage::disk($disk)->url($this->banner_path);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
