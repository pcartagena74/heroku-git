<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class LocaleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->hasCookie('locale')) {
            $locale = $request->cookie('locale');

            if (!preg_match('/^[a-zA-Z]{2}$/', $locale) &&
                !preg_match('/^[a-zA-Z0-9]+\|[a-zA-Z]{2}$/', $locale)) {
                $response->withCookie(Cookie::forget('locale'));
            }
        }

        return $response;
    }
}