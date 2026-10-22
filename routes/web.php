<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ConfirmationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataExportController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SocialLoginController;
use App\Http\Controllers\TagController;
use App\Livewire\Pricing;
use App\Livewire\SettingsPanel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// Protected Routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    //    Route::get('/dashboard', [DashboardController::class, 'create'])->name('dashboard');
    Route::view('/dashboard', 'SecViews.dashboard')->name('dashboard');

    // Route::view('/entries/create', 'SecViews.newentry')->name('entries.create'); // Show form
    Route::view('/entries/create', 'SecViews.newentry')->name('entries.create'); // Show form
    // Entries
    // Route::view('/entries', 'SecViews.history')->name('archive.entries');       // Show all entries (like history)
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
    Route::get('/insights/day/{date}', [InsightController::class, 'day'])->name('insights.day');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    // Route::get('/settings', SettingsPanel::class)->name('settings.index');
    Route::post('/settings/music-preference', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        if ($user) {
            $settings = $user->settings ?? [];
            if ($request->has('url')) {
                $settings['sanctuary_youtube_url'] = $request->input('url');
            }
            if ($request->has('title')) {
                $settings['sanctuary_youtube_title'] = $request->input('title');
            }
            if ($request->has('mode')) {
                $settings['sanctuary_audio_mode'] = $request->input('mode');
            }
            $user->update(['settings' => $settings]);
        }
        return response()->json(['success' => true]);
    })->name('settings.music-preference');

    // Data Export Download

    // Blogs
});

// Data export download (outside auth for email links)
Route::get('/export/download/{token}', [DataExportController::class, 'download'])->name('export.download');
Route::get('/blogs', function () {
    return view('blogs.index');
})->name('blogs.index');
Route::get('/', function () {
    // Use 1 hour cache with live GitHub star check (fallback to live count of 3)
    $stars = Cache::remember('github_stars_live', 3600, function () {
        if (app()->environment('testing')) {
            return 3;
        }

        try {
            $response = Http::timeout(2.5)
                ->connectTimeout(1.5)
                ->withHeaders([
                    'User-Agent' => 'Lumina-App',
                    'Accept' => 'application/vnd.github.v3+json',
                ])->get('https://api.github.com/repos/Yonathan-T/Lumina');

            if ($response->successful()) {
                $count = $response->json()['stargazers_count'] ?? null;
                if ($count !== null) {
                    if ($count >= 1000) {
                        return number_format($count / 1000, 1).'K+';
                    }

                    return $count;
                }
            }
        } catch (\Throwable $e) {
            // Silent fail
        }

        return 3; // Live fallback
    });

    return view('landing-page', ['stars' => $stars]);
})->name('landingPage');

// Legal & Trust Pages
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/terms', 'pages.terms')->name('terms');
Route::view('/security', 'pages.security')->name('security');
// Route::get('/dashboard', function () {
//     return view('entries.index');
// });
// Guest-only Routes (Redirect already logged-in users directly to /dashboard)
Route::middleware('guest')->group(function () {
    Route::get('/auth/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/auth/register', [RegisteredUserController::class, 'store']);

    Route::get('/auth/login', [SessionController::class, 'create'])->name('login');
    Route::post('/auth/login', [SessionController::class, 'store']);

    Route::get('/auth/{provider}/redirect', [SocialLoginController::class, 'redirectToProvider']);
    Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback']);

    Route::get('password/reset', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Email Verification Routes
Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');
Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
    ->middleware(['throttle:6,1'])
    ->name('verification.send');

Route::post('/auth/logout', [SessionController::class, 'destroy'])->name('logout');

Route::get('/search', [SearchController::class, 'search'])->name('search');

Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::middleware('auth')->post('/chat/stream', [ChatController::class, 'stream'])->name('chat.stream');

// //entries need middleware ofc.
// Route::get('/entries',[EntryController::class ,'create']);
// Route::post('/entries',[EntryController::class ,'store'])->middleware('auth');

// Subscription management routes
Route::middleware('auth')->group(function () {
    Route::post('/subscription/upgrade', [ConfirmationController::class, 'upgradeSubscription'])->name('subscription.upgrade');
});

// Pricing and checkout routes
Route::middleware('auth')->group(function () {
    Route::get('/pricing', [ProductsController::class, 'handle'])->name('pricing');
    Route::get('/checkout', [CheckoutController::class, 'handle']);
    Route::get('/confirmation', [ConfirmationController::class, 'handle']);
});

// One-Time Envelope Encryption Migration Route (Option B)
Route::get('/system/migrate-option-b', function (\Illuminate\Http\Request $request) {
    $token = $request->query('token') ?? $request->query('secret');
    $validPassphrase = 'lumina-option-b-migrate-2026';

    $isAuthorized = false;
    if (!empty($token)) {
        if (hash_equals($validPassphrase, $token)) {
            $isAuthorized = true;
        } elseif (!empty(config('app.key')) && hash_equals(config('app.key'), $token)) {
            $isAuthorized = true;
        }
    }

    if (!$isAuthorized) {
        abort(403, 'Unauthorized migration request. Please use: ?token=lumina-option-b-migrate-2026');
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $migrateOut = \Illuminate\Support\Facades\Artisan::output();
    } catch (\Throwable $e) {
        $migrateOut = 'Migration error: ' . $e->getMessage();
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('lumina:migrate-encryption');
        $encryptOut = \Illuminate\Support\Facades\Artisan::output();
    } catch (\Throwable $e) {
        $encryptOut = 'Encryption migration error: ' . $e->getMessage();
    }

    return response(
        "<div style='background:#060b16;color:#e2e8f0;padding:32px;font-family:monospace;min-height:100vh;'>" .
        "<h2 style='color:#60a5fa;'>Lumina Option B — Production Migration Status</h2>" .
        "<h3 style='color:#94a3b8;'>Database Schema (migrate --force):</h3>" .
        "<pre style='background:#0f172a;padding:16px;border-radius:8px;border:1px solid #1e293b;overflow:auto;'>" . e($migrateOut) . "</pre>" .
        "<h3 style='color:#94a3b8;'>Envelope Encryption (lumina:migrate-encryption):</h3>" .
        "<pre style='background:#0f172a;padding:16px;border-radius:8px;border:1px solid #1e293b;overflow:auto;'>" . e($encryptOut) . "</pre>" .
        "</div>"
    );
});

// One-Time Route to restore clean reflections for Yonathan
Route::get('/system/restore-clean-entries', function (\Illuminate\Http\Request $request) {
    $token = $request->query('token') ?? $request->query('secret');
    if ($token !== 'lumina-option-b-migrate-2026') {
        abort(403, 'Unauthorized');
    }

    $jsonPath = database_path('data/yonathan_clean_entries.json');
    if (!file_exists($jsonPath)) {
        return response("Data file not found", 404);
    }

    $entries = json_decode(file_get_contents($jsonPath), true);
    $user = \App\Models\User::where('email', 'yonathantaweke@gmail.com')->first();
    if (!$user) {
        return response("User yonathantaweke@gmail.com not found", 404);
    }

    $userEnc = $user->getEncrypter();
    $restored = 0;

    foreach ($entries as $item) {
        $entry = \App\Models\Entry::where('user_id', $user->id)
            ->where(function ($q) use ($item) {
                $q->where('id', $item['id'])
                  ->orWhere('created_at', $item['created_at']);
            })->first();

        if ($entry) {
            \Illuminate\Support\Facades\DB::table('entries')->where('id', $entry->id)->update([
                'title' => $userEnc->encryptString((string) $item['title']),
                'content' => $userEnc->encryptString((string) $item['content']),
            ]);
            $restored++;
        }
    }

    return response(
        "<div style='background:#060b16;color:#e2e8f0;padding:32px;font-family:monospace;min-height:100vh;'>" .
        "<h2 style='color:#34d399;'>🎉 Successfully Restored {$restored} Reflections!</h2>" .
        "<p style='color:#94a3b8;'>All reflections have been cleanly re-encrypted under Yonathan's Option B personal key.</p>" .
        "<p style='margin-top:24px;'><a href='/entries?sort=oldest' style='background:#2563eb;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold;'>View History on Lumina</a></p>" .
        "</div>"
    );
});
