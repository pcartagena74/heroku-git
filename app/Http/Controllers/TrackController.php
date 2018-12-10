<?php

namespace App\Http\Controllers;

use App\EventSession;
use Illuminate\Http\Request;
use App\Event;
use App\Track;
use App\Ticket;

class TrackController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Event $event)
    {
        $tracks = Track::where('eventID', $event->eventID)->get();
        $x = $event->registered_speakers();
        $speakers = [];

        foreach($x as $p){
            array_push($speakers, [$p->showFullName(), $p->personID]);
        }

        return view('v1.auth_pages.events.track-session', compact('event', 'tracks', 'speakers'));
    }

    public function confDaysUpdate(Request $request, Event $event)
    {
        // this function is only to update $event->confDays
        $value  = request()->input('value');
        $tracks = Track::where('eventID', $event->eventID)->get();

        if ($value < $event->confDays) {
            if ($value == 0) {
                $value = 1;
            }
            for ($d = $value; $d <= $event->confDays; $d++) {
                foreach ($tracks as $t) {
                    for ($i = 1; $i <= 5; $i++) {
                        $s = EventSession::where([
                            ['order', '=', $i],
                            ['confDay', '=', $d],
                            ['trackID', '=', $t->trackID],
                            ['eventID', '=', $event->eventID]
                        ])->first();
                        $s->delete();
                    }
                }
            }
        }
        $event->confDays  = $value;
        $event->updaterID = auth()->user()->id;
        $event->save();

        // once set, now populate sessions table with some sessions for each track


        for ($d = 1; $d <= $value; $d++) {
            foreach ($tracks as $t) {
                for ($i = 1; $i <= 5; $i++) {
                    $s            = new EventSession;
                    $s->eventID   = $event->eventID;
                    $s->trackID   = $t->trackID;
                    $s->confDay   = $d;
                    $s->start     = $event->eventStartDate->addDays($d - 1);
                    $s->end       = $event->eventStartDate->addDays($d - 1);
                    $s->creatorID = auth()->user()->id;
                    $s->order     = $i;
                    $s->save();
                }
            }
        }
    }

    public function update(Request $request, Track $track)
    {
        // the name that is passed in is trackName + id and we only change trackName
        $value            = request()->input('value');
        $track->trackName = $value;
        $track->updaterID = auth()->user()->id;
        $track->save();
    }

    public function sessionUpdate(Request $request, Event $event)
    {
        $value = request()->input('value');
        list($name, $track, $day, $order) = array_pad(explode("-", request()->input('name'), 4), 4, null);
        $s = EventSession::find(request()->input('pk'));

        // symmetric schedules need to update start & end together
        // any other variable can be updated as a single update
        if ($name == 'start' || $name == 'end') {
            if ($event->isSymmetric) {
                $sessions = EventSession::where([
                    ['order', $order],
                    ['confDay', '=', $day],
                    ['eventID', $event->eventID],
                ])->get();

                foreach ($sessions as $os) {
                    $os->{$name} = $value;
                    if ($os->end->gt($os->start)) {
                        $os->creditAmt = number_format($os->end->diffInMinutes($os->start) / 60, 2, '.', '');
                    }
                    $os->updaterID = auth()->user()->id;
                    $os->save();
                }
            } else {
                $s->{$name} = $value;
                if ($s->end->gt($s->start)) {
                    $s->creditAmt = number_format($s->end->diffInMinutes($s->start) / 60, 2, '.', '');
                }
                $s->updaterID = auth()->user()->id;
                $s->save();
            }
        } else {
            $s->{$name}   = $value;
            $s->updaterID = auth()->user()->id;
            $s->save();
        }
        return json_encode(array('status' => 'success', 'message' => 'Did something...' . $event->isSymmetric,
            'pk' => request()->input('pk'), 'blah' => $name, 'val' => $value, 's' => $s));
    }

    public function updateSymmetry(Request $request, Event $event)
    {

        if (request()->input('isSymmetric') === null) {
            $isSymmetric = 0;
        } else {
            $isSymmetric = 1;
        }
        $event->isSymmetric = $isSymmetric;
        $event->updaterID   = auth()->user()->id;
        $event->save();
        //return json_encode(array('status' => 'success', 'message' => 'Did something...' . $isSymmetric, 'blah' => $checked));
        return redirect('/tracks/' . $event->eventID);
    }

    public function assignTicketSessions(Request $request, $day)
    {
        // pass the day of the event in the URL and $value = ticketID
        $value  = request()->input('value');
        $ticket = Ticket::find($value);
        list($name, $day) = array_pad(explode("-", request()->input('name'), 2), 2, null);
        $sessions = EventSession::where([
            ['confDay', '=', $day],
            ['eventID', '=', $ticket->eventID]
        ])->get();

        foreach ($sessions as $s) {
            $s->ticketID  = $ticket->ticketID;
            $s->updaterID = auth()->user()->id;
            $s->save();
        }
    }
}
