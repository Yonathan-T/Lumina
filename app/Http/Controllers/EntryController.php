<?php

namespace App\Http\Controllers;

use App\Models\Entry;
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
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("entries.index");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
    request()->validate([  
            "title"=> ["required","string"],
            "content"=> ["required"],
            ]);
        Entry::create([
            "title"=> request()->title,
            "content"=> request()->content,
            "user_id" => auth()->id(), 
            ]);
        return redirect('/entries');
    }

    /**
     * Display the specified resource.
     */
    public function show(Entry $entry)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Entry $entry)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEntryRequest $request, Entry $entry)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Entry $entry)
    {
        //
    }
}
