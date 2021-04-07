<?php

namespace App\Http\Controllers;

use App\Event;
use App\Person;
use App\Registration;
use App\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BundleController extends Controller
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
    }

    public function create()
    {
        // responds to /blah/create and shows add/edit form
    }

    public function store(Request $request)
    {
        // responds to POST to /blah and creates, adds, stores the event
        dd(request()->all());
    }

    public function edit($id)
    {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update(Request $request, $id)
    {
        // responds to PATCH /blah/id
        Log::info("Showing ajax input called on eventID: $id ".print_r(request()->all(), true));
        $eventID = $id;

        // $name is passed as eventID-{bundleID]-{ticketID} so it needs to be split
        $name = request()->input('name');
        list($field1, $field2, $ticketID) = array_pad(explode('-', $name, 3), 3, null);

        $value = request()->input('value');
        // pk is the bundleID.  Yes. Redundancy above done for editable_jQ_uniqueness
        $bundleID = request()->input('pk');

        // The ticketID is no longer part of the bundle
        if ($value == 0) {
            DB::table('bundle-ticket')->where([
                ['bundleID', $bundleID],
                ['eventID', $eventID],
                ['ticketID', $ticketID],
            ])->delete();
        } elseif ($value == 1) {
            $cp = $this->currentPerson = Person::find(auth()->user()->id);
            DB::table('bundle-ticket')->insert(
                ['bundleID' => $bundleID, 'eventID' => $eventID, 'ticketID' => $ticketID,
                    'creatorID' => $cp->personID, 'updaterID' => $cp->personID, ]
            );
        }

        return json_encode(['status' => 'success', 'message' => print_r(request()->all(), true)]);
    }

    public function destroy($id)
    {
        // responds to DELETE /blah/id
        $this->currentPerson = Person::find(auth()->user()->id);
        $ticket = Ticket::Find($id);
        $eventID = $ticket->eventID;
        //check to see if any regIDs have this ticketID
        if (Registration::where('ticketID', $id)->count() > 0) {
            // soft-delete if there are registrations

            $ticket->isSuppressed = 1;
            $ticket->updaterID = $this->currentPerson->personID;
            $ticket->save();
        } else {
            // else just remove from DB after removing bundle associations
            DB::table('bundle-ticket')->where('bundleID', $ticket->ticketID)->delete();
            DB::table('event-tickets')->where('ticketID', $id)->delete();
        }

        return redirect('/event-tickets/'.$eventID);
    }
}
