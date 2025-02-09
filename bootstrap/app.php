<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders()
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        // channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(RouteServiceProvider::HOME);

        $middleware->encryptCookies(except: [
            //
        ]);
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'reportissue',
        ]);

        $middleware->append(\App\Http\Middleware\Locale::class);

        $middleware->web(\App\Http\Middleware\Locale::class);

        $middleware->throttleApi();

        $middleware->replace(\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class, \App\Http\Middleware\PreventRequestsDuringMaintenance::class);
        $middleware->replace(\Illuminate\Foundation\Http\Middleware\TrimStrings::class, \App\Http\Middleware\TrimStrings::class);
        $middleware->replace(\Illuminate\Http\Middleware\TrustHosts::class, \App\Http\Middleware\TrustHosts::class);
        $middleware->replace(\Illuminate\Http\Middleware\TrustProxies::class, \App\Http\Middleware\TrustProxies::class);

        $middleware->alias([
            'ability' => \Shanmuga\LaravelEntrust\Middleware\LaravelEntrustAbility::class,
            'permission' => \Shanmuga\LaravelEntrust\Middleware\LaravelEntrustPermission::class,
            'role' => \Shanmuga\LaravelEntrust\Middleware\LaravelEntrustRole::class,
            'signed' => \App\Http\Middleware\ValidateSignature::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
