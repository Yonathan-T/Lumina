<?php

namespace App\Providers;
use BladeUI\Icons\Factory;

use Illuminate\Support\ServiceProvider;

class BladeIconsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Using default configuration from blade-icons package
    }
}

