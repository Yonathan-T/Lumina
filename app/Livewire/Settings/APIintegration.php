<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class ApiIntegration extends Component
{
    public $apiKey = '';
    public $showKey = false;
    public $status = 'idle';
    public $statusMessage = '';

    public $isConfirmingRemoval = false;
    public $isKeyVerified = false;
    public $hasTestedSuccessfully = false;

    public function mount()
    {
        $user = auth()->user();

        if ($user->api_key) {
            try {
                $this->apiKey = Crypt::decryptString($user->api_key);
                $this->isKeyVerified = !is_null($user->api_key_verified_at);
            } catch (DecryptException $e) {
                \Log::error('API key decryption failed: ' . $e->getMessage());
                $this->status = 'error';
                $this->statusMessage = 'Saved key is corrupted. Please re-enter.';
                $this->apiKey = '';
                $this->isKeyVerified = false;
            }
        }
    }

    public function testConnection()
    {
        if (empty(trim($this->apiKey))) {
            $this->status = 'error';
            $this->statusMessage = 'Please enter an API key first';
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
                $this->statusMessage = 'Invalid or restricted API key.';
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
            $this->statusMessage = 'API key cannot be empty';
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
            ? 'API key saved and active!'
            : 'API key saved. Test connection to activate.';

        $this->dispatch('key-saved');
    }

    public function openConfirmationModal()
    {
        $this->isConfirmingRemoval = true;
    }

    public function closeConfirmationModal()
    {
        $this->isConfirmingRemoval = false;
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
        $this->isConfirmingRemoval = false;

        $this->status = 'success';
        $this->statusMessage = 'API key removed successfully';
        $this->dispatch('key-removed');
    }

    public function toggleShowKey()
    {
        $this->showKey = !$this->showKey;
    }

    public function hasApiKey(): bool
    {
        return !empty(auth()->user()->api_key);
    }


    public function render()
    {
        return view('livewire.settings.api-integration');
    }
}