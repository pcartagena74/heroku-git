<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Config;
use Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Locale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->hasSession()) {
            return $next($request);
        }
        try {
            $locale = Config::get('app.fallback_locale');

            if (Auth::check()) {
                if (empty(Auth::user()->locale)) {
                    $user = Auth::user();
                    $user->locale = $locale;
                    $user->update();
                    session(['locale' => $locale]);
                } else {
                    $userLocale = Auth::user()->locale;

                    // Validate user's stored locale
                    if (str_contains($userLocale, '|')) {
                        $parts = explode('|', $userLocale);
                        $userLocale = end($parts);
                    }

                    if (in_array($userLocale, Config::get('app.locales'))) {
                        $locale = $userLocale;
                    } else {
                        $user = Auth::user();
                        $user->locale = $locale;
                        $user->update();
                    }
                    session(['locale' => $locale]);
                }
            } else {
                $cookieLocale = Cookie::get('locale');

                if (!empty($cookieLocale)) {
                    // Clean potentially corrupted cookie value
                    if (strpos($cookieLocale, '|') !== false) {
                        $parts = explode('|', $cookieLocale);
                        $cookieLocale = end($parts);
                    }

                    if (in_array($cookieLocale, Config::get('app.locales'))) {
                        $locale = $cookieLocale;
                    }
                }
            }
            App::setLocale($locale);
            Cookie::queue('locale', $locale, 60);
            session(['locale' => $locale]);
        } catch (\Exception $e) {
            // Failsafe: use fallback locale
            $locale = Config::get('app.fallback_locale');
            App::setLocale($locale);
            Cookie::queue('locale', $locale, 60);
            session(['locale' => $locale]);
        }
        // App::setLocale($locale);
        return $next($request);
    }
}
