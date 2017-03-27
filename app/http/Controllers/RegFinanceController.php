<?php

namespace App\Http\Controllers;

use App\Notifications\EventReceipt;
use App\Registration;
use Illuminate\Http\Request;
use App\Org;
use App\Person;
use App\User;
use App\Event;
use App\Ticket;
use App\RegFinance;
use App\Location;
use Stripe\Stripe;

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
        $person        = Person::find($rf->personID);

        // prep for stripe-related stuff since the next step is billing for non-$0

        return view('v1.public_pages.register2', compact('ticket', 'event', 'quantity', 'discount_code', 'loc', 'rf', 'person'));
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
        //dd(request()->all());
        $rf    = RegFinance::find($id);
        $event = Event::find($rf->eventID);
        $org   = Org::find($event->orgID);
        $user  = User::find($rf->personID);

        // user can hit "at door" or "credit" buttons.
        // if the cost is $0, the pay button won't show on the form

        if($rf->confirmation != 'Processed') {

            // if > $0, payment details were given and we need to check stripeToken, stripeEmail, stripeTokenType and record to user table
            if($rf->cost > 0) {
                $stripeEmail     = $request->input('stripeEmail');
                $stripeToken     = $request->input('stripeToken');
                $stripeTokenType = $request->input('stripeTokenType');
                Stripe::setApiKey(env('STRIPE_SECRET'));

                // Check if a customer id exists, and retrieve or create
                if(!$user->stripe_id) {
                    $customer        = \Stripe\Customer::create(array(
                        'email' => $user->email,
                        'source' => $stripeToken,
                    ));
                    $user->stripe_id = $customer->id;
                    $user->save();
                }

                $charge = \Stripe\Charge::create(array(
                    'amount' => $rf->cost * 100,
                    'currency' => 'usd',
                    'description' => "$org->orgName Event Registration: $event->eventName",
                    'customer' => $user->stripe_id,
                ));

                $rf->status  = 'Processed';
                $rf->pmtType = $stripeTokenType;
            } else {
                $rf->status  = 'Processed';
                $rf->pmtType = 'At Door';
            }

            $end = $rf->seats - 1;
            for($i = $id - $end; $i <= $id; $i++) {
                $reg            = Registration::find($i);
                $reg->regStatus = 'Processed';
                // No one is actually, necessarily, logged in...
                //$reg->updaterID = auth()->user()->id;
                $reg->save();
            }
            $rf->confirmation = 'Processed';
            $rf->save();
        }
        // update $rf record and each $reg record status

        // email the user who paid
        $user->notify(new EventReceipt($rf));
        return view('v1.public_pages.event_receipt', compact('rf', 'event', 'loc', 'ticket'));
    }

    public function destroy ($id) {
        // responds to DELETE /blah/id
    }
}
