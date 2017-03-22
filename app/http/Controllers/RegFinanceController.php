<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Event;
use App\Ticket;
use App\RegFinance;
use App\Location;

class RegFinanceController extends Controller
{
    public function index () {
        // responds to /blah
    }

    public function show ($id) {
        // responds to GET /blah/id
        $rf            = RegFinance::find($id);
        $event         = Event::find($rf->eventID);
        $ticket        = Ticket::find($rf->ticketID);
        $loc           = Location::find($event->locationID);
        $quantity      = $rf->seats;
        $discount_code = $rf->discountCode;

        // prep for stripe-related stuff since the next step is billing for non-$0

        return view('v1.public_pages.register2', compact('ticket', 'event', 'quantity', 'discount_code', 'loc', 'rf'));
    }

    public function create () {
        // responds to /blah/create and shows add/edit form
    }

    public function store (Request $request) {
        // responds to POST to /blah and creates, adds, stores the event
        dd(request()->all());
    }

    public function edit ($id) {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update (Request $request, $id) {
        // responds to PATCH /blah/id
        dd(request()->all());
    }

    public function destroy ($id) {
        // responds to DELETE /blah/id
    }
}
