<?php
Route::webhooks('/webhook/polar');
Route::post('/webhooks/github', [\App\Http\Controllers\GitHubWebhookController::class, 'handle']);

$verifyCronToken = function (\Illuminate\Http\Request $request): void {
    // 1. Automatically trust Vercel's automated Cron scheduler
    if (str_starts_with((string) $request->userAgent(), 'vercel-cron') || $request->hasHeader('x-vercel-cron-schedule')) {
        return;
    }

    // 2. Otherwise verify token query string, Bearer token, or X-Health-Token
    $expectedTokens = array_filter([
        config('services.health.token'),
        env('CRON_SECRET'),
    ]);
    $providedToken = $request->query('token') ?: $request->bearerToken() ?: $request->header('X-Health-Token');

    foreach ($expectedTokens as $expected) {
        if (!empty($expected) && !empty($providedToken) && hash_equals((string) $expected, (string) $providedToken)) {
            return;
        }
    }

    abort(404);
};

Route::get('/ping', function (\Illuminate\Http\Request $request) use ($verifyCronToken) {
    $verifyCronToken($request);

    $dbStatus = 'ok';
    $dbLatencyMs = 0;

    try {
        $start = microtime(true);
        \Illuminate\Support\Facades\DB::select('SELECT 1');
        $dbLatencyMs = round((microtime(true) - $start) * 1000, 2);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'degraded',
            'database' => 'disconnected',
            'error' => $e->getMessage(),
        ], 500);
    }

    return response()->json([
        'status' => 'ok',
        'database' => $dbStatus,
        'db_latency_ms' => $dbLatencyMs,
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::get('/blogs/fetch', function (\Illuminate\Http\Request $request) use ($verifyCronToken) {
    $verifyCronToken($request);

    try {
        \Illuminate\Support\Facades\Artisan::call('blogs:fetch');
        $output = \Illuminate\Support\Facades\Artisan::output();

        return response()->json([
            'status' => 'success',
            'output' => trim($output),
            'timestamp' => now()->toIso8601String(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/reminders/daily', function (\Illuminate\Http\Request $request) use ($verifyCronToken) {
    $verifyCronToken($request);

    try {
        \Illuminate\Support\Facades\Artisan::call('reminders:daily');
        $output = \Illuminate\Support\Facades\Artisan::output();

        return response()->json([
            'status' => 'success',
            'output' => trim($output),
            'timestamp' => now()->toIso8601String(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/reminders/streak', function (\Illuminate\Http\Request $request) use ($verifyCronToken) {
    $verifyCronToken($request);

    try {
        \Illuminate\Support\Facades\Artisan::call('reminders:streak');
        $output = \Illuminate\Support\Facades\Artisan::output();

        return response()->json([
            'status' => 'success',
            'output' => trim($output),
            'timestamp' => now()->toIso8601String(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});