<?php

namespace App\Livewire\Settings;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class ApiIntegration extends Component
{
    // Gemini API Key State
    public $apiKey = '';
    public $status = 'idle';
    public $statusMessage = '';
    public $isKeyVerified = false;
    public $hasTestedSuccessfully = false;

    // ElevenLabs API Key State
    public $elevenLabsKey = '';
    public $elevenLabsStatus = 'idle';
    public $elevenLabsStatusMessage = '';
    public $isElevenLabsKeyVerified = false;
    public $hasElevenLabsTestedSuccessfully = false;

    public function mount()
    {
        $user = auth()->user();

        // Load Gemini Key
        if ($user->api_key) {
            try {
                $this->apiKey = Crypt::decryptString($user->api_key);
                $this->isKeyVerified = !is_null($user->api_key_verified_at);
            } catch (DecryptException $e) {
                \Log::error('Gemini API key decryption failed: ' . $e->getMessage());
                $this->status = 'error';
                $this->statusMessage = 'Saved Gemini key is corrupted. Please re-enter.';
                $this->apiKey = '';
                $this->isKeyVerified = false;
            }
        }

        // Load ElevenLabs Key
        if ($user->elevenlabs_api_key) {
            try {
                $this->elevenLabsKey = Crypt::decryptString($user->elevenlabs_api_key);
                $this->isElevenLabsKeyVerified = !is_null($user->elevenlabs_api_key_verified_at);
            } catch (DecryptException $e) {
                \Log::error('ElevenLabs API key decryption failed: ' . $e->getMessage());
                $this->elevenLabsStatus = 'error';
                $this->elevenLabsStatusMessage = 'Saved ElevenLabs key is corrupted. Please re-enter.';
                $this->elevenLabsKey = '';
                $this->isElevenLabsKeyVerified = false;
            }
        }
    }

    // -------------------------------------------------------------
    // Gemini Actions
    // -------------------------------------------------------------

    public function testConnection()
    {
        if (empty(trim($this->apiKey))) {
            $this->status = 'error';
            $this->statusMessage = 'Please enter a Gemini API key first';
            return;
        }

        $this->status = 'testing';
        $this->statusMessage = 'Testing connection to Google Gemini...';

        try {
            $response = Http::get("https://generativelanguage.googleapis.com/v1/models?key={$this->apiKey}");

            if ($response->successful() && !empty($response->json('models'))) {
                $this->hasTestedSuccessfully = true;
                $this->isKeyVerified = true;

                auth()->user()->update([
                    'api_key_verified_at' => now(),
                    'api_key_tested_at' => now(),
                ]);

                $this->status = 'success';
                $this->statusMessage = 'Connection successful! You can now save.';
            } else {
                $this->hasTestedSuccessfully = false;
                $this->isKeyVerified = false;

                auth()->user()->update([
                    'api_key_verified_at' => null,
                    'api_key_tested_at' => now(),
                ]);

                $this->status = 'error';
                $this->statusMessage = 'Invalid or restricted Gemini API key.';
            }
        } catch (\Exception $e) {
            $this->hasTestedSuccessfully = false;
            $this->isKeyVerified = false;
            $this->status = 'error';
            $this->statusMessage = 'Connection failed: ' . $e->getMessage();
        }
    }

    public function saveApiKey()
    {
        if (empty(trim($this->apiKey))) {
            $this->status = 'error';
            $this->statusMessage = 'Gemini API key cannot be empty';
            return;
        }

        $user = auth()->user();
        $encrypted = Crypt::encryptString($this->apiKey);

        $currentPlaintext = $user->api_key ? Crypt::decryptString($user->api_key) : null;
        $keyChanged = $currentPlaintext !== $this->apiKey;

        $user->api_key = $encrypted;

        if ($keyChanged && !$this->hasTestedSuccessfully) {
            $user->api_key_verified_at = null;
            $user->api_key_tested_at = now();
            $this->isKeyVerified = false;
        } else {
            $user->api_key_verified_at = now();
            $this->isKeyVerified = true;
        }

        $user->save();

        $this->status = 'success';
        $this->statusMessage = $this->isKeyVerified
            ? 'Gemini API key saved and active!'
            : 'Gemini API key saved. Test connection to activate.';

        $this->dispatch('key-saved');
    }

    public function removeApiKey()
    {
        auth()->user()->update([
            'api_key' => null,
            'api_key_verified_at' => null,
            'api_key_tested_at' => null,
        ]);

        $this->apiKey = '';
        $this->isKeyVerified = false;
        $this->hasTestedSuccessfully = false;

        $this->status = 'success';
        $this->statusMessage = 'Gemini API key removed successfully';
        $this->dispatch('key-removed');
    }

    public function hasApiKey(): bool
    {
        return !empty(auth()->user()->api_key);
    }

    // -------------------------------------------------------------
    // ElevenLabs Actions
    // -------------------------------------------------------------

    public function testElevenLabsConnection()
    {
        if (empty(trim($this->elevenLabsKey))) {
            $this->elevenLabsStatus = 'error';
            $this->elevenLabsStatusMessage = 'Please enter an ElevenLabs API key first';
            return;
        }

        $this->elevenLabsStatus = 'testing';
        $this->elevenLabsStatusMessage = 'Testing connection to ElevenLabs...';

        try {
            $response = Http::withHeaders([
                'xi-api-key' => trim($this->elevenLabsKey),
            ])->get('https://api.elevenlabs.io/v1/user');

            if ($response->successful()) {
                $this->hasElevenLabsTestedSuccessfully = true;
                $this->isElevenLabsKeyVerified = true;

                auth()->user()->update([
                    'elevenlabs_api_key_verified_at' => now(),
                    'elevenlabs_api_key_tested_at' => now(),
                ]);

                $this->elevenLabsStatus = 'success';
                $this->elevenLabsStatusMessage = 'Connection successful! Your ElevenLabs key is valid.';
            } else {
                $this->hasElevenLabsTestedSuccessfully = false;
                $this->isElevenLabsKeyVerified = false;

                auth()->user()->update([
                    'elevenlabs_api_key_verified_at' => null,
                    'elevenlabs_api_key_tested_at' => now(),
                ]);

                $this->elevenLabsStatus = 'error';
                $this->elevenLabsStatusMessage = 'Invalid or unauthorized ElevenLabs API key.';
            }
        } catch (\Exception $e) {
            $this->hasElevenLabsTestedSuccessfully = false;
            $this->isElevenLabsKeyVerified = false;
            $this->elevenLabsStatus = 'error';
            $this->elevenLabsStatusMessage = 'Connection failed: ' . $e->getMessage();
        }
    }

    public function saveElevenLabsKey()
    {
        if (empty(trim($this->elevenLabsKey))) {
            $this->elevenLabsStatus = 'error';
            $this->elevenLabsStatusMessage = 'ElevenLabs API key cannot be empty';
            return;
        }

        $user = auth()->user();
        $encrypted = Crypt::encryptString(trim($this->elevenLabsKey));

        $currentPlaintext = $user->elevenlabs_api_key ? Crypt::decryptString($user->elevenlabs_api_key) : null;
        $keyChanged = $currentPlaintext !== trim($this->elevenLabsKey);

        $user->elevenlabs_api_key = $encrypted;

        if ($keyChanged && !$this->hasElevenLabsTestedSuccessfully) {
            $user->elevenlabs_api_key_verified_at = null;
            $user->elevenlabs_api_key_tested_at = now();
            $this->isElevenLabsKeyVerified = false;
        } else {
            $user->elevenlabs_api_key_verified_at = now();
            $this->isElevenLabsKeyVerified = true;
        }

        $user->save();

        $this->elevenLabsStatus = 'success';
        $this->elevenLabsStatusMessage = $this->isElevenLabsKeyVerified
            ? 'ElevenLabs API key saved and active!'
            : 'ElevenLabs API key saved. Test connection to activate.';

        $this->dispatch('elevenlabs-key-saved');
    }

    public function removeElevenLabsKey()
    {
        auth()->user()->update([
            'elevenlabs_api_key' => null,
            'elevenlabs_api_key_verified_at' => null,
            'elevenlabs_api_key_tested_at' => null,
        ]);

        $this->elevenLabsKey = '';
        $this->isElevenLabsKeyVerified = false;
        $this->hasElevenLabsTestedSuccessfully = false;

        $this->elevenLabsStatus = 'success';
        $this->elevenLabsStatusMessage = 'ElevenLabs API key removed successfully';
        $this->dispatch('elevenlabs-key-removed');
    }

    public function hasElevenLabsKey(): bool
    {
        return !empty(auth()->user()->elevenlabs_api_key);
    }

    public function render()
    {
        return view('livewire.settings.api-integration');
    }
}