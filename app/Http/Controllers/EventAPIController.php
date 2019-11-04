<?php

namespace App\Http\Controllers;

use App\Event;
use App\Org;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

class EventAPIController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the events for the specified Organization $orgID
     *
     * @param int $orgID
     * @param int $past
     * @param int $etID
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function show($orgID, $past, $etID = null)
    {
        $org = Org::find($orgID);
        if (null === $org) {
            $message = trans('messages.instructions.no_org');
            return view('v1.public_pages.error_display', compact('message'));
        }

        $override = null;

        // Check to see if $etID is sent as a comma-separated list of etIDs
        if (preg_match('/,/', $etID)) {
            // change value of $etID to be the list of edIDs if it's a list
            $etID_array = explode(",", $etID);
            $tag = DB::table('org-event_types')->whereIn('etID', $etID_array)->pluck('etName')->toArray();
            $tag = array_map("et_translate", $tag);
            $tag = implode(" or ", (array)$tag);

            $events = Event::where([
                ['orgID', $orgID],
                ['isActive', 1],
                ['isPrivate', 0],
            ])
                ->whereIn('eventTypeID', $etID_array)
                ->whereDate('eventStartDate', '>=', Carbon::today()->toDateString())
                ->with('location', 'event_type')
                ->orderBy('eventStartDate')
                ->get();
        } elseif (null !== $etID) {
            $tag = DB::table('org-event_types')->where('etID', $etID)->select('etName')->first();
            $etID_array = explode(",", $etID);
            if (Lang::has('messages.event_types' . $tag->etName)) {
                $tag->etName = trans_choice('messages.event_types.' . $tag->etName, 1);
            }
        } else {
            // a null etID means all event categories
            $etID_array = null;
            $which = trans_choice('messages.var_words.time_period', $past);
            $tag = trans('messages.codes.etID99', ['which' => $which]);
        }

        if (!$past) {
            if ($etID_array) {
                $events = Event::where([
                    ['orgID', $orgID],
                    ['isActive', 1],
                    ['isPrivate', 0],
                ])
                    ->whereIn('eventTypeID', $etID_array)
                    ->whereDate('eventStartDate', '>=', Carbon::today()->toDateString())
                    ->with('location', 'event_type')
                    ->orderBy('eventStartDate')
                    ->get();
            } else {
                $events = Event::where([
                    ['orgID', $orgID],
                    ['isActive', 1],
                    ['isPrivate', 0],
                ])
                    ->whereDate('eventStartDate', '>=', Carbon::today()->toDateString())
                    ->with('location', 'event_type')
                    ->orderBy('eventStartDate')
                    ->get();
            }
        } else {
            if ($etID_array) {
            $events = Event::where([
                ['orgID', $orgID],
                ['eventTypeID', $etID_array],
                ['isPrivate', 0],
            ])
                ->whereDate('eventEndDate', '<=', Carbon::today()->toDateString())
                ->with('location', 'event_type')
                ->orderBy('eventStartDate', 'DESC')
                ->get();
            } else {
                $events = Event::where([
                    ['orgID', $orgID],
                    ['isPrivate', 0],
                ])
                    ->whereDate('eventEndDate', '<=', Carbon::today()->toDateString())
                    ->with('location', 'event_type')
                    ->orderBy('eventStartDate', 'DESC')
                    ->get();
            }
        }

        $cnt = count($events);

        if ($override) {
            $view = view('v1.public_pages.eventlist', compact('events', 'cnt', 'etID', 'org', 'tag'))->render();
            $view = trim(preg_replace('/\r\n/', ' ', $view));

            return json_encode(array('status' => 'success', 'message' => $view));
        } else {
            return view('v1.public_pages.eventlist', compact('events', 'cnt', 'etID', 'org', 'tag', 'past'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Event $event
     * @return \Illuminate\Http\Response
     */
    public
    function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Event $event
     * @return \Illuminate\Http\Response
     */
    public
    function update(Request $request, Event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Event $event
     * @return \Illuminate\Http\Response
     */
    public
    function destroy(Event $event)
    {
        //
    }
}
