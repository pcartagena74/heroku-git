<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Config;
use Illuminate\Support\Facades\Auth;
use Session;

class Locale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //$raw_locale = Session::get('locale');
        if (Auth::check()) {
            if (empty(Auth::user()->locale)) {
                $locale             = Config::get('app.locale');
                $user               = Auth::user();
                $user->transalation = $locale;
                $user->update();
            } else {
                $locale = Auth::user()->locale;
            }
            // return redirect(RouteServiceProvider::HOME);
        } else {
            $raw_locale = $request->session()->get('locale');
            if (in_array($raw_locale, Config::get('app.locales'))) {
                $locale = $raw_locale;
            } else {
                $locale = Config::get('app.locale');
            }
        }
        App::setLocale($locale);
        return $next($request);
    }
}
