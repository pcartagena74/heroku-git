<?php

namespace App\Http\TicketitControllers;

use App\Models\Person;
use App\Models\Ticketit\AgentOver as Agent;
use App\Models\Ticketit\TicketOver as Ticket;
use Cache;
use Carbon\Carbon;
use Entrust;
use Illuminate\Http\Request;
use Kordy\Ticketit\Controllers\TicketsController as TicketController;
use Kordy\Ticketit\Helpers\LaravelVersion;
use Kordy\Ticketit\Models;
use Kordy\Ticketit\Models\Category;
use Kordy\Ticketit\Models\Setting;
use Validator;

class TicketsControllerOver extends TicketController
{
    protected $tickets;
    protected $agent;
    protected $currentPerson;

    public function __construct(Ticket $tickets, Agent $agent)
    {
        $this->middleware(\App\Http\Middleware\Ticketit\ResAccessMiddlewareOver::class, ['only' => ['show']]);
        $this->middleware(\App\Http\Middleware\Ticketit\IsAgentMiddlewareOver::class, ['only' => ['edit', 'update']]);
        $this->middleware(\App\Http\Middleware\Ticketit\IsAdminMiddlewareOver::class, ['only' => ['destroy']]);

        $this->tickets = $tickets;
        $this->agent = $agent;
    }

    public function data($complete = false)
    {
        if (LaravelVersion::min('5.4')) {
            $datatables = app(\Yajra\DataTables\DataTables::class);
        } else {
            $datatables = app(\Yajra\Datatables\Datatables::class);
        }

        $user = $this->agent->find(auth()->user()->id);
        $person = Person::find(auth()->user()->id);
        $orgId = $person->defaultOrgID;
        if ($user->isAdmin()) {
            if ($complete) {
                $collection = Ticket::complete($orgId);
            } else {
                $collection = Ticket::active($orgId);
            }
        } elseif ($user->isAgent()) {
            if ($complete) {
                $collection = Ticket::complete($orgId)->agentUserTickets($user->id);
            } else {
                $collection = Ticket::active($orgId)->agentUserTickets($user->id);
            }
        } else {
            if ($complete) {
                $collection = Ticket::userTickets($user->id)->complete($orgId);
            } else {
                $collection = Ticket::userTickets($user->id)->active($orgId);
            }
        }

        // if ($complete == 'my-ticket') {
        //     $collection = Ticket::userTickets($user->id)->active($orgId);
        // }
        $collection
            ->join('users', 'users.id', '=', 'ticketit.user_id')
            ->join('ticketit_statuses', 'ticketit_statuses.id', '=', 'ticketit.status_id')
            ->join('ticketit_priorities', 'ticketit_priorities.id', '=', 'ticketit.priority_id')
            ->join('ticketit_categories', 'ticketit_categories.id', '=', 'ticketit.category_id')
            ->select([
                'ticketit.id',
                'ticketit.subject AS subject',
                'ticketit_statuses.name AS status',
                'ticketit_statuses.color AS color_status',
                'ticketit_priorities.color AS color_priority',
                'ticketit_categories.color AS color_category',
                'ticketit.id AS agent',
                'ticketit.updated_at AS updated_at',
                'ticketit_priorities.name AS priority',
                'users.name AS owner',
                'ticketit.agent_id',
                'ticketit_categories.name AS category',
                'ticketit.agent_id AS agent_id',
                'ticketit.user_id AS user_id',
            ]);
        $collection = $datatables->of($collection);

        $this->renderTicketTable($collection);

        $collection->editColumn('updated_at', '{!! \Carbon\Carbon::createFromFormat("Y-m-d H:i:s", $updated_at)->diffForHumans() !!}');

        // method rawColumns was introduced in laravel-datatables 7, which is only compatible with >L5.4
        // in previous laravel-datatables versions escaping columns wasn't defaut
        if (LaravelVersion::min('5.4')) {
            $collection->rawColumns(['subject', 'status', 'priority', 'category', 'agent', 'agent_id', 'user_id']);
        }

        return $collection->make(true);
    }

    public function renderTicketTable($collection)
    {
        $collection->editColumn('subject', function ($ticket) {
            return (string) link_to_route(
                Setting::grab('main_route').'.show',
                $ticket->subject,
                $ticket->id
            );
        });

        $collection->editColumn('status', function ($ticket) {
            $color = $ticket->color_status;
            $status = e($ticket->status);

            return "<div style='color: $color'>$status</div>";
        });

        $collection->editColumn('priority', function ($ticket) {
            $color = $ticket->color_priority;
            $priority = e($ticket->priority);

            return "<div style='color: $color'>$priority</div>";
        });

        $collection->editColumn('category', function ($ticket) {
            $color = $ticket->color_category;
            $category = e($ticket->category);

            return "<div style='color: $color'>$category</div>";
        });

        $collection->editColumn('agent', function ($ticket) {
            // $ticket = $this->tickets->find($ticket->id);
            // return e($ticket->agent->name);
            // removed as now we are not using ticketit agent table for same.
            $ticket = Person::where('personID', $ticket->agent_id)->get()->first();

            return e($ticket->login);
        });

        // added by mufaddal for filter
        $collection->editColumn('agent_id', function ($ticket) {
            $ticket = Person::where('personID', $ticket->agent_id)->get()->first();

            return e($ticket->login);
        });
        $collection->editColumn('user_id', function ($ticket) {
            $ticket = Person::where('personID', $ticket->user_id)->get()->first();

            return e($ticket->login);
        });

        return $collection;
    }

    /**
     * Display a listing of active tickets related to user.
     *
     * @return Response
     */
    public function index()
    {
        $complete = false;

        return view('ticketit::index', compact('complete'));
    }

    /**
     * Display a listing of completed tickets related to user.
     *
     * @return Response
     */
    public function indexComplete()
    {
        $complete = true;

        return view('ticketit::index', compact('complete'));
    }

    /**
     * Returns priorities, categories and statuses lists in this order
     * Decouple it with list().
     *
     * @return array
     */
    public function PCS()
    {
        // seconds expected for L5.8<=, minutes before that
        $time = LaravelVersion::min('5.8') ? 60 * 60 : 60;

        $priorities = Cache::remember('ticketit::priorities', $time, function () {
            return Models\Priority::all();
        });

        $categories = Cache::remember('ticketit::categories', $time, function () {
            return Models\Category::all();
        });

        $statuses = Cache::remember('ticketit::statuses', $time, function () {
            return Models\Status::all();
        });

        if (LaravelVersion::min('5.3.0')) {
            return [$priorities->pluck('name', 'id'), $categories->pluck('name', 'id'), $statuses->pluck('name', 'id')];
        } else {
            return [$priorities->lists('name', 'id'), $categories->lists('name', 'id'), $statuses->lists('name', 'id')];
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        list($priorities, $categories) = $this->PCS();

        return view('ticketit::tickets.create', compact('priorities', 'categories'));
    }

    /**
     * Store a newly created ticket and auto assign an agent for it.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'subject'     => 'required|min:3',
            'content'     => 'required|min:6',
            'priority_id' => 'required|exists:ticketit_priorities,id',
            'category_id' => 'required|exists:ticketit_categories,id',
        ]);

        $ticket = new Ticket();

        $ticket->subject = $request->subject;

        $ticket->setPurifiedContent($request->get('content'));

        $ticket->priority_id = $request->priority_id;
        $ticket->category_id = $request->category_id;

        $ticket->status_id = Setting::grab('default_status_id');
        $ticket->user_id = auth()->user()->id;
        $person = Person::find(auth()->user()->id);
        $ticket->orgId = $person->defaultOrgID;

        $ticket->autoSelectAgent();

        $ticket->save();

        session()->flash('status', trans('ticketit::lang.the-ticket-has-been-created'));

        // return redirect()->action('\App\Http\TicketitControllers\TicketsControllerOver@index');
        return redirect()->route(Setting::grab('main_route').'.index');
    }

    /**
     * store new ticket via ajax agent auto assigned
     * @param  Request $request
     * @return json
     */
    public function storeAjax(Request $request)
    {
        if (Agent::isAdmin() || Agent::isAgent()) {
            $validator = Validator::make($request->all(), [
                'subject'     => 'required|min:3',
                'content'     => 'required|min:6',
                'priority_id' => 'required|exists:ticketit_priorities,id',
                'category_id' => 'required|exists:ticketit_categories,id',
                'url'         => 'required',
            ]);
            if (! $validator->passes()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()]);
            }
        } else {
            $validator = Validator::make($request->all(), [
                'subject' => 'required|min:3',
                'content' => 'required|min:6',
                'url'     => 'required',
            ]);
            if (! $validator->passes()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()]);
            }
        }

        $ticket = new Ticket();
        $page_url = $request->input('url');
        $referrer = $request->headers->get('referer');
        if ($referrer != $request->input('url')) {
            $page_url = $referrer;
        }
        $ticket->subject = $request->subject;

        $ticket->setPurifiedContent($request->get('content').' <br> URL: '.$referrer);

        $ticket->category_id = 4;
        $ticket->priority_id = 2;

        if (! empty($request->input('category_id'))) {
            $ticket->category_id = $request->input('category_id');
        }
        if (! empty($request->input('priority_id'))) {
            $ticket->priority_id = $request->input('priority_id');
        }

        $ticket->status_id = Setting::grab('default_status_id');
        $ticket->user_id = auth()->user()->id;
        $person = Person::find(auth()->user()->id);
        $ticket->orgId = $person->defaultOrgID;

        if (empty($request->input('agent_id'))) {
            $ticket->autoSelectAgent();
        } elseif ($request->input('agent_id') == 'auto') {
            $ticket->autoSelectAgent();
        } elseif ($request->input('agent_id') == 'auto_dev') {
            $ticket->autoSelectAgent('dev');
        } else {
            $ticket->agent_id = $request->input('agent_id');
        }

        $ticket->save();

        // session()->flash('status', trans('ticketit::lang.the-ticket-has-been-created'));
        return response()->json(['success' => true, 'message' => trans('ticketit::lang.the-ticket-has-been-created')]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $ticket = $this->tickets->findOrFail($id);

        list($priority_lists, $category_lists, $status_lists) = $this->PCS();

        $close_perm = $this->permToClose($id);
        $reopen_perm = $this->permToReopen($id);

        //removing category agent as it envolves changing laravel default add role remove role to add remove entires from tickeit it as well and will have same effect without it.
        // $cat_agents = Models\Category::find($ticket->category_id)->agents()->agentsLists();
        $agent_lists = getAgentList($ticket);
        $comments = $ticket->comments()->paginate(Setting::grab('paginate_items'));
        if (Entrust::hasRole('Admin') || Entrust::hasRole('Developer')) {
            $ticket->agent_read = 1;
            $ticket->save();
        }
        if (auth()->user()->id == $ticket->user_id) {
            $ticket->user_read = 1;
            $ticket->save();
        }

        return view('ticketit::tickets.show',
            compact('ticket', 'status_lists', 'priority_lists', 'category_lists', 'agent_lists', 'comments',
                'close_perm', 'reopen_perm'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'subject'     => 'required|min:3',
            'content'     => 'required|min:6',
            'priority_id' => 'required|exists:ticketit_priorities,id',
            'category_id' => 'required|exists:ticketit_categories,id',
            'status_id'   => 'required|exists:ticketit_statuses,id',
            'agent_id'    => 'required',
        ]);

        $ticket = $this->tickets->findOrFail($id);

        $ticket->subject = $request->subject;

        $ticket->setPurifiedContent($request->get('content'));

        $ticket->status_id = $request->status_id;
        $ticket->category_id = $request->category_id;
        $ticket->priority_id = $request->priority_id;

        if ($request->input('agent_id') == 'auto') {
            $ticket->autoSelectAgent();
        } elseif ($request->input('agent_id') == 'auto_dev') {
            $ticket->autoSelectAgent('dev');
        } else {
            $ticket->agent_id = $request->input('agent_id');
        }
        $ticket->user_read = 0;
        if ($ticket->agent_id != auth()->user()->id) {
            $ticket->agent_read = 0;
        }
        $ticket->save();

        session()->flash('status', trans('ticketit::lang.the-ticket-has-been-modified'));

        return redirect()->route(Setting::grab('main_route').'.show', $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $ticket = $this->tickets->findOrFail($id);
        $subject = $ticket->subject;
        $ticket->delete();

        session()->flash('status', trans('ticketit::lang.the-ticket-has-been-deleted', ['name' => $subject]));

        return redirect()->route(Setting::grab('main_route').'.index');
    }

    /**
     * Mark ticket as complete.
     *
     * @param int $id
     *
     * @return Response
     */
    public function complete($id)
    {
        if ($this->permToClose($id) == 'yes') {
            $ticket = $this->tickets->findOrFail($id);
            $ticket->completed_at = Carbon::now();

            if (Setting::grab('default_close_status_id')) {
                $ticket->status_id = Setting::grab('default_close_status_id');
            }

            $subject = $ticket->subject;
            $ticket->save();

            session()->flash('status', trans('ticketit::lang.the-ticket-has-been-completed', ['name' => $subject]));

            return redirect()->route(Setting::grab('main_route').'.index');
        }

        return redirect()->route(Setting::grab('main_route').'.index')
            ->with('warning', trans('ticketit::lang.you-are-not-permitted-to-do-this'));
    }

    /**
     * Reopen ticket from complete status.
     *
     * @param int $id
     *
     * @return Response
     */
    public function reopen($id)
    {
        if ($this->permToReopen($id) == 'yes') {
            $ticket = $this->tickets->findOrFail($id);
            $ticket->completed_at = null;

            if (Setting::grab('default_reopen_status_id')) {
                $ticket->status_id = Setting::grab('default_reopen_status_id');
            }

            $subject = $ticket->subject;
            if ($ticket->user_id != auth()->user()->id) {
                $ticket->user_read = 0;
            }
            if ($ticket->agent_id != auth()->user()->id) {
                $ticket->agent_read = 0;
            }

            $ticket->save();

            session()->flash('status', trans('ticketit::lang.the-ticket-has-been-reopened', ['name' => $subject]));

            return redirect()->route(Setting::grab('main_route').'.index');
        }

        return redirect()->route(Setting::grab('main_route').'.index')
            ->with('warning', trans('ticketit::lang.you-are-not-permitted-to-do-this'));
    }

    public function agentSelectList($category_id, $ticket_id)
    {
        // $cat_agents = Models\Category::find($category_id)->agents()->agentsLists();
        // if (is_array($cat_agents)) {
        //     $agents = ['auto' => 'Auto Select'] + $cat_agents;
        // } else {
        //     $agents = ['auto' => 'Auto Select'];
        // }
        $ticket = $this->tickets->find($ticket_id);
        $agents = getAgentList($ticket);
        $selected_Agent = $ticket->agent->id;
        $select = '<select class="form-control" id="agent_id" name="agent_id">';
        foreach ($agents as $id => $name) {
            $selected = ($id == $selected_Agent) ? 'selected' : '';
            $select .= '<option value="'.$id.'" '.$selected.'>'.$name.'</option>';
        }
        $select .= '</select>';

        return $select;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function permToClose($id)
    {
        $close_ticket_perm = Setting::grab('close_ticket_perm');

        if ($this->agent->isAdmin() && $close_ticket_perm['admin'] == 'yes') {
            return 'yes';
        }
        if ($this->agent->isAgent() && $close_ticket_perm['agent'] == 'yes') {
            return 'yes';
        }
        if ($this->agent->isTicketOwner($id) && $close_ticket_perm['owner'] == 'yes') {
            return 'yes';
        }

        return 'no';
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function permToReopen($id)
    {
        $reopen_ticket_perm = Setting::grab('reopen_ticket_perm');
        if ($this->agent->isAdmin() && $reopen_ticket_perm['admin'] == 'yes') {
            return 'yes';
        } elseif ($this->agent->isAgent() && $reopen_ticket_perm['agent'] == 'yes') {
            return 'yes';
        } elseif ($this->agent->isTicketOwner($id) && $reopen_ticket_perm['owner'] == 'yes') {
            return 'yes';
        }

        return 'no';
    }

    /**
     * Calculate average closing period of days per category for number of months.
     *
     * @param int $period
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function monthlyPerfomance($period = 2)
    {
        $categories = Category::all();
        foreach ($categories as $cat) {
            $records['categories'][] = $cat->name;
        }

        for ($m = $period; $m >= 0; $m--) {
            $from = Carbon::now();
            $from->day = 1;
            $from->subMonth($m);
            $to = Carbon::now();
            $to->day = 1;
            $to->subMonth($m);
            $to->endOfMonth();
            $records['interval'][$from->format('F Y')] = [];
            foreach ($categories as $cat) {
                $records['interval'][$from->format('F Y')][] = round($this->intervalPerformance($from, $to, $cat->id), 1);
            }
        }

        return $records;
    }

    /**
     * Calculate the date length it took to solve a ticket.
     *
     * @param Ticket $ticket
     *
     * @return int|false
     */
    public function ticketPerformance($ticket)
    {
        if ($ticket->completed_at == null) {
            return false;
        }

        $created = new Carbon($ticket->created_at);
        $completed = new Carbon($ticket->completed_at);
        $length = $created->diff($completed)->days;

        return $length;
    }

    /**
     * Calculate the average date length it took to solve tickets within date period.
     *
     * @param $from
     * @param $to
     *
     * @return int
     */
    public function intervalPerformance($from, $to, $cat_id = false)
    {
        if ($cat_id) {
            $tickets = Ticket::where('category_id', $cat_id)->whereBetween('completed_at', [$from, $to])->get();
        } else {
            $tickets = Ticket::whereBetween('completed_at', [$from, $to])->get();
        }

        if (empty($tickets->first())) {
            return false;
        }

        $performance_count = 0;
        $counter = 0;
        foreach ($tickets as $ticket) {
            $performance_count += $this->ticketPerformance($ticket);
            $counter++;
        }
        $performance_average = $performance_count / $counter;

        return $performance_average;
    }

    public function myTickets(Request $request)
    {
        $complete = 'my-ticket';

        return view('ticketit::tickets.my_tickets', compact('complete'));
    }
}
