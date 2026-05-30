<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Passport\PassportServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->append(\App\Http\Middleware\RefreshTokenCookie::class);
        
        $middleware->validateCsrfTokens(except: [
            'oauth/token',
            'oauth/callback',
        ]);
    })
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
	    PassportServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();