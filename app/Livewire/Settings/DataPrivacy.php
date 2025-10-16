<?php

namespace App\Livewire\Settings;

use App\Models\Conversation;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\Models\Entry;
use App\Mail\DataExportReady;

class DataPrivacy extends Component
{
    public $isExporting = false;
    public $showDeleteConfirm = false;

    public function exportData()
    {
        $this->isExporting = true;

        try {
            $user = Auth::user();
            
           
            $entries = Entry::with('tags')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($entry) {
                    return [
                        'id' => $entry->id,
                        'title' => $entry->title,
                        'content' => $entry->content,
                        'tags' => $entry->tags->pluck('name')->toArray(),
                        'created_at' => $entry->created_at->toIso8601String(),
                        'updated_at' => $entry->updated_at->toIso8601String(),
                    ];
                });

            
            $exportData = [
                'export_metadata' => [
                    'version' => '1.0',
                    'exported_at' => now()->toIso8601String(),
                    'user_email' => $user->email,
                    'user_name' => $user->name,
                    'total_entries' => $entries->count(),
                    'app_name' => '{Lumina}',
                    'app_version' => '1.0.0',
                    'format' => 'json',
                    'encoding' => 'UTF-8',
                ],
                'entries' => $entries->toArray(),
            ];

            $filename = 'lumina-entry-export-' . $user->id . '-' . now()->format('Y-m-d-His') . '.json';
            
            $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            Storage::disk('local')->put('exports/' . $filename, $json);

            $expiresAt = now()->addHours(24)->timestamp;
            $token = Crypt::encrypt([
                'user_id' => $user->id,
                'filename' => $filename,
                'expires_at' => $expiresAt,
            ]);

            $downloadUrl = route('export.download', ['token' => $token]);

            Mail::to($user->email)->send(new DataExportReady(
                $user->name,
                $entries->count(),
                $downloadUrl,
                now()->addHours(24)->format('F j, Y \a\t g:i A'),
                'entries'
            ));

            $this->isExporting = false;

            session()->flash('success', '🎉 Export ready! Check your email for the download link.');

        } catch (\Exception $e) {
            $this->isExporting = false;
            \Log::error('Export failed: ' . $e->getMessage());
            
            session()->flash('error', 'Failed to export data. Please try again or contact support.');
        }
    }
    public function exportConversation()
{
    $this->isExporting = true;

    try {
        $user = Auth::user();

        $conversations = Conversation::with('messages')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($conversations->isEmpty()) {
            throw new \Exception('No chat conversations available for export');
        }

        $conversations = $conversations->map(function ($conversation) {

            try {
                $messages = $conversation->messages;
                if (!$messages || !$messages->count()) {
                    return [
                        'id' => $conversation->id,
                        'title' => $conversation->title,
                        'type' => $conversation->type,
                        'messages_count' => 0,
                        'created_at' => $conversation->created_at->toIso8601String(),
                        'updated_at' => $conversation->updated_at->toIso8601String(),
                        'last_activity' => $conversation->last_activity ? $conversation->last_activity->toIso8601String() : null,
                        'messages' => [],
                    ];
                }

                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'type' => $conversation->type,
                    'messages_count' => $messages->count(),
                    'created_at' => $conversation->created_at->toIso8601String(),
                    'updated_at' => $conversation->updated_at->toIso8601String(),
                    'last_activity' => $conversation->last_activity ? $conversation->last_activity->toIso8601String() : null,
                    'messages' => $messages->map(function ($message) {
                        return [
                            'id' => $message->id,
                            'role' => $message->is_ai_response ? 'assistant' : 'user',
                            'content' => $message->content,
                            'created_at' => $message->created_at->toIso8601String(),
                        ];
                    })->all(), 
                ];
            } catch (\Exception $e) {
                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'type' => $conversation->type,
                    'messages_count' => 0,
                    'created_at' => $conversation->created_at->toIso8601String(),
                    'updated_at' => $conversation->updated_at->toIso8601String(),
                    'last_activity' => $conversation->last_activity ? $conversation->last_activity->toIso8601String() : null,
                    'messages' => [],
                ];
            }
        });


        $exportData = [
            'export_metadata' => [
                'version' => '1.0',
                'exported_at' => now()->toIso8601String(),
                'user_email' => $user->email,
                'user_name' => $user->name,
                'total_conversations' => $conversations->count(),
                'app_name' => '{Lumina}',
                'app_version' => '1.0.0',
                'format' => 'json',
                'encoding' => 'UTF-8',
            ],
            'conversations' => $conversations->toArray(),
        ];

        $filename = 'lumina_chat_export_' . $user->id . '-' . now()->format('Y-m-d-His') . '.json';

        $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new \Exception('Failed to encode export data to JSON: ' . json_last_error_msg());
        }

        $result = Storage::disk('local')->put('exports/' . $filename, $json);
        if (!$result) {
            throw new \Exception('Failed to save export file to storage');
        }

        $expiresAt = now()->addHours(24)->timestamp;
        $token = Crypt::encrypt([
            'user_id' => $user->id,
            'filename' => $filename,
            'expires_at' => $expiresAt,
        ]);

        $downloadUrl = route('export.download', ['token' => $token]);

        Mail::to($user->email)->send(new DataExportReady(
            $user->name,
            $conversations->count(),
            $downloadUrl,
            now()->addHours(24)->format('F j, Y \a\t g:i A'),
            'conversations'
        ));

        $this->isExporting = false;
        session()->flash('success', '🎉 Chat export ready! Check your email for the download link.');

    } catch (\Exception $e) {
        $this->isExporting = false;
        session()->flash('error', 'Failed to export chat data. Please try again or contact support.');
    }
}

    public function confirmDelete()
    {
        $this->showDeleteConfirm = true;
    }

    public function deleteAccount()
    {
        $this->showDeleteConfirm = false;
        session()->flash('message', 'Account deletion is not yet implemented.');
    }

    public function render()
    {
        return view('livewire.settings.data-privacy');
    }
}