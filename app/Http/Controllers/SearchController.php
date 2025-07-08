<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entry;
use App\Models\Tag;
use Str;
class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 1) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Search entries big brrr
        $entries = Entry::where('title', 'LIKE', "%{$query}%")
            ->orWhere('content', 'LIKE', "%{$query}%")
            ->orWhereHas('tags', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->with('tags')
            ->limit(10)
            ->get();

        foreach ($entries as $entry) {
            $results[] = [
                'id' => $entry->id,
                'title' => $entry->title,
                'preview' => Str::limit($entry->content, 150),
                'date' => $entry->created_at->format('M j, Y'),
                'tags' => $entry->tags->pluck('name')->toArray(),
                'type' => 'entry',
                'url' => route('entries.show', $entry->id)
            ];
        }

        // and Search tags
        if ($request->get('include_tags')) {
            $tags = Tag::where('name', 'LIKE', "%{$query}%")
                ->withCount('entries')
                ->limit(5)
                ->get();

            foreach ($tags as $tag) {
                $results[] = [
                    'id' => "tag-{$tag->id}",
                    'title' => "#{$tag->name}",
                    'preview' => "{$tag->entries_count} entries",
                    'date' => "{$tag->entries_count} entries",
                    'tags' => [$tag->name],
                    'type' => 'tag',
                    'url' => route('tags.show', $tag->name)
                ];
            }
        }

        return response()->json(['results' => $results]);
    }
}