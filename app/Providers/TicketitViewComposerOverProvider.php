<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TicketitViewComposerOverProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind('Vendor\kordy\ticketit\src\ViewComposers\TicketItComposer', \App\Vendor\Ticketit\TicketItComposer::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
