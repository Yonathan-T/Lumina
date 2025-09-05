<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SocialLoginController;
use App\Livewire\SettingsPanel;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PasswordResetController;



// Protected Routes
Route::middleware('auth')->group(function () {

    // Dashboard
//    Route::get('/dashboard', [DashboardController::class, 'create'])->name('dashboard');
    Route::view('/dashboard', 'SecViews.dashboard')->name('dashboard');
    Route::view('/entries/create', 'SecViews.newentry')->name('entries.create'); // Show form
    // Entries
    Route::view('/entries', 'SecViews.history')->name('archive.entries');       // Show all entries (like history)
    // Route::get('/entries/create', [EntryController::class, 'create'])->name('entries.create'); // Show form
    Route::post('/entries', [EntryController::class, 'store'])->name('entries.store');        // Save entry
    Route::get('/entries/{entry}', [EntryController::class, 'show'])->name('entries.show');    // View a single entry
    Route::get('/entries/{entry}/edit', [EntryController::class, 'edit'])->name('entries.edit');    // Show edit form
    Route::put('/entries/{entry}', [EntryController::class, 'update'])->name('entries.update');    // Update entry
    Route::delete('/entries/{entry}', [EntryController::class, 'destroy'])->name('entries.destroy');    // Delete entry

    // Tags
    // Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::view('/tags', 'SecViews.taglist')->name('tags.index');

    // Insights
    Route::get('/insights', [InsightController::class, 'index'])->name('insights.index');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    //Route::get('/settings', SettingsPanel::class)->name('settings.index');

    // Blogs
});

Route::get('/blogs', function () {
    return view('blogs.index');
})->name('blogs.index');
Route::get('/', function () {
    return view('landing-page');
});
// Route::get('/dashboard', function () {
//     return view('entries.index');
// });
Route::get('/auth/register', [RegisteredUserController::class, 'create']);
Route::post('/auth/register', [RegisteredUserController::class, 'store']);

Route::get('/auth/login', [SessionController::class, 'create'])->name('login');
Route::post('/auth/login', [SessionController::class, 'store']);
Route::post('/auth/logout', [SessionController::class, 'destroy'])->name('logout');


Route::get('/auth/{provider}/redirect', [SocialLoginController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback']);

Route::get('/search', [App\Http\Controllers\SearchController::class, 'search'])->name('search');

Route::get('password/reset', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');

Route::post('password/email', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');

Route::get('password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');

Route::post('password/reset', [PasswordResetController::class, 'reset'])->name('password.update');

Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');

// //entries need middleware ofc.
// Route::get('/entries',[EntryController::class ,'create']);
// Route::post('/entries',[EntryController::class ,'store'])->middleware('auth');


