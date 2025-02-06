<?php

namespace App\Http\Middleware\Ticketit;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use App\Models\Ticketit\AgentOver as Agent;
use App\Models\Ticketit\SettingOver as Setting;
use Closure;
use Kordy\Ticketit\Middleware\IsAgentMiddleware as IsAgentMiddleware;

class IsAgentMiddlewareOver extends IsAgentMiddleware
{
    /**
     * Run the request filter.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Agent::isAgent() || Agent::isAdmin() || auth()->id() == 1) {
            return $next($request);
        }

        // return redirect()->action('\App\Http\TicketitControllers\TicketsControllerOver@index')
        //     ->with('warning', trans('ticketit::lang.you-are-not-permitted-to-access'));
        return redirect()->route(Setting::grab('main_route').'.index')
            ->with('warning', trans('ticketit::lang.you-are-not-permitted-to-access'));
    }
}
