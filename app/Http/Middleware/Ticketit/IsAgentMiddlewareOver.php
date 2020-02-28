<?php

namespace App\Http\Middleware\Ticketit;

use Closure;
use Kordy\Ticketit\Models\Agent;
use Kordy\Ticketit\Middleware\IsAgentMiddleware as IsAgentMiddleware;

class IsAgentMiddlewareOver extends IsAgentMiddleware
{
    /**
     * Run the request filter.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //auth()->user()->hasRole('Admin')
        if (Agent::isAgent() || Agent::isAdmin()) {
            return $next($request);
        }

        // return redirect()->action('\App\Http\TicketitControllers\TicketsControllerOver@index')
        //     ->with('warning', trans('ticketit::lang.you-are-not-permitted-to-access'));
         return redirect()->route(Setting::grab('main_route'). '.index')
            ->with('warning', trans('ticketit::lang.you-are-not-permitted-to-access'));
    }
}
