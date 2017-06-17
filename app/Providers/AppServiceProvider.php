<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Phirehose;
use App\TwitterStream;
use \Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('dd', function ($expression) {
            return "<?php dd(with{$expression}); ?>";
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\TwitterStream', function ($app) {
            $twitter_access_token = env('TWITTER_ACCESS_TOKEN', null);
            $twitter_access_token_secret = env('TWITTER_ACCESS_TOKEN_SECRET', null);
            return new TwitterStream($twitter_access_token, $twitter_access_token_secret, Phirehose::METHOD_FILTER);
        });
    }
}
