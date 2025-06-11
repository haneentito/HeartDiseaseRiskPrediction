<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Tymon\JWTAuth\JWTGuard;
use Tymon\JWTAuth\JWTAuth;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();

        // Register JWT authentication guards
        $this->app['auth']->extend('jwt', function ($app, $name, array $config) {
            return new JWTGuard(
                $app['tymon.jwt.auth'],
                $app['auth']->createUserProvider($config['provider']),
                $app['request']
            );
        });
    }
}