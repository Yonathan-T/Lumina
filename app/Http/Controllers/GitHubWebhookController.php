<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GitHubWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('services.github.webhook_secret');
        if ($secret) {
            $signature = $request->header('X-Hub-Signature-256');
            $payload = $request->getContent();
            $hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);

            if (!hash_equals($hash, $signature)) {
                Log::warning('GitHub Webhook: Invalid signature');
                return response()->json(['message' => 'Invalid signature'], 403);
            }

        }
        
        $payload = $request->json()->all();

        if (isset($payload['repository']['stargazers_count'])) {
            $stars = $payload['repository']['stargazers_count'];
            
            $formattedStars = $stars;
            if ($stars >= 1000) {
                $formattedStars = number_format($stars / 1000, 1) . 'K+';
            }

            Cache::forever('github_stars_v4', $formattedStars);
            
            Log::info("GitHub Webhook: Updated stars to {$formattedStars}");
            return response()->json(['message' => 'Stars updated successfully']);
        }

        return response()->json(['message' => 'No star count found in payload'], 200);
    }
}
