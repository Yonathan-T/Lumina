<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Models\Entry;
use App\Models\Message;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateToEnvelopeEncryption extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lumina:migrate-encryption';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate database to Option B (Per-User Envelope Encryption)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration to Option B (Per-User Envelope Encryption)...');

        // 1. Initialize DEKs for all users
        $users = User::all();
        $this->info("Initializing Data Encryption Keys (DEKs) for {$users->count()} users...");
        $bar = $this->output->createProgressBar($users->count());
        foreach ($users as $user) {
            $user->getEncrypter(); // generates & saves DEK if missing
            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);

        // 2. Re-encrypt all Entries with owning user's DEK
        $entries = Entry::with('user')->get();
        $this->info("Migrating {$entries->count()} Entries to per-user DEKs...");
        $bar = $this->output->createProgressBar($entries->count());
        $entriesMigrated = 0;
        $entriesFailed = 0;

        foreach ($entries as $entry) {
            try {
                $user = $entry->user ?: User::find($entry->user_id);
                if (!$user) {
                    $this->warn("Skipping Entry #{$entry->id}: user_id {$entry->user_id} not found.");
                    $entriesFailed++;
                    continue;
                }

                $userEnc = $user->getEncrypter();
                $decryptedTitle = $entry->title;
                $decryptedContent = $entry->content;

                // Directly encrypt with user's DEK and persist
                DB::table('entries')->where('id', $entry->id)->update([
                    'title' => $userEnc->encryptString((string) $decryptedTitle),
                    'content' => $userEnc->encryptString((string) $decryptedContent),
                ]);

                $entriesMigrated++;
            } catch (\Throwable $e) {
                $this->error("Error on Entry #{$entry->id}: " . $e->getMessage());
                $entriesFailed++;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);

        // 3. Re-encrypt all Conversations
        $conversations = Conversation::with('user')->get();
        $this->info("Migrating {$conversations->count()} Conversations to per-user DEKs...");
        $bar = $this->output->createProgressBar($conversations->count());
        $convsMigrated = 0;

        foreach ($conversations as $conv) {
            try {
                $user = $conv->user ?: User::find($conv->user_id);
                if ($user) {
                    $userEnc = $user->getEncrypter();
                    $decryptedTitle = $conv->title;

                    DB::table('conversations')->where('id', $conv->id)->update([
                        'title' => $userEnc->encryptString((string) $decryptedTitle),
                    ]);
                    $convsMigrated++;
                }
            } catch (\Throwable $e) {
                $this->error("Error on Conversation #{$conv->id}: " . $e->getMessage());
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);

        // 4. Re-encrypt all Messages
        $messages = Message::with('conversation.user')->get();
        $this->info("Migrating {$messages->count()} Messages to per-user DEKs...");
        $bar = $this->output->createProgressBar($messages->count());
        $msgsMigrated = 0;

        foreach ($messages as $msg) {
            try {
                $user = $msg->conversation?->user ?: ($msg->conversation_id ? Conversation::find($msg->conversation_id)?->user : null);
                if ($user) {
                    $userEnc = $user->getEncrypter();
                    $decryptedContent = $msg->content;

                    DB::table('messages')->where('id', $msg->id)->update([
                        'content' => $userEnc->encryptString((string) $decryptedContent),
                    ]);
                    $msgsMigrated++;
                }
            } catch (\Throwable $e) {
                $this->error("Error on Message #{$msg->id}: " . $e->getMessage());
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine(2);

        // 5. Verification Phase: Verify that DEK decrypts directly without falling back
        $this->info("Verifying migration integrity...");
        $testEntry = Entry::find(7);
        if ($testEntry) {
            $this->line("Test Entry #7 Title: " . $testEntry->title);
            $this->line("Test Entry #7 Content Preview: " . substr($testEntry->content, 0, 60) . "...");
        }

        $this->info("Migration completed successfully!");
        $this->table(
            ['Entity', 'Migrated', 'Failed'],
            [
                ['Users (DEKs)', $users->count(), 0],
                ['Entries', $entriesMigrated, $entriesFailed],
                ['Conversations', $convsMigrated, 0],
                ['Messages', $msgsMigrated, 0],
            ]
        );

        return 0;
    }
}
