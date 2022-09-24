<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventSession;
use App\Models\Org;
use App\Models\Person;
use App\Models\Registration;
use App\Models\RegSession;
use App\Models\RSSurvey;
use App\Models\Ticket;
use App\Models\Track;
use App\Notifications\SendSurvey;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RegSessionController extends Controller
{
    public function __construct()
    {
        $this->middleware('web', ['except' => ['record_attendance']]);
    }

    public function show(EventSession $session)
    {
        // Called with GET /rs/{session}
        // Given a event's sessionID, display a form for a person to enter their $regID

        $event = Event::find($session->eventID);
        $track = Track::find($session->trackID);
        $org = Org::find($event->orgID);

        return view('v1.public_pages.attend_session', compact('session', 'event', 'track', 'org'));
    }

    public function volunteer_checkin($param, $id = null)
    {
        // Called with GET /checkin/{event}/{session?}
        // Given an event's sessionID, display a form for a person to enter their $regID
        try {
            $event = Event::where('eventID', '=', $param)
                          ->orWhere('slug', '=', $param)
                          ->firstOrFail();
        } catch (\Exception $exception) {
            request()->session()->flash('alert-danger', trans('messages.instructions.no_event'));

            return redirect()->back();
        }

        if ($id !== null) {
            // $session = EventSession::find($event->mainSession);
            $session = EventSession::find($id);
        } else {
            $session = null; // $event->default_session();
        }

        if ($event->hasTracks > 0) {
            $tracks = Track::where([
                ['eventID', '=', $event->eventID],
                ['trackID', '!=', 0],
            ])->get();
        } else {
            $tracks = null;
        }
        $org = Org::find($event->orgID);

        return view('v1.auth_pages.events.checkin_attendee', compact('session', 'event', 'tracks', 'org'));
    }

    public function process_checkin(Request $request)
    {
        // Called as /process_checkin post;  Need to:
        // 1. check if hasTracks > 0 and give options and buttons to re-trigger
        // 2. display the regID request

        $regID = request()->input('regID');
        $sessionID = request()->input('sessionID');
        $eventID = request()->input('eventID');

        // Check if Registration ID entered was valid else redirect back with message
        try {
            $reg = Registration::where([
                ['regID', '=', $regID],
                ['eventID', '=', $eventID],
            ])->first();
            $event = Event::find($reg->eventID);
            $org = Org::find($event->orgID);
            $session = EventSession::find($event->mainSession);
            $person = Person::find($reg->personID);
        } catch (\Exception $exception) {
            request()->session()->flash('alert-danger', trans('messages.messages.bad_regID'));

            return redirect()->back();
        }

        /*
        // Consider deleting as $track doesn't seem to be used
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
        */

        // Check if a reg-session for this regID, eventID, sessionID exists (pre-registered) & update hasAttended if yes
        try {
            $rs = RegSession::where([
                ['regID', '=', $regID],
                ['eventID', '=', request()->input('eventID')],
                ['sessionID', '=', $sessionID],
            ])->first();
            $rs->hasAttended = 1;
            $rs->save();
            request()->session()->flash('alert-success', trans('messages.messages.reg_success', ['name' => $person->showFullName()]));
        } catch (\Exception $exception) {
            // Check if event-session restricts casual switching
            $es = EventSession::find($sessionID);
            if (! $es->isRegSwitchProhibited) {
                $rs = new RegSession;
                $rs->regID = $regID;
                $rs->eventID = request()->input('eventID');
                $rs->sessionID = $sessionID;
                $rs->personID = $person->personID;
                $rs->confDay = $session->confDay;
                $rs->hasAttended = 1;
                $rs->save();
                request()->session()->flash('alert-success', trans('messages.messages.reg_success', ['name' => $person->showFullName()]));
            } else {
                request()->session()->flash('alert-warning', $org->noSwitchTEXT);
            }
        }

        if (request()->input('return')) {
            return redirect('/checkin/'.$eventID.'/'.$sessionID);
        } else {
            return redirect('/checkin/'.$eventID);
        }
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
                $reg->checkin();
                $count++;
            }
        }

        if ($chk) {
            return redirect(env('APP_URL')."/group/$eventID/checkin");
        } else {
            request()->session()->flash('alert-success', trans_choice('messages.headers.count_updated', $count, ['count' => $count]));

            return back()->withInput(['tab' => 'tab_content7']);
        }
    }

    public function update_sessions(Request $request, Registration $reg)
    {
        // Update or create session records, set a display message, and re-display list
        $event = Event::find($reg->eventID);
        $verb = trans('messages.messages.saved');

        // Check if there are any sessions already saved for the person's reg, to decrement EventSession->regCount and delete.
        $rs = RegSession::where([
            ['eventID', '=', $reg->eventID],
            ['regID', '=', $reg->regID],
        ])->get();

        if (count($rs) > 1) {
            $verb = trans('messages.messages.updated');
            foreach ($rs as $s) {
                $e = EventSession::find($s->sessionID);
                if (null !== $e && $e->regCount > 0) {
                    $e->regCount--;
                    $e->save();
                }
                $s->delete();
            }
        }

        for ($j = 1; $j <= $event->confDays; $j++) {
            for ($x = 1; $x <= 5; $x++) {
                $sessionID = null;
                $sessionID = request()->input('sess-'.$j.'-'.$x.'-'.$reg->regID);
                if ($sessionID !== null) {
                    // if this is set, the value is the session that was chosen.

                    $e = EventSession::find($sessionID);
                    if ($e !== null && $e->deleted_at === null) {
                        // increment the attendee count
                        $e->regCount++;
                        $e->save();

                        // record the person's registration for the session
                        $rs = new RegSession;
                        $rs->regID = $reg->regID;
                        $rs->personID = $reg->personID;
                        $rs->eventID = $event->eventID;
                        $rs->confDay = $j;
                        $rs->sessionID = $sessionID;
                        $rs->creatorID = auth()->user()->id;
                        $rs->save();
                    }
                }
            }
        }

        request()->session()->flash('alert-success', trans('messages.messages.sess_saved', ['reg' => $reg->regID, 'verb' => $verb]));

        return redirect('/upcoming');
    }

    public function store_session(Request $request, EventSession $session)
    {
        // 1) Receive the check-in with $regID
        // 2) Update the RegSession instance,
        //    OR create it if it doesn't exist, and
        // 3) Return the individual survey

        $regID = request()->input('regID');

        $rs = RegSession::where([
            ['regID', '=', $regID],
            ['eventID', '=', request()->input('eventID')],
            ['sessionID', '=', $session->sessionID],
        ])->first();

        if (null === $rs) {
            // Create the RegSession
            $reg = Registration::find($regID);
            $rs = new RegSession;
            $rs->regID = $regID;
            $rs->eventID = $session->eventID;
            $rs->sessionID = $session->sessionID;
            $rs->personID = $reg->personID;
            $rs->creatorID = $reg->personID;
            $rs->updaterID = $reg->personID;
        }
        $rs->hasAttended = 1;
        $rs->save();

        return redirect('/rs_survey/'.$rs->id);
    }

    public function show_session_survey(RegSession $rs)
    {
        $event = Event::find($rs->eventID);
        $org = Org::find($event->orgID);
        $session = EventSession::find($rs->sessionID);

        return view('v1.public_pages.session_survey', compact('rs', 'session', 'event', 'org'));
    }

    public function store_survey(Request $request)
    {
        // Response from /rs_survey post

        $wants = request()->input('wantsContact');
        $personID = request()->input('personID');

        $person = Person::find($personID);
        if ($person->prefName) {
            $name = $person->prefName;
        } else {
            $name = $person->firstName;
        }

        $survey = new RSSurvey;
        $survey->regID = request()->input('regID');
        $survey->personID = request()->input('personID');
        $survey->sessionID = request()->input('sessionID');
        $survey->engageResponse = request()->input('engageResponse');
        $survey->takeResponse = request()->input('takeResponse');
        $survey->contentResponse = request()->input('contentResponse');
        $survey->styleResponse = request()->input('styleResponse');
        $survey->favoriteResponse = request()->input('favoriteResponse');
        $survey->suggestResponse = request()->input('suggestResponse');
        $survey->contactResponse = request()->input('contactResponse');
        if ($wants) {
            $survey->wantsContact = 1;
        } else {
            $survey->wantsContact = 0;
        }

        $already_saved = RSSurvey::where([
            ['regID', '=', request()->input('regID')],
            ['sessionID', '=', request()->input('sessionID')],
        ])->get();

        if (count($already_saved) == 0) {
            $survey->save();
        }

        $message = trans('messages.surveys.complete_thanks');

        return view('v1.public_pages.thanks', compact('message'));
    }

    public function send_surveys(Event $event, EventSession $es = null)
    {
        $count = 0;
        $scount = 0;
        if (null === $es) {
            $es = $event->default_session();
        }
        foreach ($event->registrations as $reg) {
            $rs = RegSession::where([
                ['regID', $reg->regID],
                ['personID', $reg->personID],
                ['eventID', $event->eventID],
                ['sessionID', $es->sessionID],
                ['hasAttended', 1],
            ])->first();
            $rss = RSSurvey::where([
                ['regID', $reg->regID],
                ['personID', $reg->personID],
                ['sessionID', $es->sessionID],
            ])->first();

            // YES this person attended the session and NO there is no survey yet
            if (null !== $rs && $rss === null) {
                $p = Person::find($reg->personID);
                $p->notify(new SendSurvey($p, $event, $rs));
                $count++;
            } elseif (null !== $rs) {
                $scount++;
            }
        }

        $event->surveyMailDate = Carbon::now();
        $event->save();

        request()->session()->flash('alert-success', trans_choice('messages.notifications.SS.post_mail_msg', $count, ['count' => $count, 'c2' => $scount]));
        //return redirect(env('APP_URL')."/eventreport/$event->slug");
        return redirect()->back();
    }
}
