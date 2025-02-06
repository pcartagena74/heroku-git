<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App;
use Closure;
use Config;
use Cookie;
use Illuminate\Support\Facades\Auth;

// Symfony\Component\HttpFoundation\Cookie
class Locale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            if (empty(Auth::user()->locale)) {
                $locale = Config::get('app.locale');
                $user = Auth::user();
                $user->locale = $locale;
                $user->update();
                session(['locale' => $locale]);
            } else {
                $locale = Auth::user()->locale;
                session(['locale' => $locale]);
            }
            // return redirect(RouteServiceProvider::HOME);
        } else {
            $locale = Cookie::get('locale');
            if (! empty($locale)) {
                if (in_array($locale, Config::get('app.locales'))) {
                    //do nothing
                } else {
                    $locale = Config::get('app.locale');
                    session(['locale' => $locale]);
                }
            } else {
                $locale = Config::get('app.locale');
            }
            Cookie::queue('locale', $locale, 60);
        }
        App::setLocale($locale);

        return $next($request);
    }
}
