<?php

namespace App\Http\Controllers;

use App\Org;
use App\RSSurvey;
use Illuminate\Http\Request;
use App\RegSession;
use App\EventSession;
use App\Event;
use App\Track;
use App\Person;

class RegSessionController extends Controller
{
    public function __construct () {
        //$this->middleware('auth');
        //$this->middleware('tidy')->only('update');
    }

    public function show (EventSession $session) {
        // Called with /rs/{session}
        // Given a event's sessionID, display a form for a person to enter their $regID

        $event = Event::find($session->eventID);
        $track = Track::find($session->trackID);
        $org   = Org::find($event->orgID);

        return view('v1.public_pages.attend_session', compact('session', 'event', 'track', 'org'));
    }

    public function update (Request $request, EventSession $session) {
        // 1) Receive the check-in with $regID
        // 2) Update the RegSession instance, and
        // 3) Return EITHER the same form or the individual survey

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

    public function show_session_survey (RegSession $rs) {
        $event   = Event::find($rs->eventID);
        $org     = Org::find($event->orgID);
        $session = EventSession::find($rs->sessionID);

        return view('v1.public_pages.session_survey', compact('rs', 'session', 'event', 'org'));
    }

    public function store (Request $request) {
        // Response from /rs_survey post

        $wants = request()->input('wantsContact');
        $personID = request()->input('personID');

        $person = Person::find($personID);
        if($person->prefName){
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
        if($wants) {
            $survey->wantsContact = 1;
        } else {
            $survey->wantsContact = 0;
        }

        $already_saved = RSSurvey::where([
            ['regID', '=', request()->input('regID')],
            ['sessionID', '=', request()->input('sessionID')]
        ])->get();

        if(!$already_saved){
            $survey->save();
        }

        $message = "Thank you for providing session feedback, $name.";
        return view('v1.public_pages.thanks', compact('message'));
    }

}
