<?php

namespace App\Http\Controllers;

//use App\Notifications\EventReceipt;
use App\Mail\EventReceipt;
use Illuminate\Support\Facades\Mail;
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
use Illuminate\Support\Facades\DB;

class RegFinanceController extends Controller
{
    public function __construct () {
        $this->middleware('auth');
    }

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
        $org           = Org::find($event->orgID);

        $prefixes   = DB::table('prefixes')->get();
        $industries = DB::table('industries')->get();

        // prep for stripe-related stuff since the next step is billing for non-$0

        return view('v1.public_pages.register2', compact('ticket', 'event', 'quantity', 'discount_code', 'org',
            'loc', 'rf', 'person', 'prefixes', 'industries'));
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
        
        $rf    = RegFinance::find($id);
        $event = Event::find($rf->eventID);
        $org   = Org::find($event->orgID);
        $user  = User::find($rf->personID);
        $person = Person::find($rf->personID);
        $loc   = Location::find($event->locationID);
        $quantity      = $rf->seats;
        $discount_code = $rf->discountCode;
        $ticket        = Ticket::find($rf->ticketID);
        $this->currentPerson = Person::find(auth()->user()->id);
        dd($event);

        // user can hit "at door" or "credit" buttons.
        // if the cost is $0, the pay button won't show on the form

        if($rf->confirmation != 'Processed') {

            // if cost > $0 AND payment details were given ($stripeToken isset),
            // we need to check stripeToken, stripeEmail, stripeTokenType and record to user table
            if($rf->cost > 0 && isset($stripeToken)) {
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
                $rf->pmtRecd = 1;

            } elseif($rf->cost > 0) {
                // cost > 0 and the 'Pay at Door' button was pressed
                $rf->status  = 'Payment Pending';
                $rf->pmtType = 'At Door';
            } else {
                $rf->status  = 'Processed';
                $rf->pmtType = 'No Charge';
            }

            $discountAmt = 0;
            $end = $rf->seats - 1;
            for($i = $id - $end; $i <= $id; $i++) {
                $reg            = Registration::find($i);
                $reg->regStatus = 'Processed';
                // No one is actually, necessarily, logged in...
                //$reg->updaterID = auth()->user()->id;
                $discountAmt+= ($reg->origcost - $reg->subtotal);
                $reg->save();
            }
            // Confirmation code is:  personID-regFinance->regID/seats
            $rf->confirmation = $this->currentPerson->personID . "-" . $rf->regID."/" .$rf->seats;

            // Need to set fees IF the cost > $0
           if($rf->cost > 0) {
               // Stripe ccFee = 2.9% of $rf->cost + $0.30, no cap
               $rf->ccFee = number_format(($rf->cost * .029) + .30, 2, '.', ',');

               // mCentric Handle fee = 2.9% of $rf->cost + $0.30 capped at $4.00 for orgID 10, else $5
               $rf->handleFee = number_format(($rf->cost * .029) + .30, 2, '.', '');
               if($rf->handleFee > 4 && $org->orgID == 10) {
                   $rf->handleFee = number_format(4, 2, '.', '');
               } elseif($rf->handleFee > 5) {
                   $rf->handleFee = number_format(5, 2, '.', '');
               }
               $rf->orgAmt      = number_format($rf->cost - $rf->ccFee - $rf->handleFee, 2, '.', '');
               $rf->discountAmt = number_format($discountAmt, 2, '.', '');
           }
           // fees above are already $0 unless changed so save.
            $rf->save();
        }
        // update $rf record and each $reg record status

        // email the user who paid
        //$user->notify(new EventReceipt($rf));
        Mail::to($user->login)->send(new EventReceipt($rf));
        //return view('v1.public_pages.event_receipt', compact('rf', 'event', 'loc', 'ticket'));
        return view('v1.public_pages.event_receipt', compact('ticket', 'event', 'quantity', 'discount_code',
            'loc', 'rf', 'person', 'prefixes', 'industries'));
    }

    public function destroy ($id) {
        // responds to DELETE /blah/id
    }
}
