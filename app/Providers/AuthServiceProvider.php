<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        
        // Basic gates for your 3 tiers
        Gate::define('access-premium', function (User $user) {
            // Clear cached relationships to force fresh query
            $user->unsetRelation('subscriptions');
            return $user->hasActiveSubscription(); // Any paid plan
        });
        
        Gate::define('access-standard', function (User $user) {
            // Clear cached relationships to force fresh query
            $user->unsetRelation('subscriptions');
            return $user->hasMinimumPlan('standard'); // Standard or higher
        });
        
        Gate::define('access-pro', function (User $user) {
            // Clear cached relationships to force fresh query
            $user->unsetRelation('subscriptions');
            return $user->hasMinimumPlan('pro'); // Pro plan only
        });
    }
}
