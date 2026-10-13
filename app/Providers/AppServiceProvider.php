<?php

namespace App\Providers;
use BladeUI\Icons\Factory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Carbon\Carbon;


use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') || str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        $userTimezone = request()->cookie('user_timezone') ?: config('app.timezone');
        if ($userTimezone && in_array($userTimezone, timezone_identifiers_list(), true)) {
            date_default_timezone_set($userTimezone);
        }
    }
}
