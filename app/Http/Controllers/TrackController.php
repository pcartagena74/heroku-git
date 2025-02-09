<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventSession;
use App\Models\Ticket;
use App\Models\Track;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Event $event): View
    {
        $tracks = Track::where('eventID', $event->eventID)->get();
        $spk_list = $event->registered_speakers();

        return view('v1.auth_pages.events.track-session', compact('event', 'tracks', 'spk_list'));
    }

    public function confDaysUpdate(Request $request, Event $event)
    {
        // this function is only to update $event->confDays
        $value = request()->input('value');
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
                            ['eventID', '=', $event->eventID],
                        ])->first();
                        $s->delete();
                    }
                }
            }
        }

        // once set, now populate sessions table with some sessions for each track

        for ($d = $event->confDays; $d <= $value; $d++) {
            foreach ($tracks as $t) {
                for ($i = 1; $i <= 5; $i++) {
                    $s = new EventSession;
                    $s->eventID = $event->eventID;
                    $s->trackID = $t->trackID;
                    $s->confDay = $d;
                    $s->start = $event->eventStartDate->addDays($d - 1);
                    $s->end = $event->eventStartDate->addDays($d - 1);
                    $s->creatorID = auth()->user()->id;
                    $s->order = $i;
                    $s->save();
                }
            }
        }

        // Moved later so that the creation of sessions can take advantage of existing sessions.
        $event->confDays = $value;
        $event->updaterID = auth()->user()->id;
        $event->save();
    }

    public function update(Request $request, Track $track)
    {
        // the name that is passed in is trackName + id and we only change trackName
        $value = request()->input('value');
        $track->trackName = $value;
        $track->updaterID = auth()->user()->id;
        $track->save();
    }

    public function sessionUpdate(Request $request, Event $event)
    {
        $value = request()->input('value');
        if ($value === null) {
            $name = request()->input('name');
            $value = request()->input($name);
        }
        [$name, $track, $day, $order] = array_pad(explode('-', request()->input('name'), 4), 4, null);
        $s = EventSession::withTrashed()->find(request()->input('pk'));

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
        } elseif ($name == 'isLinked') {
            if ($value) {
                $s->isLinked = $s->sessionID;
            } else {
                $s->isLinked = 0;
            }
            $s->updaterID = auth()->user()->id;
            $s->save();

            return redirect(env('APP_URL')."/tracks/$event->eventID");
        } elseif ($name == 'isLinked2') {
            if ($value) {
                $previous = EventSession::where([
                    ['order', $order - 1],
                    ['confDay', '=', $day],
                    ['eventID', $s->eventID],
                    ['trackID', $track],
                ])->withTrashed()->first();
                $s->isLinked = $previous->isLinked;
                $s->start = $previous->start;
                $s->end = $previous->end;
                $s->sessionName = $previous->sessionName;
                $s->ticketID = $previous->ticketID;
            } else {
                $previous = EventSession::where([
                    ['order', $order],
                    ['confDay', '=', $day],
                    ['eventID', $s->eventID],
                ])->withTrashed()->first();
                $s->isLinked = 0;
                $s->start = $previous->start;
                $s->end = $previous->end;
                $s->sessionName = null;
            }
            $s->updaterID = auth()->user()->id;
            $s->save();

            return redirect(env('APP_URL')."/tracks/$event->eventID");
        } elseif ($name == 'sessionSpeakers') {
            // Do stuff for sessionSpeaker assignment
            //dd(request()->all());
            // $v is an array of personIDs
            $s->speakers()->sync($value);
        } else {
            $s->{$name} = $value;
            $s->updaterID = auth()->user()->id;
            $s->save();
        }

        return json_encode(['status' => 'success', 'message' => 'Did something...'.$event->isSymmetric,
            'pk' => request()->input('pk'), 'blah' => $name, 'val' => $value, 's' => $s, ]);
    }

    public function updateSymmetry(Request $request, Event $event): RedirectResponse
    {
        if (request()->input('isSymmetric') === null) {
            $isSymmetric = 0;
        } else {
            $isSymmetric = 1;
        }
        $event->isSymmetric = $isSymmetric;
        $event->updaterID = auth()->user()->id;
        $event->save();

        //return json_encode(array('status' => 'success', 'message' => 'Did something...' . $isSymmetric, 'blah' => $checked));
        return redirect('/tracks/'.$event->eventID);
    }

    public function assignTicketSessions(Request $request, $day)
    {
        // pass the day of the event in the URL and $value = ticketID
        $value = request()->input('value');
        $ticket = Ticket::find($value);
        [$name, $day] = array_pad(explode('-', request()->input('name'), 2), 2, null);
        $sessions = EventSession::where([
            ['confDay', '=', $day],
            ['eventID', '=', $ticket->eventID],
        ])->get();

        foreach ($sessions as $s) {
            $s->ticketID = $ticket->ticketID;
            $s->updaterID = auth()->user()->id;
            $s->save();
        }
    }
}
