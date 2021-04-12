<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventSession;
use App\Models\Org;
use App\Models\Registration;
use App\Models\RegSession;
use App\Models\Track;
use Illuminate\Http\Request;

set_time_limit(0);
ini_set('memory_limit', '-1');

class AuthCheckinController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($param)
    {
        // Response from /record_attendance/{event}
        try {
            $event = Event::when(
                filter_var($param, FILTER_VALIDATE_INT) !== false,
                function ($query) use ($param) {
                    return $query->where('eventID', $param);
                },
                function ($query) use ($param) {
                    return $query->where('slug', $param);
                }
            )->firstOrFail();
        } catch (\Exception $exception) {
            $message = trans('messages.warning.inactive_event_url');

            return view('v1.public_pages.error_display', compact('message'));
        }

        $currentOrg = Org::find($event->orgID);
        $def_sesses = $event->default_sessions();

        if ($event->hasTracks > 0) {
            $tracks = Track::where('eventID', $event->eventID)->get();
        } else {
            $tracks = null;
        }

        return view('v1.auth_pages.events.record_attendance',
                          compact('event', 'def_sesses', 'currentOrg', 'tracks'));
    }

    public function show(EventSession $es)
    {
        $url = 'record_attendance';
        $html = view('v1.parts.session_checkin', compact('es', 'url'))->render();

        return json_encode(['html'=>$html]);
    }

    public function store(Request $request, Event $event)
    {
        // called by POST /event_checkin/{eventID}
        $esID = $request->input('sessionID');
        $chk = $request->input('chk_walk');
        $eventID = $request->input('eventID');
        $id = auth()->user()->id;
        $count = 0;

        // First, delete all RegSession records that were saved
        $old_regs = RegSession::where([
            ['eventID', '=', $event->eventID],
            ['sessionID', '=', $esID],
        ])->get();
        foreach ($old_regs as $o) {
            $o->hasAttended = 0;
            $o->save();
        }

        // Then, cycle through all p-#-# registrants to enter record
        foreach ($request->all() as $key => $value) {
            if (preg_match('/^p-/', $key)) {
                list($field, $personID, $regID) = array_pad(explode('-', $key, 3), 3, null);
                $reg = Registration::find($regID);
                $reg->checkin($esID);
                $count++;
            }
        }
        request()->session()->flash('alert-success', trans_choice('messages.headers.count_updated', $count, ['count' => $count]));

        return redirect(env('APP_URL')."/record_attendance/$event->slug/");
    }
}
