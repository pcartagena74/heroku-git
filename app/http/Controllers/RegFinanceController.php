<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Registration;
use App\Event;
use App\Ticket;
use App\RegFinance;

class RegFinanceController extends Controller
{
    public function index () {
        // responds to /blah
    }

    public function show ($id) {
        // responds to GET /blah/id
        $reg    = Registration::find($id);
        $event  = Event::find($reg->eventID);
        $ticket = Ticket::find($reg->ticketID);

        dd($reg);

        return view('v1.public_pages.register2', compact('ticket', 'event', 'quantity', 'discount_code'));
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
    }

    public function destroy ($id) {
        // responds to DELETE /blah/id
    }
}
