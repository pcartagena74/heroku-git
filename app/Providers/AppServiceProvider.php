<?php

namespace App\Providers;

use App\Models\RegFinance;
use App\Models\Registration;
use App\Models\TwitterStream;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL as URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\DuskServiceProvider;

//use Phirehose;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (env('APP_ENV') != 'local') {
            URL::forceRootUrl(\Config::get('app.url'));
            URL::forceScheme('https');
        }

        \Illuminate\Pagination\Paginator::useBootstrap();

        $this->app['request']->server->set('HTTPS', $this->app->environment() != 'local');

        Blade::directive('dd', function ($expression) {
            return "<?php dd({$expression}); ?>";
        });

        Blade::directive('trans_choice', function ($expression) {
            return "<?php trans_choice({$expression}); ?>";
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /*
        $this->app->bind(TwitterStream::class, function ($app) {
            $twitter_access_token = env('TWITTER_ACCESS_TOKEN', null);
            $twitter_access_token_secret = env('TWITTER_ACCESS_TOKEN_SECRET', null);

            return new TwitterStream($twitter_access_token, $twitter_access_token_secret, Phirehose::METHOD_FILTER);
        });
        */

        $this->app->alias('bugsnag.multi', \Psr\Log\LoggerInterface::class);
        $this->app->alias('bugsnag.multi', \Psr\Log\LoggerInterface::class);

        if ($this->app->environment('local', 'dev', 'test')) {
            $this->app->register(DuskServiceProvider::class);
            //$this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        //RegFinance::observe(new \Intouch\LaravelNewrelic\Observers\NewrelicTimingObserver());
        //RegFinance::observe(new \Intouch\LaravelNewrelic\Observers\NewrelicCountingObserver());
        //Registration::observe(new \Intouch\LaravelNewrelic\Observers\NewrelicTimingObserver());
        //Registration::observe(new \Intouch\LaravelNewrelic\Observers\NewrelicCountingObserver());

        if ($this->app->environment('local', 'test', 'queue')) {
            $this->app->register(\App\Providers\TelescopeServiceProvider::class);
        }
    }
}
