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

// One-Time Route to restore clean reflections for ALL users
Route::get('/system/restore-clean-entries', function (\Illuminate\Http\Request $request) {
    $token = $request->query('token') ?? $request->query('secret');
    if ($token !== 'lumina-option-b-migrate-2026') {
        abort(403, 'Unauthorized');
    }

    $jsonPath = database_path('data/all_users_clean_data.json');
    if (!file_exists($jsonPath)) {
        return response("Data file not found", 404);
    }

    $data = json_decode(file_get_contents($jsonPath), true);
    $userMap = []; // local_id -> production User instance
    foreach ($data['users'] as $localId => $uInfo) {
        $prodUser = \App\Models\User::where('email', $uInfo['email'])->first();
        if ($prodUser) {
            $userMap[$localId] = $prodUser;
        }
    }

    $restoredEntries = 0;
    foreach ($data['entries'] as $item) {
        $user = $userMap[$item['user_id']] ?? \App\Models\User::find($item['user_id']);
        if (!$user) continue;

        $userEnc = $user->getEncrypter();
        $entry = \App\Models\Entry::where('id', $item['id'])->first()
              ?: \App\Models\Entry::where('user_id', $user->id)
                    ->where('created_at', $item['created_at'])->first();

        if ($entry) {
            \Illuminate\Support\Facades\DB::table('entries')->where('id', $entry->id)->update([
                'title' => $userEnc->encryptString((string) $item['title']),
                'content' => $userEnc->encryptString((string) $item['content']),
            ]);
            $restoredEntries++;
        }
    }

    $restoredConvs = 0;
    foreach ($data['conversations'] as $cItem) {
        $conv = \App\Models\Conversation::find($cItem['id']);
        if ($conv && $conv->user) {
            $userEnc = $conv->user->getEncrypter();
            \Illuminate\Support\Facades\DB::table('conversations')->where('id', $conv->id)->update([
                'title' => $userEnc->encryptString((string) $cItem['title']),
            ]);
            $restoredConvs++;
        }
    }

    $restoredMsgs = 0;
    foreach ($data['messages'] as $mItem) {
        $msg = \App\Models\Message::find($mItem['id']);
        if ($msg && $msg->conversation?->user) {
            $userEnc = $msg->conversation->user->getEncrypter();
            \Illuminate\Support\Facades\DB::table('messages')->where('id', $msg->id)->update([
                'content' => $userEnc->encryptString((string) $mItem['content']),
            ]);
            $restoredMsgs++;
        }
    }

    return response(
        "<div style='background:#060b16;color:#e2e8f0;padding:32px;font-family:monospace;min-height:100vh;'>" .
        "<h2 style='color:#34d399;'>🎉 Successfully Restored Data Across All Users!</h2>" .
        "<ul style='font-size:16px;line-height:2;margin:16px 0;'>" .
        "<li>Restored Entries: <strong style='color:#60a5fa;'>{$restoredEntries}</strong></li>" .
        "<li>Restored Conversations: <strong style='color:#60a5fa;'>{$restoredConvs}</strong></li>" .
        "<li>Restored Messages: <strong style='color:#60a5fa;'>{$restoredMsgs}</strong></li>" .
        "</ul>" .
        "<p style='color:#94a3b8;'>Every user's entries have been re-encrypted with their own individual Option B key.</p>" .
        "<p style='margin-top:24px;'><a href='/entries?sort=oldest' style='background:#2563eb;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:bold;'>View History on Lumina</a></p>" .
        "</div>"
    );
});

// Diagnostic & Auto-heal endpoint for all entities (Entries, Conversations, Messages)
Route::get('/system/inspect-and-heal-entries', function (\Illuminate\Http\Request $request) {
    $token = $request->query('token') ?? $request->query('secret');
    if ($token !== 'lumina-option-b-migrate-2026') {
        abort(403, 'Unauthorized');
    }

    $keys = array_values(array_unique(array_filter([
        config('app.key'),
        'base64:0uHCR8r5ymbQ+jphDGP879HWojhn75UHBYQSuMdd9pE=',
        'base64:2WbY47N72l3ZRXag7RHrjy+jX6J4Kkk3XWZLVDcme+U=',
        'base64:WcZMeHqadl9tb4SaeuCBefokHTD4zX9QiPRotjAHWdA=',
        ...config('app.previous_keys', []),
    ])));

    $encrypters = [];
    foreach ($keys as $k) {
        try {
            $raw = base64_decode(str_starts_with($k, 'base64:') ? substr($k, 7) : $k);
            if (strlen($raw) === 32) {
                $encrypters[] = new \Illuminate\Encryption\Encrypter($raw, 'AES-256-CBC');
            }
        } catch (\Throwable $e) {}
    }

    $tryDecrypt = function ($value, $userEnc) use ($encrypters) {
        if (empty($value) || !is_string($value)) return $value;

        // Try user DEK first
        if ($userEnc) {
            try {
                $d = $userEnc->decryptString($value);
                if (!str_contains($d, 'Key Updated') && !str_contains($d, 'previous key') && !str_contains($d, 'Archived')) {
                    return $d;
                }
            } catch (\Throwable $e) {}
        }

        // Try candidate master encrypters
        foreach ($encrypters as $enc) {
            try {
                $d = $enc->decryptString($value);
                if (!str_contains($d, 'Key Updated') && !str_contains($d, 'previous key') && !str_contains($d, 'Archived')) {
                    return $d;
                }
            } catch (\Throwable $e) {}
        }

        return null;
    };

    // Load clean snapshot if available
    $cleanPath = database_path('data/all_users_clean_data.json');
    $cleanData = file_exists($cleanPath) ? json_decode(file_get_contents($cleanPath), true) : null;
    $cleanEntriesById = [];
    $cleanConvsById = [];
    $cleanMsgsById = [];
    if ($cleanData) {
        foreach ($cleanData['entries'] ?? [] as $ce) {
            $cleanEntriesById[$ce['id']] = $ce;
        }
        foreach ($cleanData['conversations'] ?? [] as $cc) {
            $cleanConvsById[$cc['id']] = $cc;
        }
        foreach ($cleanData['messages'] ?? [] as $cm) {
            $cleanMsgsById[$cm['id']] = $cm;
        }
    }

    // 1. Process Entries
    $entryResults = [];
    $healedEntries = 0;
    $entries = \App\Models\Entry::all();

    foreach ($entries as $e) {
        $rawTitle = $e->getRawOriginal('title');
        $rawContent = $e->getRawOriginal('content');
        $user = $e->user ?: \App\Models\User::find($e->user_id);
        $userEnc = $user?->getEncrypter();

        $entryStatus = [
            'id' => $e->id,
            'user_id' => $e->user_id,
            'user_email' => $user?->email,
            'created_at' => (string) $e->created_at,
            'status' => 'unknown',
            'resolved_title' => null,
        ];

        $decryptedTitle = $tryDecrypt($rawTitle, $userEnc);
        $decryptedContent = $tryDecrypt($rawContent, $userEnc);

        // Fallback to clean snapshot if needed
        if (!$decryptedTitle && isset($cleanEntriesById[$e->id])) {
            $decryptedTitle = $cleanEntriesById[$e->id]['title'];
        }
        if (!$decryptedContent && isset($cleanEntriesById[$e->id])) {
            $decryptedContent = $cleanEntriesById[$e->id]['content'];
        }

        if ($decryptedTitle) {
            $alreadyHealthy = false;
            if ($userEnc) {
                try {
                    if ($userEnc->decryptString($rawTitle) === $decryptedTitle) {
                        $alreadyHealthy = true;
                    }
                } catch (\Throwable $ex) {}
            }

            if ($alreadyHealthy) {
                $entryStatus['status'] = 'healthy_user_dek';
            } else {
                if ($userEnc) {
                    \Illuminate\Support\Facades\DB::table('entries')->where('id', $e->id)->update([
                        'title' => $userEnc->encryptString((string) $decryptedTitle),
                        'content' => $decryptedContent ? $userEnc->encryptString((string) $decryptedContent) : $rawContent,
                    ]);
                    $healedEntries++;
                    $entryStatus['status'] = 'healed_to_user_dek';
                }
            }
            $entryStatus['resolved_title'] = $decryptedTitle;
        } else {
            $entryStatus['status'] = 'raw_ciphertext_failed_decryption';
            $entryStatus['resolved_title'] = $e->title;
        }

        $entryResults[] = $entryStatus;
    }

    // 2. Process Conversations
    $healedConvs = 0;
    $healthyConvs = 0;
    foreach (\App\Models\Conversation::all() as $conv) {
        $user = $conv->user ?: \App\Models\User::find($conv->user_id);
        if (!$user) continue;
        $userEnc = $user->getEncrypter();
        $rawTitle = $conv->getRawOriginal('title');

        $decryptedTitle = $tryDecrypt($rawTitle, $userEnc);
        if (!$decryptedTitle && isset($cleanConvsById[$conv->id])) {
            $decryptedTitle = $cleanConvsById[$conv->id]['title'];
        }

        if ($decryptedTitle) {
            $alreadyHealthy = false;
            try {
                if ($userEnc->decryptString($rawTitle) === $decryptedTitle) {
                    $alreadyHealthy = true;
                }
            } catch (\Throwable $ex) {}

            if ($alreadyHealthy) {
                $healthyConvs++;
            } else {
                \Illuminate\Support\Facades\DB::table('conversations')->where('id', $conv->id)->update([
                    'title' => $userEnc->encryptString((string) $decryptedTitle),
                ]);
                $healedConvs++;
            }
        }
    }

    // 3. Process Messages
    $healedMsgs = 0;
    $healthyMsgs = 0;
    foreach (\App\Models\Message::all() as $msg) {
        $conv = \App\Models\Conversation::find($msg->conversation_id);
        $user = $conv?->user ?: ($conv?->user_id ? \App\Models\User::find($conv->user_id) : null);
        if (!$user) continue;
        $userEnc = $user->getEncrypter();
        $rawContent = $msg->getRawOriginal('content');

        $decryptedContent = $tryDecrypt($rawContent, $userEnc);
        if (!$decryptedContent && isset($cleanMsgsById[$msg->id])) {
            $decryptedContent = $cleanMsgsById[$msg->id]['content'];
        }

        if ($decryptedContent) {
            $alreadyHealthy = false;
            try {
                if ($userEnc->decryptString($rawContent) === $decryptedContent) {
                    $alreadyHealthy = true;
                }
            } catch (\Throwable $ex) {}

            if ($alreadyHealthy) {
                $healthyMsgs++;
            } else {
                \Illuminate\Support\Facades\DB::table('messages')->where('id', $msg->id)->update([
                    'content' => $userEnc->encryptString((string) $decryptedContent),
                ]);
                $healedMsgs++;
            }
        }
    }

    return response()->json([
        'entries_summary' => [
            'total' => count($entries),
            'healed_now' => $healedEntries,
            'healthy_total' => count(array_filter($entryResults, fn($r) => $r['status'] === 'healthy_user_dek' || $r['status'] === 'healed_to_user_dek')),
        ],
        'conversations_summary' => [
            'healed_now' => $healedConvs,
            'healthy_total' => $healthyConvs + $healedConvs,
        ],
        'messages_summary' => [
            'healed_now' => $healedMsgs,
            'healthy_total' => $healthyMsgs + $healedMsgs,
        ],
        'entries' => $entryResults,
    ]);
});
