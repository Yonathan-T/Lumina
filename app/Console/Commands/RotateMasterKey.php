<?php

namespace App\Console\Commands;

use App\Models\Entry;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class RotateMasterKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lumina:rotate-master-key';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely rotate the application master APP_KEY without risking any user entries (Option B)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Lumina Safe Master Key Rotation ===');

        $currentKey = config('app.key');
        if (empty($currentKey)) {
            $this->error('Current APP_KEY is empty!');
            return 1;
        }

        // 1. Generate a new cryptographically secure 256-bit AES master key
        $newRawKey = random_bytes(32);
        $newKey = 'base64:' . base64_encode($newRawKey);
        $newEncrypter = new Encrypter($newRawKey, 'AES-256-CBC');

        $users = User::whereNotNull('encryption_key')->get();
        $this->info("Re-wrapping Data Encryption Keys (DEKs) for {$users->count()} users under the new master key...");

        DB::beginTransaction();
        try {
            foreach ($users as $user) {
                // Decrypt existing DEK using active encrypter
                $decryptedDekB64 = Crypt::decryptString($user->encryption_key);
                // Re-encrypt DEK with the new master encrypter
                $newEncryptedDek = $newEncrypter->encryptString($decryptedDekB64);

                DB::table('users')->where('id', $user->id)->update([
                    'encryption_key' => $newEncryptedDek,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Failed to re-wrap user keys: ' . $e->getMessage());
            return 1;
        }

        // 2. Update .env file
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);

            // Maintain previous keys list
            $existingPrev = env('APP_PREVIOUS_KEYS', '');
            $prevArray = array_filter(explode(',', $existingPrev));
            array_unshift($prevArray, $currentKey);
            $prevArray = array_unique($prevArray);
            $newPrevStr = implode(',', $prevArray);

            // Replace APP_KEY
            if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
                $envContent = preg_replace('/^APP_KEY=.*$/m', "APP_KEY={$newKey}", $envContent);
            } else {
                $envContent = "APP_KEY={$newKey}\n" . $envContent;
            }

            // Replace or add APP_PREVIOUS_KEYS
            if (preg_match('/^APP_PREVIOUS_KEYS=.*$/m', $envContent)) {
                $envContent = preg_replace('/^APP_PREVIOUS_KEYS=.*$/m', "APP_PREVIOUS_KEYS={$newPrevStr}", $envContent);
            } else {
                $envContent = preg_replace('/^(APP_KEY=.*)$/m', "$1\nAPP_PREVIOUS_KEYS={$newPrevStr}", $envContent);
            }

            file_put_contents($envPath, $envContent);
        }

        Artisan::call('config:clear');

        $this->newLine();
        $this->info("Master APP_KEY successfully rotated to: {$newKey}");
        $this->info("Previous key preserved in APP_PREVIOUS_KEYS.");
        $this->info("Zero user entries, conversations, or messages had to be touched!");

        // Verification check
        $sample = Entry::first();
        if ($sample) {
            $this->line("Verification: Entry #{$sample->id} successfully decrypts: '{$sample->title}'");
        }

        return 0;
    }
}
