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
                $user->locale = $locale;
                $user->update();
                session(['locale' => $locale]);
            } else {
                $locale = Auth::user()->locale;
                session(['locale' => $locale]);
            }
            // return redirect(RouteServiceProvider::HOME);
        } else {
            $raw_locale = $request->session()->get('locale');
            if (empty($request->session()->get('locale'))) {
                $locale = Config::get('app.locale');
                session(['locale' => $locale]);
            } else {
                if (in_array($raw_locale, Config::get('app.locales'))) {
                    $locale = $raw_locale;
                } else {
                    $locale = Config::get('app.locale');
                    session(['locale' => $locale]);
                }
            }
        }
        App::setLocale($locale);
        return $next($request);
    }
}
