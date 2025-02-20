<?php

namespace App\Http\TicketitControllers;

use App\Http\Controllers\Controller;
use App\Models\Ticketit\AgentOver as Agent;
//use Kordy\Ticketit\Models\Agent;
use App\Models\Ticketit\TicketOver as Ticket;
use Illuminate\View\View;
//use Kordy\Ticketit\Models\Ticket;
use Kordy\Ticketit\Models\Category;

class DashboardController extends Controller
{
    public function index($indicator_period = 2): View
    {
        $tickets_count = Ticket::count();
        $open_tickets_count = Ticket::whereNull('completed_at')->count();
        $closed_tickets_count = $tickets_count - $open_tickets_count;

        // Per Category pagination
        $categories = Category::paginate(10, ['*'], 'cat_page');

        // Total tickets counter per category for google pie chart
        $categories_all = Category::all();
        $categories_share = [];
        foreach ($categories_all as $cat) {
            $categories_share[$cat->name] = $cat->tickets()->count();
        }

        // Total tickets counter per agent for google pie chart
        $agents_share_obj = Agent::agents()->with(['agentTotalTickets' => function ($query) {
            $query->addSelect(['id', 'agent_id']);
        }])->get();

        $agents_share = [];
        foreach ($agents_share_obj as $agent_share) {
            $agents_share[$agent_share->name] = $agent_share->agentTotalTickets->count();
        }

        // Per Agent
        $agents = Agent::agents(10);

        // Per User
        $users = Agent::users(10);
        //$users = Agent::users()->has('tickets')->get(10);
        //dd($users);

        // Per Category performance data
        $ticketController = new TicketsControllerOver(new Ticket, new Agent);
        $monthly_performance = $ticketController->monthlyPerfomance($indicator_period);

        if (request()->has('cat_page')) {
            $active_tab = 'cat';
        } elseif (request()->has('agents_page')) {
            $active_tab = 'agents';
        } elseif (request()->has('users_page')) {
            $active_tab = 'users';
        } else {
            $active_tab = 'cat';
        }

        return view(
            'ticketit::admin.index',
            compact(
                'open_tickets_count',
                'closed_tickets_count',
                'tickets_count',
                'categories',
                'agents',
                'users',
                'monthly_performance',
                'categories_share',
                'agents_share',
                'active_tab'
            ));
    }
}
