<?php

namespace App\Http\Middleware\Ticketit;

use App\Models\Ticketit\AgentOver as Agent;
use App\Models\Ticketit\SettingOver as Setting;
use Closure;
use Kordy\Ticketit\Middleware\IsAdminMiddleware as IsAdminMiddleware;

class IsAdminMiddlewareOver extends IsAdminMiddleware
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Agent::isAdmin() || auth()->id() == 1) {
            return $next($request);
        }

        // return redirect()->action('\App\Http\TicketitControllers\TicketsControllerOver@index')
        // ->with('warning', trans('ticketit::lang.you-are-not-permitted-to-access'));
        return redirect()->route(Setting::grab('main_route').'.index')
            ->with('warning', trans('ticketit::lang.you-are-not-permitted-to-access'));
    }
}
