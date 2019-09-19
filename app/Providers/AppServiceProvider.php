<?php

namespace App\Providers;

use App\Registration;
use Illuminate\Support\ServiceProvider;
use Phirehose;
use App\TwitterStream;
use \Illuminate\Support\Facades\Blade;
use \Illuminate\Support\Facades\URL as URL;
use Illuminate\Pagination\Paginator;

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

        // Paginator::useBootstrapThree();

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
        $this->app->bind(\App\TwitterStream::class, function ($app) {
            $twitter_access_token = env('TWITTER_ACCESS_TOKEN', null);
            $twitter_access_token_secret = env('TWITTER_ACCESS_TOKEN_SECRET', null);
            return new TwitterStream($twitter_access_token, $twitter_access_token_secret, Phirehose::METHOD_FILTER);
        });

        $this->app->alias('bugsnag.multi', \Psr\Log\LoggerInterface::class);
        $this->app->alias('bugsnag.multi', \Psr\Log\LoggerInterface::class);

        \App\RegFinance::observe(new \Intouch\LaravelNewrelic\Observers\NewrelicTimingObserver() );
        \App\RegFinance::observe(new \Intouch\LaravelNewrelic\Observers\NewrelicCountingObserver() );
        \App\Registration::observe(new \Intouch\LaravelNewrelic\Observers\NewrelicTimingObserver() );
        \App\Registration::observe(new \Intouch\LaravelNewrelic\Observers\NewrelicCountingObserver() );
    }
}
