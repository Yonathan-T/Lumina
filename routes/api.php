<?php
Route::webhooks('/webhook/polar');
Route::post('/webhooks/github', [\App\Http\Controllers\GitHubWebhookController::class, 'handle']);