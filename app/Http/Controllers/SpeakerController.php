<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Person;
use App\Event;
use App\Registration;
use App\EventSession;

class SpeakerController extends Controller
{
    public function index()
    {
        $speakers = Person::whereHas('roles', function ($q) {
            $q->where('roles.id', '=', '2');
        })
            ->join('event-registration as er', function ($q) {
                $q->on('er.personID', '=', 'person.personID')
                    ->whereNull('er.deleted_at');
            })
            ->join('org-event', 'org-event.eventID', '=', 'er.eventID')
            ->leftjoin('eventsession_speaker as ss', 'ss.speaker_id', '=', 'er.personID')
            ->leftjoin('event-sessions as es', function ($q) {
                $q->on('es.sessionID', '=', 'ss.eventsession_id')
                    ->on('es.eventID', '=', 'org-event.eventID');
            })
            ->where('er.discountCode', '=', 'speaker')
            ->selectRaw("distinct person.personID, person.firstName, person.lastName, person.login, count(*) as 'count'")
            ->groupBy('person.personID', 'person.firstName', 'person.lastName', 'person.login')
            ->get();

        return view('v1.auth_pages.speakers.list', compact('speakers'));
    }

    public function show(Person $speaker)
    {
        $speaker_event_list = Person::whereHas('roles', function ($q) {
            $q->where('roles.id', '=', '2');
        })
            ->join('event-registration as er', function ($q) {
                $q->on('er.personID', '=', 'person.personID')
                    ->whereNull('er.deleted_at');
            })
            ->join('org-event', 'org-event.eventID', '=', 'er.eventID')
            ->leftjoin('eventsession_speaker as ss', 'ss.speaker_id', '=', 'er.personID')
            ->leftjoin('event-sessions as es', function ($q) {
                $q->on('es.sessionID', '=', 'ss.eventsession_id')
                    ->on('es.eventID', '=', 'org-event.eventID');
            })
            ->where([
                ['er.discountCode', '=', 'speaker'],
                ['person.personID', '=', $speaker->personID]
            ])
            ->selectRaw("distinct `org-event`.eventStartDate, `org-event`.eventID, es.sessionID, `org-event`.eventStartDate,
                         concat_ws(': ', `org-event`.eventName, es.sessionName) as 'eventName', person.firstName,
                         person.prefName, person.lastName")
            ->orderBy('eventStartDate', 'desc')
            ->get();

        $html = view('v1.modals.speaker_activity_modal', compact('speaker_event_list'))->render();
        return json_encode(array('html' => $html));
    }
}
