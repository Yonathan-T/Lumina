<?php

namespace App\Providers;
use BladeUI\Icons\Factory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use Carbon\Carbon;


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
        Gate::define('is-subscribed', function (User $user) {
            // Check if the user has a plan and the subscription is still active.
            return $user->plan_id !== null &&
                $user->subscription_ends_at !== null &&
                Carbon::parse($user->subscription_ends_at)->isFuture();
        });
    }
}
