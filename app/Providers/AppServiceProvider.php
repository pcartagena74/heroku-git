<?php

namespace App\Providers;

use App\Models\RegFinance;
use App\Models\Registration;
use App\Models\TwitterStream;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL as URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\DuskServiceProvider;

//use Phirehose;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
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

        $this->bootRoute();
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        /*
        $this->app->bind(TwitterStream::class, function ($app) {
            $twitter_access_token = env('TWITTER_ACCESS_TOKEN', null);
            $twitter_access_token_secret = env('TWITTER_ACCESS_TOKEN_SECRET', null);

            return new TwitterStream($twitter_access_token, $twitter_access_token_secret, Phirehose::METHOD_FILTER);
        });
        */

        $this->app->singleton('Markdown', function ($app) {

            // Obtain a pre-configured Environment with all the CommonMark parsers/renderers ready-to-go
            $environment = \League\CommonMark\Environment::createCommonMarkEnvironment();

            // Define your configuration:
            $config = ['html_input' => 'escape'];

            // Create the converter
            return new \League\CommonMark\CommonMarkConverter($config, $environment);
        });

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

    public function bootRoute(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

    }
}
