<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    'locales' => ['en', 'es'],

    'log' => env('APP_LOG', 'daily'),

    'log_level' => env('APP_LOG_LEVEL', 'debug'),

    'log_max_files' => 10,

    'providers' => ServiceProvider::defaultProviders()->merge([
        Bugsnag\BugsnagLaravel\BugsnagServiceProvider::class,
        Collective\Html\HtmlServiceProvider::class,
        //Intouch\LaravelNewrelic\NewrelicServiceProvider::class,
        Collective\Html\HtmlServiceProvider::class,

        //Kouz\LaravelAirbrake\ServiceProvider::class,

        /*
         * Package Service Providers...
         */
        //Kordy\Ticketit\TicketitServiceProvider::class,
        App\Vendor\Ticketit\TicketitServiceProvider::class,
        Shanmuga\LaravelEntrust\LaravelEntrustServiceProvider::class,

        // Laravel\Cashier\CashierServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\TelescopeServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
    ])->toArray(),

    'aliases' => Facade::defaultAliases()->merge([
        'Agent' => Jenssegers\Agent\Facades\Agent::class,
        'Bugsnag' => Bugsnag\BugsnagLaravel\Facades\Bugsnag::class,
        'Entrust' => Shanmuga\LaravelEntrust\Facades\LaravelEntrustFacade::class,
        'Form' => Collective\Html\FormFacade::class,
        'Html' => Collective\Html\HtmlFacade::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
    ])->toArray(),

];
