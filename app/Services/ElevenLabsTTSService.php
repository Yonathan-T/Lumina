<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ElevenLabsTTSService
{
    protected ?string $apiKey;
    protected string $baseUrl = 'https://api.elevenlabs.io/v1';
    public ?string $lastError = null;

    public function __construct(?string $apiKey = null)
    {
        if ($apiKey) {
            $this->apiKey = $apiKey;
        } else {
            $user = auth()->user();
            $userKey = $user?->getElevenLabsApiKey();

            $this->apiKey = $userKey ?: config('services.elevenlabs.key');
        }
    }

    public function hasKey(): bool
    {
        return !empty($this->apiKey);
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function generateAudio($text, $voiceId = '21m00Tcm4TlvDq8ikWAM', $apiKey = null)
    {
        $this->lastError = null;
        \Log::info('Generating audio with text: ' . ($text ?: 'null'));

        try {
            if (empty(trim($text))) {
                throw new Exception('Text is empty or null.');
            }

            $effectiveApiKey = $apiKey ?: $this->apiKey;
            if (!$effectiveApiKey) {
                throw new Exception('No ElevenLabs API key provided. Please configure your key in Settings.');
            }

            if (strlen($text) > 500) {
                $text = substr($text, 0, 500) . '...';
                \Log::info('Text truncated to 500 characters to stay within quota');
            }

            $response = Http::withHeaders([
                'xi-api-key' => $effectiveApiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/text-to-speech/{$voiceId}", [
                'text' => $text, 
                'model_id' => 'eleven_multilingual_v2', 
                'voice_settings' => [
                    'stability' => 0.5,
                    'similarity_boost' => 0.5,
                ],
            ]);

            if ($response->failed()) {
                $json = $response->json();
                $errorMessage = $json['detail']['message'] ?? $json['detail']['status'] ?? $response->body();
                throw new Exception('ElevenLabs API error: ' . $errorMessage);
            }

            $audioContent = $response->body();

            $filename = 'audio/' . Str::uuid() . '.mp3';
            $diskName = config('filesystems.default') === 's3' ? 's3' : 'public';
            $disk = Storage::disk($diskName);
            /* @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk->put($filename, $audioContent);

            // Return a public URL for the stored file
            return $disk->url($filename);
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            \Log::error('ElevenLabs TTS failed: ' . $e->getMessage());
            return null;
        }
    }
}