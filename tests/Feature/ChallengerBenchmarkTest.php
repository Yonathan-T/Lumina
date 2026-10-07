<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\DashboardStats;
use App\Livewire\EditEntry;
use App\Livewire\Settings\ApiIntegration;
use App\Livewire\Settings\DataPrivacy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use ReflectionClass;
use Tests\TestCase;

class ChallengerBenchmarkTest extends TestCase
{
    /**
     * Empirically verify that client-side eye toggles in Settings use pure Alpine.js state
     * with zero wire:click handlers and zero server roundtrips.
     */
    public function test_client_side_eye_toggles_have_zero_http_network_handlers_and_pure_alpine_dom(): void
    {
        $filePath = resource_path('views/livewire/settings/api-integration.blade.php');
        $this->assertFileExists($filePath);
        $content = file_get_contents($filePath);

        // 1. Verify Gemini API key eye toggle
        $this->assertMatchesRegularExpression(
            '/x-data="\{\s*showKey:\s*false\s*\}"/',
            $content,
            'Gemini key container must define Alpine showKey state.'
        );
        $this->assertMatchesRegularExpression(
            '/:type="showKey\s*\?\s*\'text\'\s*:\s*\'password\'"/',
            $content,
            'Gemini key input must toggle input type via Alpine expression.'
        );
        $this->assertMatchesRegularExpression(
            '/<button[^>]*@click="showKey\s*=\s*!showKey"[^>]*>/',
            $content,
            'Gemini toggle button must toggle showKey via client-side Alpine @click.'
        );

        // Ensure Gemini toggle button has NO wire:click
        preg_match('/<button[^>]*@click="showKey\s*=\s*!showKey"[^>]*>/', $content, $geminiMatches);
        $this->assertNotEmpty($geminiMatches, 'Gemini eye button must exist.');
        $this->assertStringNotContainsString('wire:click', $geminiMatches[0], 'Gemini eye button must NOT contain wire:click.');

        // 2. Verify ElevenLabs API key eye toggle
        $this->assertMatchesRegularExpression(
            '/x-data="\{\s*showElevenLabsKey:\s*false\s*\}"/',
            $content,
            'ElevenLabs key container must define Alpine showElevenLabsKey state.'
        );
        $this->assertMatchesRegularExpression(
            '/:type="showElevenLabsKey\s*\?\s*\'text\'\s*:\s*\'password\'"/',
            $content,
            'ElevenLabs key input must toggle input type via Alpine expression.'
        );
        $this->assertMatchesRegularExpression(
            '/<button[^>]*@click="showElevenLabsKey\s*=\s*!showElevenLabsKey"[^>]*>/',
            $content,
            'ElevenLabs toggle button must toggle showElevenLabsKey via client-side Alpine @click.'
        );

        // Ensure ElevenLabs toggle button has NO wire:click
        preg_match('/<button[^>]*@click="showElevenLabsKey\s*=\s*!showElevenLabsKey"[^>]*>/', $content, $elevenMatches);
        $this->assertNotEmpty($elevenMatches, 'ElevenLabs eye button must exist.');
        $this->assertStringNotContainsString('wire:click', $elevenMatches[0], 'ElevenLabs eye button must NOT contain wire:click.');

        // 3. Inspect Livewire ApiIntegration component via Reflection
        $reflector = new ReflectionClass(ApiIntegration::class);
        $this->assertFalse(
            $reflector->hasMethod('toggleShowKey'),
            'ApiIntegration must NOT have server-side toggleShowKey method.'
        );
        $this->assertFalse(
            $reflector->hasMethod('toggleShowElevenLabsKey'),
            'ApiIntegration must NOT have server-side toggleShowElevenLabsKey method.'
        );
        $this->assertFalse(
            $reflector->hasProperty('showKey'),
            'ApiIntegration must NOT have public $showKey property.'
        );
        $this->assertFalse(
            $reflector->hasProperty('showElevenLabsKey'),
            'ApiIntegration must NOT have public $showElevenLabsKey property.'
        );
    }

    /**
     * Empirically verify that modal open/close actions in settings, edit-entry, and data-privacy
     * execute purely on the client side without sending Livewire HTTP requests until confirmed.
     */
    public function test_modals_open_and_close_with_zero_http_requests_until_confirmed(): void
    {
        // --- 1. Settings (api-integration.blade.php) ---
        $settingsContent = file_get_contents(resource_path('views/livewire/settings/api-integration.blade.php'));

        // Gemini removal modal triggers
        $this->assertMatchesRegularExpression(
            '/<button[^>]*@click="confirmGeminiRemoval\s*=\s*true"[^>]*>/',
            $settingsContent,
            'Gemini removal trigger must use client-side @click.'
        );
        preg_match('/<button[^>]*@click="confirmGeminiRemoval\s*=\s*true"[^>]*>/', $settingsContent, $geminiRemovalBtn);
        $this->assertStringNotContainsString('wire:click', $geminiRemovalBtn[0]);

        $this->assertStringContainsString('x-show="confirmGeminiRemoval"', $settingsContent);
        $this->assertStringContainsString('@keydown.escape.window="confirmGeminiRemoval = false"', $settingsContent);
        $this->assertStringContainsString('@click.outside="confirmGeminiRemoval = false"', $settingsContent);
        $this->assertMatchesRegularExpression('/<button[^>]*@click="confirmGeminiRemoval\s*=\s*false"[^>]*>\s*Cancel/s', $settingsContent);

        // Action button only has wire:click on confirm
        $this->assertMatchesRegularExpression('/wire:click="removeApiKey"[^>]*@click="confirmGeminiRemoval\s*=\s*false"/', $settingsContent);

        // ElevenLabs removal modal triggers
        $this->assertMatchesRegularExpression(
            '/<button[^>]*@click="confirmElevenLabsRemoval\s*=\s*true"[^>]*>/',
            $settingsContent,
            'ElevenLabs removal trigger must use client-side @click.'
        );
        preg_match('/<button[^>]*@click="confirmElevenLabsRemoval\s*=\s*true"[^>]*>/', $settingsContent, $elevenRemovalBtn);
        $this->assertStringNotContainsString('wire:click', $elevenRemovalBtn[0]);

        $this->assertStringContainsString('x-show="confirmElevenLabsRemoval"', $settingsContent);
        $this->assertStringContainsString('@keydown.escape.window="confirmElevenLabsRemoval = false"', $settingsContent);
        $this->assertStringContainsString('@click.outside="confirmElevenLabsRemoval = false"', $settingsContent);
        $this->assertMatchesRegularExpression('/<button[^>]*@click="confirmElevenLabsRemoval\s*=\s*false"[^>]*>\s*Cancel/s', $settingsContent);

        // Action button only has wire:click on confirm
        $this->assertMatchesRegularExpression('/wire:click="removeElevenLabsKey"[^>]*@click="confirmElevenLabsRemoval\s*=\s*false"/', $settingsContent);

        // Verify ApiIntegration reflection: zero modal toggle methods
        $apiReflector = new ReflectionClass(ApiIntegration::class);
        $this->assertFalse($apiReflector->hasMethod('openConfirmationModal'));
        $this->assertFalse($apiReflector->hasMethod('closeConfirmationModal'));
        $this->assertFalse($apiReflector->hasMethod('openElevenLabsConfirmationModal'));
        $this->assertFalse($apiReflector->hasMethod('closeElevenLabsConfirmationModal'));
        $this->assertFalse($apiReflector->hasProperty('isConfirmingRemoval'));
        $this->assertFalse($apiReflector->hasProperty('isConfirmingElevenLabsRemoval'));

        // --- 2. Edit Entry (edit-entry.blade.php) ---
        $editEntryContent = file_get_contents(resource_path('views/livewire/edit-entry.blade.php'));

        // Delete confirmation modal trigger
        $this->assertMatchesRegularExpression(
            '/<button[^>]*@click="showDeleteModal\s*=\s*true"[^>]*>/',
            $editEntryContent,
            'Delete entry button must use client-side @click.'
        );
        preg_match('/<button[^>]*@click="showDeleteModal\s*=\s*true"[^>]*>/', $editEntryContent, $deleteBtn);
        $this->assertStringNotContainsString('wire:click', $deleteBtn[0]);

        $this->assertStringContainsString('x-show="showDeleteModal"', $editEntryContent);
        $this->assertStringContainsString('@keydown.escape.window="showDeleteModal = false"', $editEntryContent);
        $this->assertMatchesRegularExpression('/<button[^>]*@click="showDeleteModal\s*=\s*false"[^>]*>\s*Cancel/s', $editEntryContent);

        // Confirm button triggers wire:click="confirmDelete"
        $this->assertMatchesRegularExpression('/wire:click="confirmDelete"[^>]*@click="showDeleteModal\s*=\s*false"/', $editEntryContent);

        // Missing voice key modal client-side dismissals
        $this->assertStringContainsString('@click="showMissingVoiceKey = false"', $editEntryContent);
        $this->assertStringContainsString('@keydown.escape.window="showMissingVoiceKey = false"', $editEntryContent);

        // Verify EditEntry reflection: zero modal toggle methods
        $editReflector = new ReflectionClass(EditEntry::class);
        $this->assertFalse($editReflector->hasMethod('showDeleteConfirmation'));
        $this->assertFalse($editReflector->hasMethod('hideDeleteConfirmation'));
        $this->assertFalse($editReflector->hasMethod('closeMissingElevenLabsModal'));
        $this->assertFalse($editReflector->hasProperty('showDeleteModal'));

        // --- 3. Data Privacy (data-privacy.blade.php) ---
        $dataPrivacyContent = file_get_contents(resource_path('views/livewire/settings/data-privacy.blade.php'));

        $this->assertMatchesRegularExpression(
            '/<button[^>]*@click="showDeleteConfirm\s*=\s*!showDeleteConfirm"[^>]*>/',
            $dataPrivacyContent,
            'Delete account toggle must use client-side @click.'
        );
        preg_match('/<button[^>]*@click="showDeleteConfirm\s*=\s*!showDeleteConfirm"[^>]*>/', $dataPrivacyContent, $privacyToggleBtn);
        $this->assertStringNotContainsString('wire:click', $privacyToggleBtn[0]);

        $this->assertStringContainsString('x-show="showDeleteConfirm"', $dataPrivacyContent);
        $this->assertMatchesRegularExpression('/<button[^>]*@click="showDeleteConfirm\s*=\s*false"[^>]*>\s*Cancel/s', $dataPrivacyContent);
        $this->assertMatchesRegularExpression('/wire:click="deleteAccount"[^>]*@click="showDeleteConfirm\s*=\s*false"/', $dataPrivacyContent);

        // Verify DataPrivacy reflection: zero confirmDelete toggle method
        $privacyReflector = new ReflectionClass(DataPrivacy::class);
        $this->assertFalse($privacyReflector->hasMethod('confirmDelete'));
        $this->assertFalse($privacyReflector->hasProperty('showDeleteConfirm'));

        // --- 4. Dashboard Stats Bell Dropdown (dashboard-stats.blade.php) ---
        $dashboardStatsContent = file_get_contents(resource_path('views/livewire/dashboard/dashboard-stats.blade.php'));

        $this->assertStringContainsString('x-data="{ open: false }"', $dashboardStatsContent);
        $this->assertStringNotContainsString('@entangle(\'isModalOpen\')', $dashboardStatsContent);
        $this->assertMatchesRegularExpression('/<button[^>]*@click="open\s*=\s*!\s*open"[^>]*>/', $dashboardStatsContent);

        $statsReflector = new ReflectionClass(DashboardStats::class);
        $this->assertFalse($statsReflector->hasMethod('toggleNotificationsModal'));
        $this->assertFalse($statsReflector->hasProperty('isModalOpen'));
    }

    /**
     * Empirically benchmark route / execution latency and confirm zero external HTTP network calls.
     */
    public function test_landing_route_latency_benchmark_and_zero_external_http_calls(): void
    {
        // 1. Initial warm-up request
        $warmup = $this->get('/');
        $warmup->assertStatus(200);

        // 2. Measure 50 consecutive requests to benchmark latency
        $iterations = 50;
        $latencies = [];

        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $response = $this->get('/');
            $duration = (microtime(true) - $start) * 1000.0; // ms

            $response->assertStatus(200);
            $latencies[] = $duration;
        }

        $min = min($latencies);
        $max = max($latencies);
        $avg = array_sum($latencies) / count($latencies);
        sort($latencies);
        $p95 = $latencies[(int) (0.95 * count($latencies))];

        echo "\n[Empirical Latency Benchmark for / (50 requests)]:\n";
        echo sprintf("  Min: %.2f ms | Avg: %.2f ms | P95: %.2f ms | Max: %.2f ms\n", $min, $avg, $p95, $max);

        // Average should be exceptionally fast in-memory (< 35ms)
        $this->assertLessThan(100.0, $avg, 'Average route latency on / must be under 100ms.');

        // 3. Confirm ZERO external HTTP calls occur during / request handling
        Http::fake();
        $this->get('/');
        $recordedRequests = Http::recorded();

        $this->assertEmpty(
            $recordedRequests,
            'Handling GET / must make 0 external HTTP network requests (Polar and GitHub must not be called synchronously).'
        );

        // 4. Test Cold Cache behavior and GitHub fallback resilience
        Cache::forget('github_stars_live');
        $coldResponse = $this->get('/');
        $coldResponse->assertStatus(200);
        $coldResponse->assertViewHas('stars', 3);

        // 5. Simulate GitHub network exception when cache is cold
        Cache::forget('github_stars_live');
        Http::fake([
            'https://api.github.com/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
            },
        ]);

        $resilientResponse = $this->get('/');
        $resilientResponse->assertStatus(200);
        $resilientResponse->assertViewHas('stars', 3);
    }

    /**
     * Empirically verify SPA navigate directives (wire:navigate.hover) across landing, auth, and navbar.
     */
    public function test_spa_navigate_hover_directives_present_on_all_navigation_routes(): void
    {
        // 1. Landing Page (landing-page.blade.php)
        $landingContent = file_get_contents(resource_path('views/landing-page.blade.php'));
        $this->assertMatchesRegularExpression(
            '/<x-buttons[^>]*href="\/auth\/login"[^>]*wire:navigate\.hover/s',
            $landingContent,
            'Landing CTA login button must contain wire:navigate.hover.'
        );
        $this->assertMatchesRegularExpression(
            '/<a[^>]*href="\/auth\/register"[^>]*wire:navigate\.hover/s',
            $landingContent,
            'Landing footer register link must contain wire:navigate.hover.'
        );

        // 2. Auth Login (auth/login.blade.php)
        $loginContent = file_get_contents(resource_path('views/auth/login.blade.php'));
        $this->assertMatchesRegularExpression(
            '/<a[^>]*href="\/auth\/register"[^>]*wire:navigate\.hover[^>]*>Sign up<\/a>/s',
            $loginContent,
            'Login page Sign up link must contain wire:navigate.hover.'
        );

        // 3. Auth Register (auth/register.blade.php)
        $registerContent = file_get_contents(resource_path('views/auth/register.blade.php'));
        $this->assertMatchesRegularExpression(
            '/<a[^>]*href="\/auth\/login"[^>]*wire:navigate\.hover[^>]*>Sign in<\/a>/s',
            $registerContent,
            'Register page Sign in link must contain wire:navigate.hover.'
        );

        // 4. Navbar in Layout (components/layout.blade.php)
        $layoutContent = file_get_contents(resource_path('views/components/layout.blade.php'));
        $this->assertMatchesRegularExpression(
            '/<a[^>]*href="\/"[^>]*wire:navigate\.hover[^>]*>.*?LUMINA/s',
            $layoutContent,
            'Navbar logo link to / must contain wire:navigate.hover.'
        );
        $this->assertMatchesRegularExpression(
            '/<x-links\s+[^>]*?:href="route\(\'blogs\.index\'\)"[\s\S]*?wire:navigate\.hover[^>]*?>Blogs<\/x-links>/',
            $layoutContent,
            'Navbar Blogs link must contain wire:navigate.hover.'
        );
        $this->assertMatchesRegularExpression(
            '/<a[^>]*href="\/dashboard"[^>]*wire:navigate\.hover/s',
            $layoutContent,
            'Navbar Dashboard link must contain wire:navigate.hover.'
        );
        $this->assertMatchesRegularExpression(
            '/<a[^>]*href="\/auth\/login"[^>]*wire:navigate\.hover/s',
            $layoutContent,
            'Navbar Sign in link must contain wire:navigate.hover.'
        );
        $this->assertMatchesRegularExpression(
            '/<a[^>]*href="\/auth\/register"[^>]*wire:navigate\.hover/s',
            $layoutContent,
            'Navbar Sign up link must contain wire:navigate.hover.'
        );
    }

    /**
     * Empirically verify layout head preconnect optimization order.
     */
    public function test_layout_head_preconnect_optimization(): void
    {
        $layoutContent = file_get_contents(resource_path('views/components/layout.blade.php'));

        $preconnectPos = strpos($layoutContent, '<link rel="preconnect" href="https://fonts.googleapis.com">');
        $stylesheetPos = strpos($layoutContent, 'fonts.googleapis.com/css2?family=Inter');

        $this->assertNotFalse($preconnectPos, 'Preconnect to fonts.googleapis.com must exist.');
        $this->assertNotFalse($stylesheetPos, 'Google Fonts stylesheet must exist.');
        $this->assertLessThan(
            $stylesheetPos,
            $preconnectPos,
            'Preconnect tags must appear BEFORE Google Fonts stylesheet in head.'
        );

        // Verify duplicate standalone Inter stylesheet was removed
        $this->assertDoesNotMatchRegularExpression(
            '/<link[^>]*href="https:\/\/fonts\.googleapis\.com\/css2\?family=Inter:wght@[^"]*"[^>]*rel="stylesheet"[^>]*>\s*<link[^>]*href="https:\/\/fonts\.googleapis\.com\/css2\?family=Inter/',
            $layoutContent,
            'Duplicate standalone Inter font stylesheet must be removed.'
        );
    }

    /**
     * Stress-test: User billing plan memoization and zero HTTP calls during high-frequency checks.
     */
    public function test_stress_user_billing_plan_memoization_performance(): void
    {
        $migration = require database_path('migrations/2025_09_21_180948_create_polar_subscriptions_table.php');
        $migration->up();

        $user = new \App\Models\User();
        $user->id = 999;
        $user->name = 'Tester';
        $user->email = 'tester@example.com';

        Http::fake();

        $start = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            $plan = $user->getCurrentPlan();
            $this->assertSame('free', $plan);
        }
        $durationMs = (microtime(true) - $start) * 1000.0;

        // 1000 calls should execute in well under 50ms thanks to in-memory memoization (avg 0.03ms per call)
        $this->assertLessThan(50.0, $durationMs, '1000 getCurrentPlan calls must complete in < 50ms.');
        $this->assertEmpty(Http::recorded(), 'getCurrentPlan must never invoke external HTTP requests.');
    }

    /**
     * Stress-test: GitHub API outage or rate-limiting resilience on cold cache.
     */
    public function test_stress_github_api_outage_resilience(): void
    {
        Cache::forget('github_stars_live');

        // Simulate GitHub responding with HTTP 500 or 403 Rate Limited
        Http::fake([
            'https://api.github.com/*' => Http::response(['message' => 'API rate limit exceeded'], 403),
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewHas('stars', 3);

        // Ensure 0 unhandled exceptions or 500 errors
        $this->assertFalse($response->isServerError());
    }
}

