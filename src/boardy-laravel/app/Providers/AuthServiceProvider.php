<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Passport::authorizationView('auth.oauth.authorize');
        Passport::ignoreRoutes();

        Passport::tokensExpireIn(now()->addMinutes(1));
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }
}