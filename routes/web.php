<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// Protected Routes
Route::middleware('auth')->group(function () {

    // Dashboard
//    Route::get('/dashboard', [DashboardController::class, 'create'])->name('dashboard');
    Route::view('/dashboard', 'SecViews.dashboard')->name('dashboard');
    // Entries
    Route::view('/entries/create', 'SecViews.newentry')->name('entries.create'); // Show form
    Route::view('/entries', 'SecViews.history')->name('archive.entries');       // Show all entries (like history)
    Route::get('/entries/{entry}', [EntryController::class, 'show'])->name('entries.show');    // Route::get('/entries/create', [EntryController::class, 'create'])->name('entries.create'); // Show form
    Route::post('/entries', [EntryController::class, 'store'])->name('entries.store');        // Save entry
    Route::get('/entry/edit', [EntryController::class, 'edit']);
    Route::get('/entries/{entry}/edit', [EntryController::class, 'edit'])->name('entries.edit');    // Show edit form
    Route::put('/entries/{entry}', [EntryController::class, 'update'])->name('entries.update');    // Update entry
    Route::delete('/entries/{entry}', [EntryController::class, 'destroy'])->name('entries.destroy');    // Delete entry

    // routes/web.php
    Route::get('/search', [SearchController::class, 'search'])->name('search');
    // Tags
    // Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::view('/tags', 'SecViews.taglist')->name('tags.index');

    // Insights
    Route::get('/insights', [InsightController::class, 'index'])->name('insights.index');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
});

Route::get('/', function () {
    return view('landing-page');
});
// Route::get('/dashboard', function () {
//     return view('entries.index');
// });
Route::get('/register', [RegisteredUserController::class, 'create']);
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::get('/login', [SessionController::class, 'create'])->name('login');
Route::post('/login', [SessionController::class, 'store']);

// //entries need middleware ofc.
// Route::get('/entries',[EntryController::class ,'create']);
// Route::post('/entries',[EntryController::class ,'store'])->middleware('auth');


