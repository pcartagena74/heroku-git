<?php

namespace App\Vendor\Ticketit;

use App\Http\Controllers\Kordy;
use App\Http\TicketitControllers\InstallControllerOver as InstallController;
use App\Http\TicketitControllers\NotificationsControllerOver as NotificationsController;
use App\Models\Ticketit\TicketOver as Ticket;
use Collective\Html\FormFacade as CollectiveForm;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Kordy\Ticketit\Console\Htmlify;
use Kordy\Ticketit\Helpers\LaravelVersion;
use Kordy\Ticketit\Models\Comment;
use Kordy\Ticketit\Models\Setting;
use Kordy\Ticketit\ViewComposers\TicketItComposer;

class TicketitServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if (! Schema::hasTable('migrations')) {
            // Database isn't installed yet.
            return;
        }
        $installer = new InstallController;
        // if a migration or new setting is missing scape to the installation
        // updated the migration file path for installcontroller it will only pick migration from current migration directory
        if (empty($installer->inactiveMigrations()) && ! $installer->inactiveSettings()) {
            // Send the Agent User model to the view under $u
            // Send settings to views under $setting

            //cache $u
            $u = null;

            TicketItComposer::settings($u);

            // Adding HTML5 color picker to form elements
            CollectiveForm::macro('custom', function ($type, $name, $value = '#000000', $options = []) {
                $field = $this->input($type, $name, $value, $options);

                return $field;
            });

            TicketItComposer::general();
            TicketItComposer::codeMirror();
            TicketItComposer::sharedAssets();
            TicketItComposer::summerNotes();

            // Send notification when new comment is added
            Comment::creating(function ($comment) {
                if (Setting::grab('comment_notification')) {
                    $notification = new NotificationsController;
                    $notification->newComment($comment);
                }
            });
            // Send notification when ticket status is modified
            Ticket::updating(function ($modified_ticket) {
                if (Setting::grab('status_notification')) {
                    $original_ticket = Ticket::find($modified_ticket->id);
                    if ($original_ticket->status_id != $modified_ticket->status_id || $original_ticket->completed_at != $modified_ticket->completed_at) {
                        $notification = new NotificationsController;
                        $notification->ticketStatusUpdated($modified_ticket, $original_ticket);
                    }
                }
                if (Setting::grab('assigned_notification')) {
                    $original_ticket = Ticket::find($modified_ticket->id);
                    if ($original_ticket->agent->id != $modified_ticket->agent->id) {
                        $notification = new NotificationsController;
                        $notification->ticketAgentUpdated($modified_ticket, $original_ticket);
                    }
                }

                return true;
            });

            // Send notification when ticket status is modified
            Ticket::created(function ($ticket) {
                if (Setting::grab('assigned_notification')) {
                    $notification = new NotificationsController;
                    $notification->newTicketNotifyAgent($ticket);
                }

                return true;
            });

            $this->loadTranslationsFrom(__DIR__.'/Translations', 'ticketit');

            $viewsDirectory = __DIR__.'/Views/bootstrap3';
            if (Setting::grab('bootstrap_version') == '4') {
                $viewsDirectory = __DIR__.'/Views/bootstrap4';
            }

            $this->loadViewsFrom($viewsDirectory, 'ticketit');

            $this->publishes([$viewsDirectory => base_path('resources/views/vendor/ticketit')], 'views');
            $this->publishes([__DIR__.'/Translations' => base_path('lang/vendor/ticketit')], 'lang');
            $this->publishes([__DIR__.'/Public' => public_path('vendor/ticketit')], 'public');
            $this->publishes([__DIR__.'/Migrations' => base_path('database/migrations')], 'db');

            // Check public assets are present, publish them if not
            //            $installer->publicAssets();

            $main_route = Setting::grab('main_route');
            $main_route_path = Setting::grab('main_route_path');
            $admin_route = Setting::grab('admin_route');
            $admin_route_path = Setting::grab('admin_route_path');

            if (file_exists(Setting::grab('routes'))) {
                include Setting::grab('routes');
            } else {
                include __DIR__.'/routes.php';
            }
        } elseif (Request::path() == 'tickets-install'
            || Request::path() == 'tickets-upgrade'
            || Request::path() == 'tickets'
            || Request::path() == 'tickets-admin'
            || (isset($_SERVER['ARTISAN_TICKETIT_INSTALLING']) && $_SERVER['ARTISAN_TICKETIT_INSTALLING'])) {
            $this->loadTranslationsFrom(__DIR__.'/Translations', 'ticketit');
            $this->loadViewsFrom(__DIR__.'/Views/bootstrap3', 'ticketit');
            $this->publishes([__DIR__.'/Migrations' => base_path('database/migrations')], 'db');

            $authMiddleware = Helpers\LaravelVersion::authMiddleware();

            Route::get('/tickets-install', [Kordy\Ticketit\Controllers\InstallController::class, 'index'])->middleware($authMiddleware)->name('tickets.install.index');
            Route::post('/tickets-install', [Kordy\Ticketit\Controllers\InstallController::class, 'setup'])->middleware($authMiddleware)->name('tickets.install.setup');
            Route::get('/tickets-upgrade', [Kordy\Ticketit\Controllers\InstallController::class, 'upgrade'])->middleware($authMiddleware)->name('tickets.install.upgrade');
            Route::get('/tickets', function () {
                return redirect()->route('tickets.install.index');
            });
            Route::get('/tickets-admin', function () {
                return redirect()->route('tickets.install.index');
            });
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        /*
         * Register the service provider for the dependency.
         */
        $this->app->register(\Collective\Html\HtmlServiceProvider::class);

        if (LaravelVersion::min('5.4')) {
            $this->app->register(\Yajra\DataTables\DataTablesServiceProvider::class);
        } else {
            $this->app->register(\Yajra\Datatables\DatatablesServiceProvider::class);
        }

        $this->app->register(\Jenssegers\Date\DateServiceProvider::class);
        $this->app->register(\Mews\Purifier\PurifierServiceProvider::class);
        /*
         * Create aliases for the dependency.
         */
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('CollectiveForm', 'Collective\Html\FormFacade');

        /*
         * Register htmlify command. Need to run this when upgrading from <=0.2.2
         */

        $this->app->singleton('command.kordy.ticketit.htmlify', function ($app) {
            return new Htmlify;
        });
        $this->commands('command.kordy.ticketit.htmlify');
    }
}
