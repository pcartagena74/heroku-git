<?php

namespace App\Http\Controllers;

//use App\Notifications\EventReceipt;
use App\EventSession;
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
use App\Track;
use App\Bundle;
use App\RegSession;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use GrahamCampbell\Flysystem\Facades\Flysystem;
use League\Flysystem\AdapterInterface;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;

class RegFinanceController extends Controller
{
    public function __construct () {
        $this->middleware('auth');
        //$this->middleware('tidy')->only('update');
    }

    public function index () {
        // responds to /blah
    }

    public function show ($id) {
        // responds to GET /blah/id
        $needSessionPick = 0;
        $rf              = RegFinance::find($id);
        $event           = Event::find($rf->eventID);
        if($event->hasTracks > 0) {
            $tracks = Track::where('eventID', $event->eventID)->get();
        } else {
            $tracks = null;
        }

        $ticket = Ticket::find($rf->ticketID);

        if($ticket->isaBundle) {
            $tickets = Ticket::join('bundle-ticket as bt', 'bt.ticketID', 'event-tickets.ticketID')
                             ->where([
                                 ['bt.bundleID', '=', $ticket->ticketID],
                                 ['event-tickets.eventID', '=', $event->eventID]
                             ])
                             ->get();
            $s       = EventSession::where('eventID', '=', $event->eventID)
                                   ->select(DB::raw('distinct ticketID'))
                                   ->get();
            foreach($s as $t) {
                if($tickets->contains('ticketID', $t->ticketID)) {
                    $needSessionPick = 1;
                    break;
                }
            }
        } else {
            $tickets = Ticket::where('ticketID', '=', $rf->ticketID)->get();
            $s       = EventSession::where([
                ['eventID', '=', $event->eventID],
                ['ticketID', '=', '$ticket->ticketID']
            ])->first();

            if($s !== null) {
                $needSessionPick = 1;
            }
        }

        $loc           = Location::find($event->locationID);
        $quantity      = $rf->seats;
        $discount_code = $rf->discountCode;
        $person        = Person::find($rf->personID);
        $org           = Org::find($event->orgID);

        $prefixes   = DB::table('prefixes')->get();
        $industries = DB::table('industries')->get();

        // prep for stripe-related stuff since the next step is billing for non-$0

        return view('v1.public_pages.register2', compact('ticket', 'event', 'quantity', 'discount_code', 'org',
            'loc', 'rf', 'person', 'prefixes', 'industries', 'tracks', 'tickets', 'needSessionPick'));
    }

    public function show_receipt(RegFinance $rf){
        $event = Event::find($rf->eventID);
        $ticket = Ticket::find($rf->ticketID);
        $quantity = $rf->seats;
        $discount_code = $rf->discountCode;
        $org = Org::find($event->orgID);
        $loc = Location::find($event->locationID);

        if($ticket->isaBundle) {
            $tickets = Ticket::join('bundle-ticket as bt', 'bt.ticketID', 'event-tickets.ticketID')
                             ->where([
                                 ['bt.bundleID', '=', $ticket->ticketID],
                                 ['event-tickets.eventID', '=', $event->eventID]
                             ])
                             ->get();
            $s       = EventSession::where('eventID', '=', $event->eventID)
                                   ->select(DB::raw('distinct ticketID'))
                                   ->get();
            foreach($s as $t) {
                if($tickets->contains('ticketID', $t->ticketID)) {
                    $needSessionPick = 1;
                    break;
                }
            }
        } else {
            $tickets = Ticket::where('ticketID', '=', $rf->ticketID)->get();
            $s       = EventSession::where([
                ['eventID', '=', $event->eventID],
                ['ticketID', '=', '$ticket->ticketID']
            ])->first();

            if($s !== null) {
                $needSessionPick = 1;
            }
        }

        if($needSessionPick) {
            $tickets = Ticket::join('bundle-ticket as bt', 'bt.ticketID', 'event-tickets.ticketID')
                             ->where([
                                 ['bt.bundleID', '=', $ticket->ticketID],
                                 ['event-tickets.eventID', '=', $event->eventID]
                             ])
                             ->get();
        } else {
            $tickets = null;
        }

        $x = compact('needSessionPick', 'ticket', 'event', 'quantity', 'discount_code', 'loc', 'rf', 'person', 'org', 'tickets');
        return view('v1.public_pages.event_receipt', $x);
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
        // responds to PATCH /complete_registration/{id}
//dd(request()->all());

        $rf                  = RegFinance::find($id);
        $event               = Event::find($rf->eventID);
        $org                 = Org::find($event->orgID);
        $user                = User::find($rf->personID);
        $person              = Person::find($rf->personID);
        $loc                 = Location::find($event->locationID);
        $quantity            = $rf->seats;
        $discount_code       = $rf->discountCode;
        $ticket              = Ticket::find($rf->ticketID);
        $this->currentPerson = Person::find(auth()->user()->id);
        $needSessionPick     = $request->input('needSessionPick');
        $stripeToken     = $request->input('stripeToken');

        if($needSessionPick) {
            $tickets = Ticket::join('bundle-ticket as bt', 'bt.ticketID', 'event-tickets.ticketID')
                             ->where([
                                 ['bt.bundleID', '=', $ticket->ticketID],
                                 ['event-tickets.eventID', '=', $event->eventID]
                             ])
                             ->get();
        } else {
            $tickets = null;
        }

        // user can hit "at door" or "credit" buttons.
        // if the cost is $0, the pay button won't show on the form

        if($rf->status != 'Processed') {

            // if cost > $0 AND payment details were given ($stripeToken isset),
            // we need to check stripeToken, stripeEmail, stripeTokenType and record to user table
            if($rf->cost > 0 && isset($stripeToken)) {
                $stripeEmail     = $request->input('stripeEmail');
                $stripeTokenType = $request->input('stripeTokenType');
                Stripe::setApiKey(env('STRIPE_SECRET'));

                // Check if a customer id exists, and retrieve or create
                if(!$user->stripe_id) {
                    $customer        = \Stripe\Customer::create(array(
                        'email' => $user->email,
                        'source' => $stripeToken,
                    ));
                    $user->stripeEmail = $customer->email;
                    $user->stripe_id = $customer->id;
                    $user->save();
                }

                $charge = \Stripe\Charge::create(array(
                    'amount' => $rf->cost * 100,
                    'currency' => 'usd',
                    'description' => "$org->orgName Event Registration: $event->eventName",
                    'customer' => $user->stripe_id,
                ));
                $rf->stripeChargeID = $charge->id;
                $rf->status  = 'Processed';
                $rf->pmtType = $stripeTokenType;
                $rf->pmtRecd = 1;

            } elseif($rf->cost > 0) {
                // cost > 0 and the 'Pay at Door' button was pressed
                $rf->status  = 'Payment Pending';
                $rf->pmtType = 'At Door';
            } else {
                $rf->pmtRecd = 1;
                $rf->status  = 'Processed';
                $rf->pmtType = 'No Charge';
            }

            $discountAmt = 0;
            $end         = $rf->seats - 1;
            for($i = $id - $end; $i <= $id; $i++) {
                $reg            = Registration::find($i);
                $reg->regStatus = 'Processed';
                // No one is actually, necessarily, logged in...
                //$reg->updaterID = auth()->user()->id;
                $discountAmt += ($reg->origcost - $reg->subtotal);
                $reg->save();
            }
            // Confirmation code is:  personID-regFinance->regID/seats
            $rf->confirmation = $this->currentPerson->personID . "-" . $rf->regID . "-" . $rf->seats;

            // Need to set fees IF the cost > $0
            if($rf->cost > 0) {
                // Stripe ccFee = 2.9% of $rf->cost + $0.30, no cap
                $rf->ccFee = number_format(($rf->cost * .029) + .30, 2, '.', ',');

                // mCentric Handle fee = 2.9% of $rf->cost + $0.30 capped at $4.00 for orgID 10, else $5
                $rf->handleFee = number_format(($rf->cost * .029) + .30, 2, '.', '');
                if($rf->handleFee > 5) {
                    $rf->handleFee = number_format(5, 2, '.', '');
                }
                $rf->orgAmt      = number_format($rf->cost - $rf->ccFee - $rf->handleFee, 2, '.', '');
                $rf->discountAmt = number_format($discountAmt, 2, '.', '');
            }
            // fees above are already $0 unless changed so save.
            $rf->save();

            // Update ticket purchase on all bundle ticket members by $rf->seat
            if($ticket->isaBundle) {
                foreach($tickets as $t) {
                    $t->regCount += $rf->seats;
                    $t->save();
                }
            } else {
                $ticket->regCount += $rf->seats;
                $ticket->save();
            }

        }
        // update $rf record and each $reg record status

        // Record the Session Selections (if any AND if not already written)
        $written = RegSession::where([
            ['regID', '=', $rf->regID],
            ['eventID', '=', $event->eventID],
            ['creatorID', '=', $this->currentPerson->personID]
        ])->first();

        if($needSessionPick && !$written) {
            // inserted code below into this loop to be able to process for each registered person
            // need $reg->personID to save into RegSession record.
            for($i = $id - $end; $i <= $id; $i++) {
                $reg = Registration::find($i);

                for($j = 1; $j <= $event->confDays; $j++) {
                    $z = EventSession::where([
                        ['confDay', '=', $j],
                        ['eventID', '=', $event->eventID]
                    ])->first();
                    $y = Ticket::find($z->ticketID);

                    for($x = 1; $x <= 5; $x++) {
                        if(request()->input('sess-' . $j . '-' . $x)) {
                            // if this is set, the value is the session that was chosen.
                            // Create the RegSession record

                            $es            = new RegSession;
                            $es->regID     = $rf->regID;
                            $es->personID  = $reg->personID;
                            $es->eventID   = $event->eventID;
                            $es->confDay   = $j;
                            $es->sessionID = request()->input('sess-' . $j . '-' . $x);
                            $es->creatorID = auth()->user()->id;
                            $es->updaterID = auth()->user()->id;
                            $es->save();
                        }
                    }
                }
            }

        } else {
            // No session selection data to record;
        }

        // email the user who paid
        // $user->notify(new EventReceipt($rf));
        $x = compact('needSessionPick', 'ticket', 'event', 'quantity', 'discount_code',
            'loc', 'rf', 'person', 'prefixes', 'industries', 'org', 'tickets');

        $receipt_filename = $rf->eventID . "/" . $rf->confirmation . ".pdf";
        $pdf = PDF::loadView('v1.public_pages.event_receipt', $x)
            ->setOption('disable-javascript', false)
            ->setOption('encoding', 'utf-8');
            //->save($receipt_filename);

        //Storage::put($receipt_filename, $pdf->output());
        Flysystem::connection('s3_receipts')->put($receipt_filename, $pdf->output(), ['visibility' => AdapterInterface::VISIBILITY_PUBLIC]);

        $client = new S3Client([
            'credentials' => [
                'key'    => env('AWS_KEY'),
                'secret' => env('AWS_SECRET')
            ],
            'region' => env('AWS_REGION'),
            'version' => 'latest',
        ]);

        $adapter = new AwsS3Adapter($client, env('AWS_BUCKET2'));
        $s3fs = new Filesystem($adapter);
        $event_pdf = $s3fs->getAdapter()->getClient()->getObjectUrl(env('AWS_BUCKET2'), $receipt_filename);

        //return $pdf->download('invoice.pdf');
        Mail::to($user->login)->send(new EventReceipt($rf, $event_pdf, $x));
        //return view('v1.public_pages.event_receipt', compact('rf', 'event', 'loc', 'ticket'));

        return view('v1.public_pages.event_receipt', $x);
    }

    public function destroy ($id) {
        // responds to DELETE /blah/id
    }
}
