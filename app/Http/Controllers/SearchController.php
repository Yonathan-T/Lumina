<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Tag;
use App\Services\EntrySearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function search(Request $request, EntrySearchService $searchService)
    {
        $query = trim((string) $request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];
        $entryIds = $searchService->findMatchingEntryIds(auth()->id(), $query, 10);

        $entries = Entry::where('user_id', auth()->id())
            ->whereIn('id', $entryIds)
            ->with('tags')
            ->get();

        $entries = $entryIds
            ->map(fn ($id) => $entries->firstWhere('id', $id))
            ->filter();

        foreach ($entries as $entry) {
            $results[] = [
                'id' => $entry->id,
                'title' => $entry->title,
                'preview' => Str::limit($entry->content, 150),
                'date' => $entry->created_at->format('M j, Y'),
                'tags' => $entry->tags->pluck('name')->toArray(),
                'type' => 'entry',
                'url' => route('entries.show', $entry->id),
            ];
        }

        // Also search matching tags
        $isPg = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql';
        $operator = $isPg ? 'ILIKE' : 'LIKE';
        $cleanTagQuery = ltrim($query, '#');

        $tags = Tag::where('name', $operator, "%{$cleanTagQuery}%")
            ->whereHas('entries', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->withCount(['entries' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->limit(3)
            ->get();

        foreach ($tags as $tag) {
            $results[] = [
                'id' => "tag-{$tag->id}",
                'title' => "#{$tag->name}",
                'preview' => "{$tag->entries_count} entries tagged",
                'date' => "Tag",
                'tags' => [$tag->name],
                'type' => 'tag',
                'url' => route('tags.index').'?tag='.urlencode($tag->name),
            ];
        }

        return response()->json(['results' => $results]);
    }
}
