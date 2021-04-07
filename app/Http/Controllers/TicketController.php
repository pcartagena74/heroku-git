<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventSession;
use App\Person;
use App\Registration;
use App\RegSession;
use App\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // responds to /blah
    }

    public function show($id)
    {
        // responds to GET /blah/id
        $event = Event::find($id);
        $topBits = '';

        $bundles = Ticket::where([
                ['isaBundle', 1],
                ['eventID', $event->eventID],
            ])->get()->sortByDesc('availableEndDate');

        $tickets = Ticket::where([
            ['isaBundle', 0],
            ['eventID', $event->eventID],
        ])->get()->sortByDesc('availableEndDate');

        return view('v1.auth_pages.events.list-tickets', compact('event', 'bundles', 'tickets', 'topBits'));
    }

    public function create()
    {
        // responds to /blah/create and shows add/edit form
    }

    public function store(Request $request)
    {
        // responds to POST to /blah and creates, adds, stores the event
        //dd(request()->all());
        $eventID = request()->input('eventID');
        $this->currentPerson = Person::find(auth()->user()->id);
        $event = Event::find($eventID);

        for ($i = 1; $i <= 5; $i++) {
            $ticketLabel = 'ticketLabel-'.$i;
            $availabilityEndDate = 'availabilityEndDate-'.$i;
            $earlyBirdEndDate = 'earlyBirdEndDate-'.$i;
            $memberBasePrice = 'memberBasePrice-'.$i;
            $nonmbrBasePrice = 'nonmbrBasePrice-'.$i;
            $maxAttendees = 'maxAttendees-'.$i;
            $isaBundle = 'isaBundle-'.$i;
            $earlyBirdPercent = 'earlyBirdPercent-'.$i;

            $tkl = request()->input($ticketLabel);
            $ava = request()->input($availabilityEndDate);
            $ear = request()->input($earlyBirdEndDate);
            $mbr = request()->input($memberBasePrice);
            $non = request()->input($nonmbrBasePrice);
            $max = request()->input($maxAttendees);
            $ebp = request()->input($earlyBirdPercent);
            null !== request()->input($isaBundle) ? $isa = request()->input($isaBundle) : $isa = 0;

            empty($ava) ? $ava = null : $ava = date('Y-m-d H:i:s', strtotime($ava));
            empty($ear) ? $ear = null : $ear = date('Y-m-d H:i:s', strtotime($ear));

            if (! empty($tkl)) {
                $newtkt = new Ticket;
                $newtkt->ticketLabel = $tkl;
                $newtkt->availabilityEndDate = $ava;
                $newtkt->earlyBirdEndDate = $ear;
                $newtkt->memberBasePrice = $mbr;
                $newtkt->nonmbrBasePrice = $non;
                $newtkt->isaBundle = $isa;
                $newtkt->eventID = $eventID;
                $newtkt->maxAttendees = $max;
                $newtkt->earlyBirdPercent = $ebp;
                $newtkt->creatorID = $this->currentPerson->personID;
                $newtkt->updaterID = $this->currentPerson->personID;
                $newtkt->save();

                // Create a mainSession for the default ticket for the event
                $def_sess = new EventSession;
                $def_sess->trackID = 0;
                $def_sess->eventID = $event->eventID;
                $def_sess->ticketID = $newtkt->ticketID;
                $def_sess->sessionName = 'def_sess';
                $def_sess->confDay = 0;
                $def_sess->start = $event->eventStartDate;
                $def_sess->end = $event->eventEndDate;
                $def_sess->order = 0;
                $def_sess->creatorID = $this->currentPerson->personID;
                $def_sess->updaterID = $this->currentPerson->personID;
                $def_sess->save();
            }
        }

        return redirect('/event-tickets/'.$eventID);
    }

    public function edit($id)
    {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update(Request $request, $id)
    {
        // responds to PATCH /blah/id
        $ticket = Ticket::find($id);
        $this->currentPerson = Person::find(auth()->user()->id);
        $name = request()->input('name');
        $value = request()->input('value');

        // if passed from the event report (and other pages) the $name will have a dash
        if (strpos($name, '-')) {
            list($name, $field) = array_pad(explode('-', $name, 2), 2, null);
        } else {
            // Otherwise, shave off number at the end to match fieldname
            $name = substr(request()->input('name'), 0, -1);
        }

        if ($name == 'availabilityEndDate' or $name == 'earlyBirdEndDate' and $value !== null) {
            $date = date('Y-m-d H:i:s', strtotime(trim($value)));
            $value = $date;
        }

        $ticket->{$name} = $value;
        $ticket->updaterID = $this->currentPerson->personID;
        $ticket->save();

        return json_encode(['status' => 'success', 'msg' => $name, 'val' => $value]);
    }

    public function destroy($id)
    {
        // responds to DELETE /blah/id
        $this->currentPerson = Person::find(auth()->user()->id);
        $ticket = Ticket::Find($id);
        $eventID = $ticket->eventID;
        $sessions = EventSession::where('ticketID', '=', $ticket->ticketID)->get();
        //check to see if any regIDs have this ticketID
        if (Registration::where('ticketID', $id)->count() > 0) {
            // soft-delete if there are registrations

            $ticket->isSuppressed = 1;
            $ticket->updaterID = $this->currentPerson->personID;
            $ticket->save();
        } else {
            // else just remove from DB
            DB::table('event-tickets')->where('ticketID', $id)->delete();
        }

        foreach ($sessions as $es) {
            $regSesses = RegSession::where('sessionID', '=', $es->sessionID)->get();
            foreach ($regSesses as $rs) {
                $rs->delete();
            }
            $es->delete();
        }

        return redirect('/event-tickets/'.$eventID);
    }
}
