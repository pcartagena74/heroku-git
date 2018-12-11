<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Phirehose;
use App\TwitterStream;
use \Illuminate\Support\Facades\Blade;
use \Illuminate\Support\Facades\URL as URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if(env('APP_ENV') != 'local'){
            URL::forceScheme('https');
        }

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

        $this->app->alias('bugsnag.multi', \Illuminate\Contracts\Logging\Log::class);
        $this->app->alias('bugsnag.multi', \Psr\Log\LoggerInterface::class);
    }
}
