<?php

namespace App\Http\Controllers;

use App\Event;
use App\Person;
use App\RegFinance;
use App\Registration;
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

        $simple = Event::where([
            ['orgID', $this->currentPerson->defaultOrgID]
        ])
            ->select('eventName', 'eventStartDate')
            ->withCount(['registrations'])
            ->withCount([
                'regfinances AS cost_sum' => function ($query) {
                    $query->select(DB::raw("COALESCE(SUM(cost),0) as costsum"));
                }
            ])
            ->withCount([
                'regfinances AS handle_sum' => function ($query) {
                    $query->select(DB::raw("COALESCE(SUM(handleFee),0) as handlesum"));
                }
            ])
            ->withCount([
                'regfinances AS ccfee_sum' => function ($query) {
                    $query->select(DB::raw("COALESCE(SUM(ccFee),0) as ccsum"));
                }
            ])
            ->orderBy('eventStartDate')
            ->get();

        $simple_header = [
            implode(" ", [trans('messages.fields.event'), trans('messages.fields.name')]),
            implode(" ", [trans('messages.headers.start'), trans_choice('messages.headers.date', 1)]),
            trans_choice('messages.headers.regs', 2), trans('messages.headers.cost'),
            trans('messages.headers.handling'), trans('messages.headers.ccfee')
        ];

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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        //
    }
}