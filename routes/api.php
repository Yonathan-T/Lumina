<?php
Route::webhooks('/webhook/polar');
Route::post('/webhooks/github', [\App\Http\Controllers\GitHubWebhookController::class, 'handle']);

Route::get('/ping', function (\Illuminate\Http\Request $request) {
    $expectedToken = config('services.health.token');
    $providedToken = $request->query('token') ?: $request->bearerToken() ?: $request->header('X-Health-Token');

    if (empty($expectedToken) || !hash_equals((string) $expectedToken, (string) $providedToken)) {
        abort(404);
    }

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

$verifyCronToken = function (\Illuminate\Http\Request $request): void {
    $expectedTokens = array_filter([
        config('services.health.token'),
        env('CRON_SECRET'),
    ]);
    $providedToken = $request->query('token') ?: $request->bearerToken() ?: $request->header('X-Health-Token');

    $isValid = false;
    foreach ($expectedTokens as $expected) {
        if (!empty($expected) && !empty($providedToken) && hash_equals((string) $expected, (string) $providedToken)) {
            $isValid = true;
            break;
        }
    }

    if (!$isValid) {
        abort(404);
    }
};

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