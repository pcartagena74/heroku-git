<?php

namespace App\Http\Controllers;

use Entrust;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Closure;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('showApplicationRoutes');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('v1.public_pages.home-login');
    }

    public function store(Request $request)
    {
        // This is the function that processes issues reported by error page
        // Responds to POST /reportissue
        dd($request);
    }

    /**
     * Show application routes.
     *
     * Forbidden in production environment.
     *
     * @return \Illuminate\View\View
     */
    public function showApplicationRoutes()
    {
        /*
        if (config('app.log_level') == 'production') {
            abort(403);
        }
        */

        if (Entrust::hasRole('Developer')) {
            $routes = collect(Route::getRoutes());

            $routes = $routes->map(function ($route) {
                return [
                    'host' => $route->action['where'],
                    'uri' => $route->uri,
                    'name' => $route->action['as'] ?? '',
                    'methods' => $route->methods,
                    'action' => $route->action['controller'] ?? 'Closure',
                    'middleware' => $this->getRouteMiddleware($route),
                ];
            });

            return view('v1.auth_pages.admin.routes', compact('routes'));
        } else {
            request()->session()->flash('alert-warning', trans('ticketit::lang.you-are-not-permitted-to-access'));
            return back();
        }
    }

    /**
     * Get route middleware.
     *
     * @param \Illuminate\Routing\Route $route
     *
     * @return string
     */
    protected function getRouteMiddleware($route)
    {
        return collect($route->gatherMiddleware())->map(function ($middleware) {
            return $middleware instanceof Closure ? 'Closure' : $middleware;
        })->implode(', ');
    }
}
