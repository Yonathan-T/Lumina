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