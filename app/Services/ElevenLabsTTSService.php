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

    public function generateAudio($text, $voiceId = 'EXAVITQu4vr4xnSDxMaL', $apiKey = null)
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

            // If the voice was blocked as a restricted library voice for free users,
            // seamlessly fall back to Bella (EXAVITQu4vr4xnSDxMaL) so free accounts don't fail!
            if ($response->failed() && ($response->status() === 402 || str_contains($response->body(), 'library voices'))) {
                \Log::info("Voice {$voiceId} restricted as library voice. Falling back to default free voice Bella.");
                $response = Http::withHeaders([
                    'xi-api-key' => $effectiveApiKey,
                    'Content-Type' => 'application/json',
                ])->post("{$this->baseUrl}/text-to-speech/EXAVITQu4vr4xnSDxMaL", [
                    'text' => $text,
                    'model_id' => 'eleven_multilingual_v2',
                    'voice_settings' => [
                        'stability' => 0.5,
                        'similarity_boost' => 0.5,
                    ],
                ]);
            }

            if ($response->failed()) {
                $status = $response->status();
                $body = $response->body();
                $json = $response->json();
                $rawMsg = $json['detail']['message'] ?? $json['detail']['status'] ?? $body;

                if (str_contains(strtolower($rawMsg), 'quota_exceeded') || str_contains(strtolower($rawMsg), 'quota')) {
                    $friendlyMsg = 'Your ElevenLabs character quota has been reached for this month. Please check your ElevenLabs account.';
                } elseif ($status === 401 || str_contains(strtolower($rawMsg), 'invalid') || str_contains(strtolower($rawMsg), 'unauthorized')) {
                    $friendlyMsg = 'Invalid or unauthorized ElevenLabs API key. Please check your key in Settings.';
                } elseif ($status === 402 || str_contains($rawMsg, 'library voices')) {
                    $friendlyMsg = 'This voice requires paid ElevenLabs credits. Free accounts can use standard voices.';
                } else {
                    $friendlyMsg = is_string($rawMsg) ? $rawMsg : 'Failed to generate voice audio.';
                }

                throw new Exception($friendlyMsg);
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