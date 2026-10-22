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

    protected $casts = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

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

    public function getContentAttribute($value)
    {
        if (empty($value)) {
            return '';
        }

        // 1. Try decrypting with owning user's isolated DEK
        $userEnc = $this->getUserEncrypter();
        if ($userEnc) {
            try {
                return $userEnc->decryptString($value);
            } catch (\Throwable $e) { }
        }

        // 2. Fallback to master APP_KEY / APP_PREVIOUS_KEYS (for legacy records)
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Entry content decryption fallback for entry {$this->id}: " . $e->getMessage());
            if (is_string($value) && (str_starts_with($value, 'eyJ') || (str_contains($value, '"iv"') && str_contains($value, '"mac"')))) {
                return 'This reflection was encrypted with a previous key and cannot be decrypted.';
            }
            return (string) $value;
        }
    }

    public function getTitleAttribute($value)
    {
        if (empty($value)) {
            return 'Untitled Reflection';
        }

        // 1. Try decrypting with owning user's isolated DEK
        $userEnc = $this->getUserEncrypter();
        if ($userEnc) {
            try {
                return $userEnc->decryptString($value);
            } catch (\Throwable $e) { }
        }

        // 2. Fallback to master APP_KEY / APP_PREVIOUS_KEYS (for legacy records)
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Entry title decryption fallback for entry {$this->id}: " . $e->getMessage());
            if (is_string($value) && (str_starts_with($value, 'eyJ') || (str_contains($value, '"iv"') && str_contains($value, '"mac"')))) {
                return 'Reflection (Key Updated)';
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

    public function setTitleAttribute($value)
    {
        $userEnc = $this->getUserEncrypter();
        if ($userEnc) {
            $this->attributes['title'] = $userEnc->encryptString((string) $value);
        } else {
            $this->attributes['title'] = \Illuminate\Support\Facades\Crypt::encryptString((string) $value);
        }
    }
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

    /**
     * Get the base64-encoded data URI for the entry's banner (for PDF and offline rendering).
     */
    public function getBannerBase64Attribute(): ?string
    {
        if (empty($this->banner_path)) {
            return null;
        }

        try {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->banner_path)) {
                $bytes = \Illuminate\Support\Facades\Storage::disk('public')->get($this->banner_path);
                $mime = \Illuminate\Support\Facades\Storage::disk('public')->mimeType($this->banner_path) ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode($bytes);
            }
        } catch (\Throwable $e) {}

        try {
            $disk = self::getStorageDisk();
            if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($this->banner_path)) {
                $bytes = \Illuminate\Support\Facades\Storage::disk($disk)->get($this->banner_path);
                $mime = \Illuminate\Support\Facades\Storage::disk($disk)->mimeType($this->banner_path) ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode($bytes);
            }
        } catch (\Throwable $e) {}

        try {
            if ($this->banner_url) {
                $resp = \Illuminate\Support\Facades\Http::timeout(5)->get($this->banner_url);
                if ($resp->successful()) {
                    $mime = $resp->header('Content-Type') ?: 'image/jpeg';
                    return 'data:' . $mime . ';base64,' . base64_encode($resp->body());
                }
            }
        } catch (\Throwable $e) {}

        return null;
    }
}
