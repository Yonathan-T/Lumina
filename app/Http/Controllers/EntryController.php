<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Tag;
use App\Http\Requests\StoreEntryRequest;
use App\Http\Requests\UpdateEntryRequest;
use Illuminate\Validation\Rules\Can;

class EntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('SecViews.newentry');
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('entries.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        request()->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $entry = Entry::create([
            'title' => request('title'),
            'content' => trim(request('content')),
            'user_id' => auth()->id(),
        ]);

        preg_match_all('/(?<=\s|^)#([A-Za-z][\w]{1,30})/', request('content'), $matches);
        $tags = array_unique(array_map('strtolower', $matches[1] ?? []));

        foreach ($tags as $tagName) {
            if (empty($tagName)) {
                continue;
            }
            $tag = Tag::firstOrCreate([
                'slug' => \Illuminate\Support\Str::slug($tagName),
            ], [
                'name' => $tagName,
            ]);
            $entry->tags()->attach($tag->id);
        }

        dispatch(fn () => app(\App\Services\EntrySearchService::class)->updateEntryIndex($entry))->afterResponse();

        return redirect()->route('dashboard')->with('message', 'Entry created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Entry $entry)
    {
        $this->authorize('view', $entry);
        $entry->loadMissing('tags:id,name');
        return view('entries.showEntry', compact('entry'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Entry $entry)
    {
        $this->authorize('update', $entry);
        return view('entries.edit', compact('entry'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEntryRequest $request, Entry $entry)
    {
        $this->authorize('update', $entry);

        $entry->update([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
        ]);

        return redirect()->route('entries.show', $entry)->with('success', 'Entry updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Entry $entry)
    {
        $this->authorize('delete', $entry);

        $entry->delete();

        return redirect()->route('archive.entries')->with('success', 'Entry deleted successfully!');
    }
}
