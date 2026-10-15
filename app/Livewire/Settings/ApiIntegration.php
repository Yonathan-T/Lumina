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
    public $originalSavedGeminiKey = '';
    public $status = 'idle';
    public $statusMessage = '';
    public $isKeyVerified = false;
    public $hasTestedSuccessfully = false;
    public $hasSavedGeminiKey = false;

    // ElevenLabs API Key State
    public $elevenLabsKey = '';
    public $originalSavedElevenLabsKey = '';
    public $elevenLabsStatus = 'idle';
    public $elevenLabsStatusMessage = '';
    public $isElevenLabsKeyVerified = false;
    public $hasElevenLabsTestedSuccessfully = false;
    public $hasSavedElevenLabsKey = false;

    // ElevenLabs Voice Selection State
    public $selectedVoiceId = 'EXAVITQu4vr4xnSDxMaL';
    public $customVoiceId = '';
    public $voiceSavedMessage = '';

    public static array $presetVoices = [
        [
            'id' => 'EXAVITQu4vr4xnSDxMaL',
            'name' => 'Bella (Default)',
            'description' => 'Female, gentle, natural',
            'tier' => 'Free',
        ],
        [
            'id' => 'pNInz6obpgDQGcFmaJgB',
            'name' => 'Adam',
            'description' => 'Male, deep, narrative',
            'tier' => 'Free',
        ],
        [
            'id' => 'ErXwobaYiN019PkySvjV',
            'name' => 'Antoni',
            'description' => 'Male, warm, conversational',
            'tier' => 'Free',
        ],
        [
            'id' => 'VR6AewLTigWG4xSOukaG',
            'name' => 'Arnold',
            'description' => 'Male, crisp, confident',
            'tier' => 'Free',
        ],
        [
            'id' => '21m00Tcm4TlvDq8ikWAM',
            'name' => 'Rachel',
            'description' => 'Female, calm, professional',
            'tier' => 'Paid credits',
        ],
        [
            'id' => 'AZnzlk1XvdvUeBnXmlld',
            'name' => 'Domi',
            'description' => 'Female, strong, lively',
            'tier' => 'Paid credits',
        ],
        [
            'id' => 'MF3mGyEYCl7XYWbV9V6O',
            'name' => 'Elli',
            'description' => 'Female, emotional, thoughtful & young',
            'tier' => 'Paid credits',
        ],
        [
            'id' => 'TxGEqnHWrfWFTfGW9XjX',
            'name' => 'Josh',
            'description' => 'Male, resonant, thoughtful & storytelling',
            'tier' => 'Paid credits',
        ],
    ];

    public function mount()
    {
        $user = auth()->user();

        // Load Gemini Key state
        if ($user->api_key) {
            try {
                $this->apiKey = Crypt::decryptString($user->api_key);
                $this->originalSavedGeminiKey = $this->apiKey;
                $this->hasSavedGeminiKey = true;
                $this->isKeyVerified = !is_null($user->api_key_verified_at);
            } catch (\Throwable $e) {
                \Log::warning('Gemini API key decryption failed: ' . $e->getMessage());
                $this->status = 'error';
                $this->statusMessage = 'Saved Gemini key is corrupted. Please re-enter.';
                $this->hasSavedGeminiKey = false;
                $this->isKeyVerified = false;
                $this->apiKey = '';
                $this->originalSavedGeminiKey = '';
            }
        } else {
            $this->apiKey = '';
            $this->originalSavedGeminiKey = '';
        }

        // Load ElevenLabs Key state
        if ($user->elevenlabs_api_key) {
            try {
                $this->elevenLabsKey = Crypt::decryptString($user->elevenlabs_api_key);
                $this->originalSavedElevenLabsKey = $this->elevenLabsKey;
                $this->hasSavedElevenLabsKey = true;
                $this->isElevenLabsKeyVerified = !is_null($user->elevenlabs_api_key_verified_at);
            } catch (\Throwable $e) {
                \Log::warning('ElevenLabs API key decryption failed: ' . $e->getMessage());
                $this->elevenLabsStatus = 'error';
                $this->elevenLabsStatusMessage = 'Saved ElevenLabs key is corrupted. Please re-enter.';
                $this->hasSavedElevenLabsKey = false;
                $this->isElevenLabsKeyVerified = false;
                $this->elevenLabsKey = '';
                $this->originalSavedElevenLabsKey = '';
            }
        } else {
            $this->elevenLabsKey = '';
            $this->originalSavedElevenLabsKey = '';
        }

        // Load ElevenLabs Voice preference
        $userVoice = $user->getElevenLabsVoiceId();
        $presetIds = array_column(self::$presetVoices, 'id');
        if (in_array($userVoice, $presetIds, true)) {
            $this->selectedVoiceId = $userVoice;
            $this->customVoiceId = '';
        } else {
            $this->selectedVoiceId = 'custom';
            $this->customVoiceId = $userVoice;
        }
    }

    public function updatedApiKey($value)
    {
        $trimmed = trim($value ?? '');
        $saved = trim($this->originalSavedGeminiKey ?? '');

        if ($trimmed !== $saved) {
            $this->isKeyVerified = false;
            $this->hasTestedSuccessfully = false;
            $this->status = 'idle';
            $this->statusMessage = '';
        } else {
            $this->isKeyVerified = !is_null(auth()->user()->api_key_verified_at);
            $this->hasTestedSuccessfully = false;
            $this->status = 'idle';
            $this->statusMessage = '';
        }
    }

    public function updatedElevenLabsKey($value)
    {
        $trimmed = trim($value ?? '');
        $saved = trim($this->originalSavedElevenLabsKey ?? '');

        if ($trimmed !== $saved) {
            $this->isElevenLabsKeyVerified = false;
            $this->hasElevenLabsTestedSuccessfully = false;
            $this->elevenLabsStatus = 'idle';
            $this->elevenLabsStatusMessage = '';
        } else {
            $this->isElevenLabsKeyVerified = !is_null(auth()->user()->elevenlabs_api_key_verified_at);
            $this->hasElevenLabsTestedSuccessfully = false;
            $this->elevenLabsStatus = 'idle';
            $this->elevenLabsStatusMessage = '';
        }
    }

    public function canSaveGeminiKey(): bool
    {
        $currentKey = trim($this->apiKey ?? '');

        if (empty($currentKey)) {
            return false;
        }

        // If key is already saved and unchanged, Save button cannot be clicked
        if ($this->hasSavedGeminiKey && $currentKey === trim($this->originalSavedGeminiKey ?? '')) {
            return false;
        }

        return (bool) $this->isKeyVerified;
    }

    public function canSaveElevenLabsKey(): bool
    {
        $currentKey = trim($this->elevenLabsKey ?? '');

        if (empty($currentKey)) {
            return false;
        }

        // If key is already saved and unchanged, Save button cannot be clicked
        if ($this->hasSavedElevenLabsKey && $currentKey === trim($this->originalSavedElevenLabsKey ?? '')) {
            return false;
        }

        return (bool) $this->isElevenLabsKeyVerified;
    }

    // -------------------------------------------------------------
    // Gemini Actions
    // -------------------------------------------------------------

    public function testConnection()
    {
        $user = auth()->user();
        $keyToTest = trim($this->apiKey);

        if (empty($keyToTest) && $user->api_key) {
            try {
                $keyToTest = Crypt::decryptString($user->api_key);
            } catch (\Throwable $e) {
                $this->status = 'error';
                $this->statusMessage = 'Could not decrypt saved key. Please enter a new key.';
                return;
            }
        }

        if (empty($keyToTest)) {
            $this->status = 'error';
            $this->statusMessage = 'Please enter a Gemini API key first';
            return;
        }

        $this->status = 'testing';
        $this->statusMessage = 'Testing connection to Google Gemini...';

        try {
            $response = Http::get("https://generativelanguage.googleapis.com/v1/models?key={$keyToTest}");

            if ($response->successful() && !empty($response->json('models'))) {
                $this->hasTestedSuccessfully = true;
                $this->isKeyVerified = true;

                if ($this->hasSavedGeminiKey && $keyToTest === $this->originalSavedGeminiKey) {
                    $user->update([
                        'api_key_verified_at' => now(),
                        'api_key_tested_at' => now(),
                    ]);
                }

                $this->status = 'success';
                $this->statusMessage = 'Connection successful! You can now save.';
            } else {
                $this->hasTestedSuccessfully = false;
                $this->isKeyVerified = false;

                if ($this->hasSavedGeminiKey && $keyToTest === $this->originalSavedGeminiKey) {
                    $user->update([
                        'api_key_verified_at' => null,
                        'api_key_tested_at' => now(),
                    ]);
                }

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
        if (!$this->canSaveGeminiKey()) {
            return;
        }

        $newKey = trim($this->apiKey);
        $user = auth()->user();

        $encrypted = Crypt::encryptString($newKey);
        $user->api_key = $encrypted;
        $user->api_key_verified_at = now();
        $user->save();

        $this->hasSavedGeminiKey = true;
        $this->originalSavedGeminiKey = $newKey;
        $this->apiKey = $newKey;
        $this->isKeyVerified = true;

        $this->status = 'success';
        $this->statusMessage = 'Gemini API key saved and active!';

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
        $this->originalSavedGeminiKey = '';
        $this->hasSavedGeminiKey = false;
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
        $user = auth()->user();
        $keyToTest = trim($this->elevenLabsKey);

        if (empty($keyToTest) && $user->elevenlabs_api_key) {
            try {
                $keyToTest = Crypt::decryptString($user->elevenlabs_api_key);
            } catch (\Throwable $e) {
                $this->elevenLabsStatus = 'error';
                $this->elevenLabsStatusMessage = 'Could not decrypt saved key. Please enter a new key.';
                return;
            }
        }

        if (empty($keyToTest)) {
            $this->elevenLabsStatus = 'error';
            $this->elevenLabsStatusMessage = 'Please enter an ElevenLabs API key first';
            return;
        }

        $this->elevenLabsStatus = 'testing';
        $this->elevenLabsStatusMessage = 'Testing connection to ElevenLabs...';

        try {
            $response = Http::withHeaders([
                'xi-api-key' => $keyToTest,
            ])->get('https://api.elevenlabs.io/v1/user');

            if ($response->successful()) {
                $this->hasElevenLabsTestedSuccessfully = true;
                $this->isElevenLabsKeyVerified = true;

                if ($this->hasSavedElevenLabsKey && $keyToTest === $this->originalSavedElevenLabsKey) {
                    $user->update([
                        'elevenlabs_api_key_verified_at' => now(),
                        'elevenlabs_api_key_tested_at' => now(),
                    ]);
                }

                $this->elevenLabsStatus = 'success';
                $this->elevenLabsStatusMessage = 'Connection successful! Your ElevenLabs key is valid.';
            } else {
                $this->hasElevenLabsTestedSuccessfully = false;
                $this->isElevenLabsKeyVerified = false;

                if ($this->hasSavedElevenLabsKey && $keyToTest === $this->originalSavedElevenLabsKey) {
                    $user->update([
                        'elevenlabs_api_key_verified_at' => null,
                        'elevenlabs_api_key_tested_at' => now(),
                    ]);
                }

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
        if (!$this->canSaveElevenLabsKey()) {
            return;
        }

        $newKey = trim($this->elevenLabsKey);
        $user = auth()->user();

        $encrypted = Crypt::encryptString($newKey);
        $user->elevenlabs_api_key = $encrypted;
        $user->elevenlabs_api_key_verified_at = now();
        $user->save();

        $this->hasSavedElevenLabsKey = true;
        $this->originalSavedElevenLabsKey = $newKey;
        $this->elevenLabsKey = $newKey;
        $this->isElevenLabsKeyVerified = true;

        $this->elevenLabsStatus = 'success';
        $this->elevenLabsStatusMessage = 'ElevenLabs API key saved and active!';

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
        $this->originalSavedElevenLabsKey = '';
        $this->hasSavedElevenLabsKey = false;
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

    public function updatedSelectedVoiceId($value)
    {
        $this->voiceSavedMessage = '';
        if ($value !== 'custom') {
            $this->saveVoiceSetting($value);
        }
    }

    public function saveCustomVoice()
    {
        $customId = trim($this->customVoiceId);
        if (empty($customId)) {
            $this->selectedVoiceId = 'EXAVITQu4vr4xnSDxMaL';
            $this->saveVoiceSetting('EXAVITQu4vr4xnSDxMaL');
            return;
        }

        $this->saveVoiceSetting($customId);
    }

    protected function saveVoiceSetting(string $voiceId): void
    {
        $user = auth()->user();
        $settings = $user->settings ?? [];
        $settings['elevenlabs_voice_id'] = $voiceId;
        $user->settings = $settings;
        $user->save();

        $this->voiceSavedMessage = 'Voice preference saved!';
        $this->dispatch('voice-preference-saved');
    }

    public function getPresetVoicesProperty(): array
    {
        return self::$presetVoices;
    }

    public function getCurrentVoiceProperty(): ?array
    {
        foreach (self::$presetVoices as $voice) {
            if ($voice['id'] === $this->selectedVoiceId) {
                return $voice;
            }
        }

        return null;
    }

    public function render()
    {
        return view('livewire.settings.api-integration');
    }
}