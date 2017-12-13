<?php

namespace App\Http\Controllers;

//use App\Notifications\EventReceipt;
use App\EventSession;
use App\Mail\GroupEventReceipt;
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
use Carbon\Carbon;
use App\EventDiscount;
use App\Email;

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
        try {
            $rf              = RegFinance::find($id);
            $needSessionPick = 0;
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
                    ['ticketID', '=', $ticket->ticketID]
                ])->get();

                if(count($s) > 1) {
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

            if($ticket->waitlisting()){
                $needSessionPick = 0;
            }
            return view('v1.public_pages.register2', compact('ticket', 'event', 'quantity', 'discount_code', 'org',
                'loc', 'rf', 'person', 'prefixes', 'industries', 'tracks', 'tickets', 'needSessionPick'));

        } catch(\Exception $exception) {
            $message = "An unexpected error occurred.";
            return view('v1.public_pages.error_display', compact('message'));
        }
    }

    public function show_receipt (RegFinance $rf) {
        $event           = Event::find($rf->eventID);
        $ticket          = Ticket::find($rf->ticketID);
        $quantity        = $rf->seats;
        $discount_code   = $rf->discountCode;
        $org             = Org::find($event->orgID);
        $loc             = Location::find($event->locationID);
        $needSessionPick = 0;

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
            ])->get();

            if(count($s) > 1) {
                $needSessionPick = 1;
            }
        }

        $x =
            compact('needSessionPick', 'ticket', 'event', 'quantity', 'discount_code', 'loc', 'rf',
                'person', 'org', 'tickets');
        return view('v1.public_pages.event_receipt', $x);
    }

    public function create () {
        // responds to /blah/create and shows add/edit form
    }

    public function store (Request $request) {
        // responds to POST to /blah and creates, adds, stores the event
        dd(request()->all());
    }

    public function edit (RegFinance $rf) {
        // responds to GET /groupreg/reg and shows the group_reg1 page
        $quantity = $rf->seats;
        $event    = Event::find($rf->eventID);
        $org      = Org::find($event->orgID);
        $loc      = Location::find($event->locationID);

        return view('v1.auth_pages.events.group_reg1', compact('event', 'quantity', 'org', 'loc', 'rf'));
    }

    public function update (Request $request, $id) {
        // responds to PATCH /complete_registration/{id}
        set_time_limit(100);

        $rf                  = RegFinance::with('registration', 'ticket')->where('regID', $id)->first();
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
        $stripeToken         = $request->input('stripeToken');

        if($ticket->isaBundle) {
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
            if($rf->cost > 0 && $stripeToken !== null) {
                $stripeEmail = $request->input('stripeEmail');
                // $stripeEmail doesn't appear to be set by Stripe's token anymore
                if($stripeEmail === null) {
                    $stripeEmail = $user->email;
                }
                $stripeTokenType = $request->input('stripeTokenType');
                Stripe::setApiKey(env('STRIPE_SECRET'));

                // Check if a customer id exists, and retrieve or create
                if(!$user->stripe_id) {
                    $customer          = \Stripe\Customer::create(array(
                        'email' => $user->email,
                        'source' => $stripeToken,
                    ));
                    $user->stripeEmail = $customer->email;
                    $user->stripe_id   = $customer->id;
                    $user->save();
                }

                $charge             = \Stripe\Charge::create(array(
                    'amount' => $rf->cost * 100,
                    'currency' => 'usd',
                    'description' => "$org->orgName Event Registration: $event->eventName",
                    'customer' => $user->stripe_id,
                ));
                $rf->stripeChargeID = $charge->id;
                $rf->status         = 'Processed';
                $rf->pmtType        = $stripeTokenType;
                $rf->pmtRecd        = 1;

            } elseif($rf->cost > 0) {
                // cost > 0 and the 'Pay at Door' button was pressed
                if($ticket->waitlisting()){
                    $rf->status  = 'Wait List';
                    $rf->pmtType = 'pending';
                } else {
                    $rf->status  = 'Payment Pending';
                    $rf->pmtType = 'At Door';
                }
            } else {
                $rf->pmtRecd = 1;
                $rf->status  = 'Processed';
                $rf->pmtType = 'No Charge';
            }

            $discountAmt = 0;
            $end         = $rf->seats - 1;
            for($i = $id - $end; $i <= $id; $i++) {
                $reg = Registration::find($i);
                if($ticket->waitlisting()){
                    $reg->regStatus = 'Wait List';
                } else {
                    $reg->regStatus = 'Processed';
                }
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

                // mCentric Handle fee = 2.9% of $rf->cost + $0.30 capped at $5.00 for orgID 10, else $5
                $rf->handleFee = number_format(($rf->cost * .029) + .30, 2, '.', '');
                if($rf->handleFee > 5) {
                    $rf->handleFee = number_format(5 * $rf->seats, 2, '.', '');
                } else {
                    $rf->handleFee = number_format($rf->handleFee * $rf->seats, 2, '.', '');
                }
                $rf->orgAmt      = number_format($rf->cost - $rf->ccFee - $rf->handleFee, 2, '.', '');
                $rf->discountAmt = number_format($discountAmt, 2, '.', '');
            }
            // fees above are already $0 unless changed so save.
            $rf->save();

            // Update ticket purchase on all bundle ticket members by $rf->seat
            if($ticket->isaBundle) {
                foreach($tickets as $t) {
                    if($t->waitlisting()){
                        $t->waitCount += $rf->seats;
                    } else {
                        $t->regCount += $rf->seats;
                    }
                    $t->save();
                }
            } else {
                if($ticket->waitlisting()){
                    $ticket->waitCount += $rf->seats;
                } else {
                    $ticket->regCount += $rf->seats;
                }
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

                            $rs            = new RegSession;
                            $rs->regID     = $rf->regID;
                            $rs->personID  = $reg->personID;
                            $rs->eventID   = $event->eventID;
                            $rs->confDay   = $j;
                            $rs->sessionID = request()->input('sess-' . $j . '-' . $x);
                            $rs->creatorID = auth()->user()->id;
                            $rs->updaterID = auth()->user()->id;
                            $rs->save();

                            $e = EventSession::find($rs->sessionID);
                            $e->regCount++;
                            $e->save();
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
        $pdf              = PDF::loadView('v1.public_pages.event_receipt', $x)
                               ->setOption('disable-javascript', false)
                               ->setOption('encoding', 'utf-8');

        Flysystem::connection('s3_receipts')->put($receipt_filename, $pdf->output(),
            ['visibility' => AdapterInterface::VISIBILITY_PUBLIC]);

        $client = new S3Client([
            'credentials' => [
                'key' => env('AWS_KEY'),
                'secret' => env('AWS_SECRET')
            ],
            'region' => env('AWS_REGION'),
            'version' => 'latest',
        ]);

        $adapter   = new AwsS3Adapter($client, env('AWS_BUCKET2'));
        $s3fs      = new Filesystem($adapter);
        $event_pdf = $s3fs->getAdapter()->getClient()->getObjectUrl(env('AWS_BUCKET2'), $receipt_filename);

        //return $pdf->download('invoice.pdf');
        Mail::to($user->login)->send(new EventReceipt($rf, $event_pdf, $x));
        //return view('v1.public_pages.event_receipt', compact('rf', 'event', 'loc', 'ticket'));

        //return view('v1.public_pages.event_receipt', $x);
        return redirect('/show_receipt/'. $rf->regID);
    }

    public function group_reg1 (Request $request) {
        $this->currentPerson = Person::find(auth()->user()->id);
        $seats               = 0;
        $total_cost          = 0;
        $total_orig          = 0;
        $total_handle        = 0;
        $today               = Carbon::now();

        // Process up to 15 entries
        for($i = 1; $i <= 15; $i++) {
            $personID  = request()->input('person-' . $i);
            $firstName = request()->input('firstName-' . $i);
            $lastName  = request()->input('lastName-' . $i);
            $email     = request()->input('email-' . $i);
            $pmiid     = request()->input('pmiid-' . $i);
            $ticketID  = request()->input('ticketID-' . $i);
            $code      = request()->input('code-' . $i);
            if($code === null || $code == " ") {
                $code = 'N/A';
            }
            if($personID === null && $firstName !== null) {
                // Perform a quick search to determine if this is a resubmit

                $e = Email::where('emailADDR', $email)->first();
                if($e) {
                    $p = Person::find($e->personID);
                } else {
                    // create requisite records: person, orgperson
                    $p               = new Person;
                    $p->firstName    = $firstName;
                    $p->lastName     = $lastName;
                    $p->defaultOrgID = $this->currentPerson->defaultOrgID;
                    $p->login        = $email;
                    $p->creatorID    = $this->currentPerson->personID;
                    $p->save();

                    $u        = new User;
                    $u->id    = $p->personID;
                    $u->login = $email;
                    $u->email = $email;
                    $u->save();

                    $op           = new OrgPerson;
                    $op->personID = $p->personID;
                    $op->orgID    = $p->defaultOrgID;
                    $op->OrgStat1 = $pmiid;
                    $op->save();

                    $e            = new Email;
                    $e->personID  = $p->personID;
                    $e->emailADDR = $email;
                    $e->save();
                }
            } else {
                // get the person record from $personID
                $p = Person::find($personID);
            }

            // Create a registration record for each attendee
            $eventID = request()->input('eventID');

            $handle = 0;
            if($p) {
                // Setup variables for valid attendee
                $t = Ticket::find($ticketID);
                // tr: ticket remember
                $tr = $t->ticketID;
                // cr: code remember
                $cr = $code;
                $seats++;

                $reg           = new Registration;
                $reg->eventID  = $eventID;
                $reg->ticketID = $ticketID;
                $reg->personID = $p->personID;
                if($p->allergenInfo) {
                    $reg->allergenInfo = $p->allergenInfo;
                }
                $reg->registeredBy = $this->currentPerson->showFullName();
                $reg->discountCode = $code;
                if($pmiid) {
                    $reg->membership = "Member";
                } else {
                    $reg->membership = "Non-Member";
                }
                $reg->token     = request()->input('_token');
                $reg->creatorID = $this->currentPerson->personID;
                if($p->affiliation) {
                    $reg->affiliation = $p->affiliation;
                }
                // Defaulting to No for explicit user agreement
                // Add to orgperson / profile
                $reg->canNetwork = 0;
                $reg->isAuthPDU = 0;
                // origcost
                // subtotal
                if($t->earlyBirdEndDate !== null && $today->lte($t->earlyBirdEndDate)) {
                    // Use earlybird discount pricing as base
                    if($reg->membership == 'Member') {
                        $reg->origcost = $t->memberBasePrice;
                        $reg->subtotal = $t->memberBasePrice - ($t->memberBasePrice * $t->earlyBirdPercent / 100);
                    } else {
                        $reg->origcost = $t->nonmbrBasePrice;
                        $reg->subtotal = $t->nonmbrBasePrice - ($t->nonmbrBasePrice * $t->earlyBirdPercent / 100);
                    }
                } else {
                    // Use non-discount pricing
                    if($reg->membership == 'Member') {
                        $reg->origcost = $t->memberBasePrice;
                        $reg->subtotal = $t->memberBasePrice;
                    } else {
                        $reg->origcost = $t->nonmbrBasePrice;
                        $reg->subtotal = $t->nonmbrBasePrice;
                    }
                }
                if($code) {
                    $dCode = EventDiscount::where([
                        ['eventID', $eventID],
                        ['discountCODE', $code]
                    ])->first();
                    if($dCode->percent > 0) {
                        $reg->subtotal = $reg->subtotal - ($reg->subtotal * $dCode->percent / 100);
                    } else {
                        $reg->subtotal = $reg->subtotal - $dCode->flatAmt;
                    }
                }
                $reg->regStatus = 'In Progress';
                $reg->save();
                $total_orig = $total_orig + $reg->origcost;
                $total_cost = $total_cost + $reg->subtotal;
                $handle     = $reg->subtotal * 0.029;
                if($handle > 5) {
                    $handle = 5;
                }
                $total_handle = $total_handle + $handle;
                $reg_save     = $reg->regID;
            }
        }
        // Create a regfinance record for all of the attendees
        // Show a group receipt
        $rf               = new RegFinance;
        $rf->regID        = $reg_save;
        $rf->eventID      = $eventID;
        $rf->ticketID     = $tr;
        $rf->discountCode = $cr;
        $rf->seats        = $seats;
        $rf->personID     = $this->currentPerson->personID;
        $rf->cost         = $total_cost;
        $rf->status       = 'In Progress';
        $rf->handleFee    = $total_handle;
        $rf->token        = request()->input('_token');
        $rf->save();
        return redirect('/groupreg/' . $reg_save);
    }

    public function group_reg2 (Request $request, RegFinance $rf) {
        // responds to PATCH /group_reg2/{rf}

        $event               = Event::find($rf->eventID);
        $org                 = Org::find($event->orgID);
        $user                = User::find($rf->personID);
        $person              = Person::find($rf->personID);
        $loc                 = Location::find($event->locationID);
        $quantity            = $rf->seats;
        $this->currentPerson = Person::find(auth()->user()->id);
        $stripeToken         = $request->input('stripeToken');
        $total_handle        = 0;

        // user can hit "at door" or "credit" buttons.
        // if the cost is $0, the pay button won't show on the form

        if($rf->status != 'Processed') {
            // if cost > $0 AND payment details were given ($stripeToken isset),
            // we need to check stripeToken, stripeEmail, stripeTokenType and record to user table
            if($rf->cost > 0 && $stripeToken !== null) {
                $stripeEmail     = $request->input('stripeEmail');
                $stripeTokenType = $request->input('stripeTokenType');
                Stripe::setApiKey(env('STRIPE_SECRET'));

                // Check if a customer id exists, and retrieve or create
                if(!$user->stripe_id) {
                    $customer          = \Stripe\Customer::create(array(
                        'email' => $user->email,
                        'source' => $stripeToken,
                    ));
                    $user->stripeEmail = $customer->email;
                    $user->stripe_id   = $customer->id;
                    $user->save();
                }

                $charge             = \Stripe\Charge::create(array(
                    'amount' => $rf->cost * 100,
                    'currency' => 'usd',
                    'description' => "$org->orgName Group Registration: $event->eventName",
                    'customer' => $user->stripe_id,
                ));
                $rf->stripeChargeID = $charge->id;
                $rf->status         = 'Processed';
                $rf->pmtType        = $stripeTokenType;
                $rf->pmtRecd        = 1;

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
            // update $rf record and each $reg record status
            for($i = $rf->regID - $end; $i <= $rf->regID; $i++) {
                $reg            = Registration::find($i);
                $reg->regStatus = 'Processed';
                // No one is actually, necessarily, logged in...
                //$reg->updaterID = auth()->user()->id;
                $discountAmt += ($reg->origcost - $reg->subtotal);
                $reg->save();

                // Update ticket purchase on all bundle ticket members by $rf->seat
                $ticket = Ticket::find($reg->ticketID);
                if($ticket->isaBundle) {
                    $tickets = Ticket::join('bundle-ticket as bt', 'bt.ticketID', 'event-tickets.ticketID')
                                     ->where([
                                         ['bt.bundleID', '=', $ticket->ticketID],
                                         ['event-tickets.eventID', '=', $event->eventID]
                                     ])
                                     ->get();
                    foreach($tickets as $t) {
                        $t->regCount++;
                        $t->save();
                    }
                } else {
                    $ticket->regCount++;
                    $ticket->save();
                }
                if($reg->subtotal > 0) {
                    // mCentric Handle fee = 2.9% of $rf->cost + $0.30
                    $handleFee = number_format(($rf->cost * .029) + .30, 2, '.', '');
                    // capped at $5.00
                    if($handleFee > 5) {
                        $handleFee = number_format(5, 2, '.', '');
                    }
                    $total_handle += $handleFee;
                }
            }
            // Confirmation code is:  personID-regFinance->regID/seats
            $rf->confirmation = $this->currentPerson->personID . "-" . $rf->regID . "-" . $rf->seats;

            // Need to set fees IF the cost > $0
            if($rf->cost > 0) {
                // Stripe ccFee = 2.9% of $rf->cost + $0.30, no cap
                $rf->ccFee = number_format(($rf->cost * .029) + .30, 2, '.', ',');

                // mCentric Handle fee = 2.9% of $rf->cost + $0.30 capped at $5.00
                $rf->handleFee   = $total_handle;
                $rf->orgAmt      = number_format($rf->cost - $rf->ccFee - $rf->handleFee, 2, '.', '');
                $rf->discountAmt = number_format($discountAmt, 2, '.', '');
            }
            // fees above are already $0 unless changed so save.
            $rf->isGroupReg = 1;
            $rf->save();
        }

        // email the user who paid
        // $user->notify(new EventReceipt($rf));
        $x = compact('event', 'quantity', 'loc', 'rf', 'person', 'prefixes', 'industries', 'org', 'tickets');

        $receipt_filename = $rf->eventID . "/" . $rf->confirmation . ".pdf";
        $pdf              = PDF::loadView('v1.auth_pages.events.group_receipt', $x)
                               ->setOption('disable-javascript', false)
                               ->setOption('javascript-delay', 20)
                               ->setOption('encoding', 'utf-8');
        //->save($receipt_filename);

        //Storage::put($receipt_filename, $pdf->output());
        Flysystem::connection('s3_receipts')->put($receipt_filename, $pdf->output(), ['visibility' => AdapterInterface::VISIBILITY_PUBLIC]);

        $client = new S3Client([
            'credentials' => [
                'key' => env('AWS_KEY'),
                'secret' => env('AWS_SECRET')
            ],
            'region' => env('AWS_REGION'),
            'version' => 'latest',
        ]);

        $adapter   = new AwsS3Adapter($client, env('AWS_BUCKET2'));
        $s3fs      = new Filesystem($adapter);
        $event_pdf = $s3fs->getAdapter()->getClient()->getObjectUrl(env('AWS_BUCKET2'), $receipt_filename);

        // Mail will need to INSTEAD go to each of the persons attached to Registration records
        Mail::to($user->login)->send(new GroupEventReceipt($rf, $event_pdf, $x));

        return view('v1.auth_pages.events.group_receipt', $x);
    }

    public function show_group_receipt (RegFinance $rf) {
        $quantity = $rf->seats;
        $event    = Event::find($rf->eventID);
        $loc      = Location::find($event->locationID);
        $person   = Person::find($rf->personID);
        $org      = Org::find($event->orgID);

        return view('v1.auth_pages.events.group_receipt', compact('event', 'quantity', 'loc', 'rf', 'person', 'org'));
    }

    public function destroy ($id) {
        // responds to DELETE /blah/id
    }
}
