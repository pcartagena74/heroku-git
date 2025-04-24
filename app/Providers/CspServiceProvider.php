<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Csp\Nonce\NonceGenerator;

class CspServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Generate nonces early
        $scriptNonce = app(NonceGenerator::class)->generate();
        $styleNonce = app(NonceGenerator::class)->generate();

        // Share with all views
        view()->share([
            'cspScriptNonce' => $scriptNonce,
            'cspStyleNonce' => $styleNonce,
        ]);
    }
}
