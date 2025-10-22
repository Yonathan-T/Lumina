<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class ElevenLabsTTSService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.elevenlabs.io/v1';

    public function __construct()
    {
        $this->apiKey = config('services.elevenlabs.key');
    }

    public function generateAudio($text, $voiceId = '21m00Tcm4TlvDq8ikWAM', $apiKey = null)
    {
        // Debug log to check input
        \Log::info('Generating audio with text: ' . ($text ?: 'null'));

        try {
            // Validate text
            if (empty(trim($text))) {
                throw new Exception('Text is empty or null.');
            }

            // Use user-provided key or fallback to app's key
            $effectiveApiKey = $apiKey ?: $this->apiKey;
            if (!$effectiveApiKey) {
                throw new Exception('No API key provided.');
            }

            // Truncate to 500 chars to stay within quota limits
            if (strlen($text) > 500) {
                $text = substr($text, 0, 500) . '...';
                \Log::info('Text truncated to 500 characters to stay within quota');
            }

            $response = Http::withHeaders([
                'xi-api-key' => $effectiveApiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/text-to-speech/{$voiceId}", [
                'text' => $text, // Explicitly set the text field
                'model_id' => 'eleven_monolingual_v1', // Free tier model
                'voice_settings' => [
                    'stability' => 0.5,
                    'similarity_boost' => 0.5,
                ],
            ]);

            if ($response->failed()) {
                throw new Exception('API request failed: ' . $response->body());
            }

            $audioContent = $response->body();

            $filename = 'audio/' . Str::uuid() . '.mp3';
            $disk = Storage::disk('public');
            /* @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk->put($filename, $audioContent);

            // Return a public URL for the stored file (use asset() so static analysis doesn't require Filesystem::url())
            return asset('storage/' . $filename);
        } catch (Exception $e) {
            \Log::error('ElevenLabs TTS failed: ' . $e->getMessage());
            return null;
        }
    }
}