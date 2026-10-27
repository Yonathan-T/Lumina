<?php

namespace App\Services\Ai\Tools;

use App\Models\Entry;
use App\Models\Tag;
use App\Models\User;
use App\Services\EntrySearchService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DraftEntryTool implements ToolInterface
{
    protected EntrySearchService $searchService;

    public function __construct(?EntrySearchService $searchService = null)
    {
        $this->searchService = $searchService ?? app(EntrySearchService::class);
    }

    public function name(): string
    {
        return 'draft_entry';
    }

    public function definition(): array
    {
        return [
            'name' => 'draft_entry',
            'description' => 'Persist and save a finalized journal entry into the user\'s sanctuary database. ONLY call this tool after the user has approved the draft or provided the full content and title. Do not call this when brainstorming or proposing drafts.',
            'parameters' => [
                'type' => 'OBJECT',
                'properties' => [
                    'title' => [
                        'type' => 'STRING',
                        'description' => 'A clear, thoughtful title summarizing the memo or reflection.',
                    ],
                    'content' => [
                        'type' => 'STRING',
                        'description' => 'The complete, meaningful body text of the journal entry capturing the user\'s thoughts and insights.',
                    ],
                    'tags' => [
                        'type' => 'ARRAY',
                        'items' => [
                            'type' => 'STRING',
                        ],
                        'description' => 'Optional tags to categorize the entry (e.g. ["reflection", "growth", "evening"]).',
                    ],
                ],
                'required' => ['title', 'content'],
            ],
        ];
    }

    public function execute(array $args, User $user): array
    {
        $title = trim((string) ($args['title'] ?? ''));
        $content = trim((string) ($args['content'] ?? ''));
        $tags = (array) ($args['tags'] ?? []);

        if ($title === '' || $content === '') {
            return [
                'status' => 'error',
                'message' => 'Both title and content are required to save an entry.',
            ];
        }

        try {
            $entry = Entry::create([
                'title' => $title,
                'content' => $content,
                'user_id' => $user->id,
            ]);

            // Attach user tags
            foreach ($tags as $tagName) {
                $name = strtolower(trim((string) $tagName, "# \t\n\r\0\x0B"));
                if ($name === '') {
                    continue;
                }

                $tag = Tag::firstOrCreate([
                    'slug' => Str::slug($name),
                ], [
                    'name' => $name,
                ]);

                $entry->tags()->syncWithoutDetaching([$tag->id]);
            }

            // Update search index
            try {
                $this->searchService->updateEntryIndex($entry);
            } catch (\Throwable $e) {
                Log::warning('Search index update failed in DraftEntryTool: ' . $e->getMessage());
            }

            return [
                'status' => 'success',
                'message' => "Journal entry '{$entry->title}' was successfully saved into your sanctuary.",
                'entry_id' => $entry->id,
                'title' => $entry->title,
                'created_at' => $entry->created_at->format('M j, Y'),
            ];
        } catch (\Throwable $e) {
            Log::error('DraftEntryTool execution failed: ' . $e->getMessage());

            return [
                'status' => 'error',
                'message' => 'Failed to save entry into the database.',
            ];
        }
    }
}
