<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GitHubWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Verify the request signature if a secret is configured
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

        // We only care about the 'watch' event (which is triggered when someone stars the repo)
        // However, the payload usually contains the updated repository info regardless of the event type
        // for many related events.
        
        $payload = $request->json()->all();

        if (isset($payload['repository']['stargazers_count'])) {
            $stars = $payload['repository']['stargazers_count'];
            
            // Format the stars (e.g., 1.2K)
            $formattedStars = $stars;
            if ($stars >= 1000) {
                $formattedStars = number_format($stars / 1000, 1) . 'K+';
            }

            // Update the cache forever (until next webhook)
            Cache::forever('github_stars_v4', $formattedStars);
            
            Log::info("GitHub Webhook: Updated stars to {$formattedStars}");
            return response()->json(['message' => 'Stars updated successfully']);
        }

        return response()->json(['message' => 'No star count found in payload'], 200);
    }
}
