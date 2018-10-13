<?php

namespace App\Http\Controllers;

use App\Org;
use App\Registration;
use App\RSSurvey;
use Illuminate\Http\Request;
use App\RegSession;
use App\EventSession;
use App\Event;
use App\Track;
use App\Person;
use App\Ticket;

class RegSessionController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
        //$this->middleware('tidy')->only('update');
    }

    public function show(EventSession $session)
    {
        // Called with /rs/{session}
        // Given a event's sessionID, display a form for a person to enter their $regID

        $event = Event::find($session->eventID);
        $track = Track::find($session->trackID);
        $org   = Org::find($event->orgID);

        return view('v1.public_pages.attend_session', compact('session', 'event', 'track', 'org'));
    }

    public function volunteer_checkin($param, $s = null)
    {
        // Called with /checkin/{event}
        // Given an event's sessionID, display a form for a person to enter their $regID
        try {
            $event = Event::where('eventID', '=', $param)
                          ->orWhere('slug', '=', $param)
                          ->firstOrFail();
        } catch (\Exception $exception) {
            request()->session()->flash('alert-danger', "Unable to find event.");
            return redirect()->back();
        }

        if ($s === null) {
            $session = EventSession::find($event->mainSession);
        } else {
            $session = EventSession::find($s);
        }

        if ($event->hasTracks > 0) {
            $track = Track::where([
                ['eventID', '=', $event->eventID],
                ['trackID', '!=', 0]
            ])->get();
        } elseif ($session->trackID == 0) {
            $track = Track::find($session->trackID);
        } else {
            $track = 0;
        }
        $org = Org::find($event->orgID);

        return view('v1.auth_pages.events.checkin_attendee', compact('session', 'event', 'track', 'org'));
    }

    public function process_checkin(Request $request)
    {
//dd(request()->all());
        // Called as /process_checkin post;  Need to:
        // 1. check if hasTracks > 0 and give options and buttons to re-trigger
        // 2. display the regID request

        $regID     = request()->input('regID');
        $sessionID = request()->input('sessionID');
        $eventID   = request()->input('eventID');

        // Check if Registration ID entered was invalid and redirect back with message
        try {
            $reg       = Registration::find($regID);
            $event     = Event::find($eventID);
            $org       = Org::find($event->orgID);
            $session   = EventSession::find($event->mainSession);
            $person    = Person::find($reg->personID);
        } catch (\Exception $exception) {
            request()->session()->flash('alert-danger', "Invalid registration ID used.");
            return redirect()->back();
        }

        if ($event->hasTracks > 0) {
            $track = Track::where([
                ['eventID', '=', $event->eventID],
                ['trackID', '!=', 0]
            ])->get();
        } elseif ($session->trackID == 0) {
            $track = Track::find($session->trackID);
        } else {
            $track = 0;
        }

        try {
            $rs              = RegSession::where([
                ['regID', '=', $regID],
                ['eventID', '=', request()->input('eventID')],
                ['sessionID', '=', $sessionID]
            ])->first();
            $rs->hasAttended = 1;
            $rs->save();
        } catch (\Exception $exception) {
            $rs = new RegSession;
            $rs->regID = $regID;
            $rs->eventID = request()->input('eventID');
            $rs->sessionID = $sessionID;
            $rs->personID = $person->personID;
            $rs->confDay = $session->confDay;
            $rs->hasAttended = 1;
            $rs->save();
        }

        request()->session()->flash('alert-success', $person->firstName . " " . $person->lastName . " was successfully registered.");
        //return view('v1.auth_pages.events.checkin_attendee', compact('event', 'session', 'org', 'track'));

        if (request()->input('return')) {
            return redirect('/checkin/' . $eventID . '/' . $sessionID);
        } else {
            return redirect('/checkin/' . $eventID);
        }
    }

    public function update_sessions(Request $request, Registration $reg)
    {
        // Update or create session records, set a display message, and re-display list
        $event = Event::find($reg->eventID);
        $verb = 'saved';

        // Check if there are any sessions already saved, so you can decrement EventSession->regCount and delete.
        $rs = RegSession::where([
            ['eventID', '=', $reg->eventID],
            ['regID', '=', $reg->regID]
        ])->get();

        if (count($rs)>1) {
            $verb = 'updated';
            foreach ($rs as $s) {
                $e = EventSession::find($s->sessionID);
                if ($e->regCount > 0) {
                    $e->regCount--;
                } $e->save();
                $s->delete();
            }
        }

        for ($j = 1; $j <= $event->confDays; $j++) {
            $z = EventSession::where([
                ['confDay', '=', $j],
                ['eventID', '=', $event->eventID]
            ])->first();
            $y = Ticket::find($z->ticketID);

            for ($x = 1; $x <= 5; $x++) {
                if (request()->input('sess-' . $j . '-' . $x . '-' . $reg->regID)) {
                    // if this is set, the value is the session that was chosen.
                    $rs            = new RegSession;
                    $rs->regID     = $reg->regID;
                    $rs->personID  = $reg->personID;
                    $rs->eventID   = $event->eventID;
                    $rs->confDay   = $j;
                    $rs->sessionID = request()->input('sess-' . $j . '-' . $x . '-' . $reg->regID);
                    $rs->creatorID = auth()->user()->id;
                    $rs->save();

                    $e = EventSession::find($rs->sessionID);
                    $e->regCount++;
                    $e->save();
                }
            }
        }

        request()->session()->flash('alert-success', "Your session choices for " . $reg->regID . " were " . $verb . ".");
        return redirect('/upcoming');
    }

    public function update(Request $request, EventSession $session)
    {
        // 1) Receive the check-in with $regID
        // 2) Update the RegSession instance, and
        // 3) Return the individual survey

        $regID = request()->input('regID');

        $rs              = RegSession::where([
            ['regID', '=', $regID],
            ['eventID', '=', request()->input('eventID')],
            ['sessionID', '=', $session->sessionID]
        ])->first();
        $rs->hasAttended = 1;
        $rs->save();

        return redirect('/rs_survey/' . $rs->id);
    }

    public function show_session_survey(RegSession $rs)
    {
        $event   = Event::find($rs->eventID);
        $org     = Org::find($event->orgID);
        $session = EventSession::find($rs->sessionID);

        return view('v1.public_pages.session_survey', compact('rs', 'session', 'event', 'org'));
    }

    public function store(Request $request)
    {
        // Response from /rs_survey post

        $wants    = request()->input('wantsContact');
        $personID = request()->input('personID');

        $person = Person::find($personID);
        if ($person->prefName) {
            $name = $person->prefName;
        } else {
            $name = $person->firstName;
        }

        $survey                   = new RSSurvey;
        $survey->regID            = request()->input('regID');
        $survey->personID         = request()->input('personID');
        $survey->sessionID        = request()->input('sessionID');
        $survey->engageResponse   = request()->input('engageResponse');
        $survey->takeResponse     = request()->input('takeResponse');
        $survey->contentResponse  = request()->input('contentResponse');
        $survey->styleResponse    = request()->input('styleResponse');
        $survey->favoriteResponse = request()->input('favoriteResponse');
        $survey->suggestResponse  = request()->input('suggestResponse');
        $survey->contactResponse  = request()->input('contactResponse');
        if ($wants) {
            $survey->wantsContact = 1;
        } else {
            $survey->wantsContact = 0;
        }

        $already_saved = RSSurvey::where([
            ['regID', '=', request()->input('regID')],
            ['sessionID', '=', request()->input('sessionID')]
        ])->get();

        if (!$already_saved) {
            $survey->save();
        }

        $message = "Thank you for providing session feedback, $name.";
        return view('v1.public_pages.thanks', compact('message'));
    }
}
