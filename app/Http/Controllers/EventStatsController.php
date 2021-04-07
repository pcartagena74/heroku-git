<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Person;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventStatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->currentPerson = Person::find(auth()->user()->id);
        $today = Carbon::now();

        // This was going to be alternate code for Developer permissions to edit a to-be-created column
        // to record whether an event was paid.

        if (\Entrust::hasRole('Developer') || 1) {
            $simple = Event::where([
                ['org-event.orgID', $this->currentPerson->defaultOrgID],
                ['eventStartDate', '<', $today],
                ['isPrivate', 0],
            ])
                ->leftJoin('org-event_types as et', function ($q) {
                    $q->on('et.etID', '=', 'org-event.eventTypeID');
                })
                ->select('eventName', 'eventStartDate', 'et.etName')
                ->withCount(['registrations'])
                ->withCount(['regsessions'])
                ->withCount(['surveys'])
                ->withCount([
                    'regfinances AS fee_sum' => function ($query) {
                        $query->select(DB::raw('format(COALESCE(SUM(handleFee + ccFee),0), 2) as handlesum'));
                    },
                ])
                ->withCount([
                    'regfinances AS net_sum' => function ($query) {
                        $query->select(DB::raw('format(COALESCE(SUM(cost - handleFee - ccFee),0), 2) as netsum'));
                    },
                ])
                ->orderBy('eventStartDate', 'DESC')
                ->get();

            $simple_header = [
                implode(' ', [trans('messages.fields.event'), trans('messages.fields.name')]),
                implode(' ', [trans('messages.headers.start'), trans_choice('messages.headers.date', 1)]),
                trans_choice('messages.headers.et', 1), trans_choice('messages.headers.regs', 2),
                trans_choice('messages.headers.att', 2), trans('messages.surveys.surveys'),
                trans('messages.headers.handling'), trans('messages.headers.net_rev'),
            ];
        }

        return view('v1.auth_pages.events.eventstats', compact('simple', 'simple_header'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Event $event
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Event $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Event $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Event $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        //
    }
}
