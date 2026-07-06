<?php

// app/Http/Controllers/InsightsController.php

namespace App\Http\Controllers;

use App\Models\Entry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InsightController extends Controller
{
    public function index()
    {
        return view('SecViews.insights');
    }

    /**
     * Lightweight JSON feed of a single day's entries, powering the Insights
     * heatmap drill-down modal. Kept separate from the Livewire component so
     * opening the modal is a tiny fetch instead of a full page re-render.
     */
    public function day(Request $request, string $date)
    {
        try {
            $day = Carbon::parse($date);
        } catch (\Exception $e) {
            abort(404);
        }

        $entries = Entry::where('user_id', $request->user()->id)
            ->whereBetween('created_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
            ->orderBy('created_at')
            ->get(['id', 'title', 'content', 'created_at'])
            ->map(fn ($e) => [
                'title' => trim($e->title) !== '' ? $e->title : 'Untitled entry',
                'time' => $e->created_at->format('g:i A'),
                'snippet' => Str::limit(trim(strip_tags($e->content ?? '')), 180),
                'url' => route('entries.show', $e->id),
            ]);

        return response()->json([
            'label' => $day->format('l, M j, Y'),
            'count' => $entries->count(),
            'entries' => $entries,
        ]);
    }
}
