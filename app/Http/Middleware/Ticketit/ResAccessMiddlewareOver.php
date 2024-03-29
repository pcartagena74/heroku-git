<?php

namespace App\Http\Middleware\Ticketit;

use App\Models\Ticketit\AgentOver as Agent;
use App\Models\Ticketit\SettingOver as Setting;
use Closure;
use Kordy\Ticketit\Helpers\LaravelVersion;
use Kordy\Ticketit\Middleware\ResAccessMiddleware as ResAccessMiddleware;

class ResAccessMiddlewareOver extends ResAccessMiddleware
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
        if (Agent::isAdmin()) {
            return $next($request);
        }

        // All Agents have access in none restricted mode
        if (Setting::grab('agent_restrict') == 'no') {
            if (Agent::isAgent()) {
                return $next($request);
            }
        }
        // if this is a ticket show page
        if ($request->route()->getName() == Setting::grab('main_route').'.show') {
            if (LaravelVersion::lt('5.2.0')) {
                $ticket_id = $request->route(Setting::grab('main_route'));
            } else {
                $ticket_id = $request->route('ticket');
            }
        }

        // if this is a new comment on a ticket
        if ($request->route()->getName() == Setting::grab('main_route').'-comment.store') {
            $ticket_id = $request->get('ticket_id');
        }

        // assigned Agent has access in the restricted mode enabled
        if (Agent::isAgent() && Agent::isAssignedAgent($ticket_id)) {
            return $next($request);
        }

        // Ticket Owner has access
        if (Agent::isTicketOwner($ticket_id)) {
            return $next($request);
        }

        return redirect()->route(Setting::grab('main_route').'.index')
            ->with('warning', trans('ticketit::lang.you-are-not-permitted-to-access'));
    }
}
