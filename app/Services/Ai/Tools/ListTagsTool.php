<?php

namespace App\Services\Ai\Tools;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ListTagsTool implements ToolInterface
{
    public function name(): string
    {
        return 'list_tags';
    }

    public function definition(): array
    {
        return [
            'name' => 'list_tags',
            'description' => 'Retrieve all unique tags the user has used in their journal along with the entry counts for each tag. Use when the user asks about their tags, how many tags they have, or what categories/topics they tag frequently.',
            'parameters' => [
                'type' => 'OBJECT',
                'properties' => [
                    'limit' => [
                        'type' => 'INTEGER',
                        'description' => 'Optional maximum number of tags to return (default is 30).',
                    ],
                ],
            ],
        ];
    }

    public function execute(array $args, User $user): array
    {
        try {
            $limit = isset($args['limit']) ? max(1, min(100, (int) $args['limit'])) : 30;

            $tags = Tag::whereHas('entries', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
                ->withCount(['entries' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                }])
                ->orderByDesc('entries_count')
                ->get();

            $totalCount = $tags->count();

            $formatted = $tags->take($limit)->map(function ($tag) {
                return [
                    'name' => $tag->name,
                    'entries_count' => $tag->entries_count,
                ];
            })->all();

            return [
                'status' => 'success',
                'total_unique_tags' => $totalCount,
                'tags' => $formatted,
            ];
        } catch (\Throwable $e) {
            Log::error('ListTagsTool execution failed: ' . $e->getMessage());

            return [
                'status' => 'error',
                'message' => 'Unable to list user tags at this time.',
            ];
        }
    }
}
