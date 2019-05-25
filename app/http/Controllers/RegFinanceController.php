<?php

namespace App\Http\Controllers;

//use App\Notifications\EventReceipt;
use App\EventSession;
use App\Mail\GroupEventReceipt;
use App\Mail\EventReceipt;
use App\Notifications\ReceiptNotification;
use Illuminate\Support\Facades\Mail;
use App\Registration;
use Illuminate\Http\Request;
use App\Org;
use App\Person;
use App\OrgPerson;
use App\User;
use App\Event;
use App\Ticket;
use App\RegFinance;
use App\Location;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Error\Base;
use Stripe\Error\Card;
use Stripe\Error\InvalidRequest;
use Stripe\Error\ApiConnection;
use Stripe\Error\Authentication;
use Stripe\Error\Permission;
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
use Illuminate\Support\Facades\Validator;
use Redirect;
use App\Notifications\AccountCreation;
use Illuminate\Support\Facades\Hash;

set_time_limit(0);
ini_set('memory_limit', '-1');

class RegFinanceController extends Controller
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
        // responds to GET /confirm_registration/{id}
        $show_pass_fields = 0;
        $registering = 1;
        $today = Carbon::now()->format('n/j/Y');
        $u = User::find(auth()->user()->id);
        if ($u->password === null) {
            $show_pass_fields = 1;
        }
        try {
            $rf = RegFinance::find($id);
            $needSessionPick = 0;
            $event = Event::find($rf->eventID);
            if ($event->hasTracks > 0) {
                $tracks = Track::where('eventID', $event->eventID)->get();
            } else {
                $tracks = null;
            }

            $regs = Registration::where('rfID', '=', $rf->regID)->get();
            if(count($regs) == 0){
                $rf->delete();
                $button1 = "<a class='btn btn-primary btn-xs' href='" . env('APP_URL') . "/events/$event->eventID'>" . trans('messages.errors.no_reg1') . "</a>";
                $button2 = "<a class='btn btn-info btn-xs' href='" . env('APP_URL') . "/dashboard'>" . trans('messages.errors.no_reg2') . "</a>";
                $message = trans('messages.errors.no_regs', ['startover' => $button1, 'close' => $button2]);
                return view('v1.public_pages.error_display', compact('message'));
            }

            $loc = Location::find($event->locationID);
            $quantity = $rf->seats;
            //$discount_code = $rf->discountCode;
            $person = Person::find($rf->personID);
            $org = Org::find($event->orgID);

            $prefixes = DB::table('prefixes')->get();
            $industries = DB::table('industries')->get();
            // prep for stripe-related stuff since the next step is billing for non-$0

            $tickets = $event->tickets();

            $certs = DB::table('certifications')->select('certification')->get();
            $cert_array = $certs->toArray();

            return view('v1.public_pages.register2',
                compact('event', 'quantity', 'org', 'loc', 'rf', 'person', 'regs', 'cert_array', 'prefixes',
                    'industries', 'tracks', 'tickets', 'show_pass_fields')); // stripe_session', 'show_pass_fields', 'registering'));
        } catch (\Exception $exception) {
            $message = trans('messages.errors.unexpected');
            return view('v1.public_pages.error_display', compact('message'));
        }
    }

    public function show_receipt(RegFinance $rf)
    {
        $event = Event::find($rf->eventID);
        $quantity = $rf->seats;
        $org = Org::find($event->orgID);
        $loc = Location::find($event->locationID);
        $person = Person::find($rf->personID);

    // return view('v1.public_pages.event_receipt', $x);
        return view('v1.auth_pages.events.registration.group_receipt_authnav', compact('event', 'quantity', 'loc', 'rf', 'person', 'org'));
    }

    public function show_receipt_orig(RegFinance $rf)
    {
        $event = Event::find($rf->eventID);
        $quantity = $rf->seats;
        $org = Org::find($event->orgID);
        $loc = Location::find($event->locationID);
        $person = Person::find($rf->personID);

        // return view('v1.public_pages.event_receipt', $x);
        return view('v1.auth_pages.events.registration.group_receipt', compact('event', 'quantity', 'loc', 'rf', 'person', 'org'));
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
        // responds to GET /groupreg/rfID and shows the group_reg1 page
        $rf = RegFinance::where('regID', '=', $id)->with('registrations')->first();
        $quantity = $rf->seats;
        $event = Event::find($rf->eventID);
        $org = Org::find($event->orgID);
        $loc = Location::find($event->locationID);

        return view('v1.auth_pages.events.registration.group_reg1', compact('event', 'quantity', 'org', 'loc', 'rf'));
    }

    public function update(Request $request, $id)
    {
        // responds to PATCH /complete_registration/{id}
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $rf = RegFinance::with('registrations.ticket')->find($id);
        $event = Event::find($rf->eventID);
        $u = User::find(auth()->user()->id);
        $org = Org::find($event->orgID);
        $user = User::find($rf->personID);
        $person = Person::find($rf->personID);
        $prefixes = DB::table('prefixes')->get();
        $industries = DB::table('industries')->get();

        if ($u->password === null) {
            // validate password matching
            $validator = Validator::make($request->all(), [
                'password' => 'required|min:6|confirmed',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator);
            }
            $password = request()->input('password');
            $u->password = Hash::make($password);
            $u->save();
            // Removed flash notification to preserve receipt look & feel
            // request()->session()->flash('alert-success', "Your password was set successfully.");
            // email notification

            $person->notify(new AccountCreation($person, $event));
        }

        $loc = Location::find($event->locationID);
        $quantity = $rf->seats;
        //$discount_code = $rf->discountCode;
        // $ticket = Ticket::find($rf->ticketID);
        $this->currentPerson = Person::find(auth()->user()->id);
        $needSessionPick = $request->input('needSessionPick');
        $stripeToken = $request->input('stripeToken');

        // user can hit "at door", if available, or "credit" buttons.
        // if the cost is $0, the 'pay with card' button won't show on the form but a "complete registration" will

        if ($rf->status == 'wait') {
            // This transaction, regardless of cost, will increment the waitlist, etc.
            $rf->confirmation = $this->currentPerson->personID . "-" . $rf->regID . "-" . $rf->seats;
            $rf->pmtType = 'wait';
            $rf->save();
        } elseif ($rf->status != 'processed') {
            // if cost > $0 AND payment details were given ($stripeToken isset),
            // we need to check stripeToken, stripeEmail, stripeTokenType and record to user table
            if ($rf->cost > 0 && $stripeToken !== null) {
                $stripeEmail = $request->input('stripeEmail');
                // $stripeEmail doesn't appear to be set by Stripe's token anymore
                if ($stripeEmail === null) {
                    $stripeEmail = $user->email;
                }
                $stripeTokenType = $request->input('stripeTokenType');
                Stripe::setApiKey(env('STRIPE_SECRET'));

                // Get customer handle for this transaction
                try {
                    $customer = \Stripe\Customer::create(array(
                        'email' => $user->email,
                        'source' => $stripeToken,
                    ));
                } catch (\Exception $exception) {
                    request()->session()->flash('alert-danger', trans('messages.instructions.card_error') . $exception->getMessage());
                    return back()->withInput();
                }

                // If customer handle is different from a saved stripe_id (or stripe_id is null)
                if ($user->stripe_id != $customer->id) {
                    // Save the customer data as we'll always save the latest info
                    $user->stripeEmail = $customer->email;
                    $user->stripe_id = $customer->id;
                    $user->save();
                }

                try {
                    $charge = \Stripe\Charge::create(
                        array(
                        'amount' => $rf->cost * 100,
                        'currency' => 'usd',
                        'description' => "$org->orgName " . trans('messages.fields.event') . " " .
                                          trans('messages.headers.reg') . ": $event->eventName",
                        'customer' => $user->stripe_id),
                        array('idempotency_key' => $person->personID . '-' . $rf->regID . '-' . $rf->seats)
                    );
                } catch (Card $exception) {
                    request()->session()->flash('alert-danger', trans('messages.instructions.card_error') . $exception->getMessage());
                    return back()->withInput();
                } catch (InvalidRequest $exception) {
                    request()->session()->flash('alert-danger', trans('messages.instructions.card_error') . $exception->getMessage());
                    return back()->withInput();
                } catch (Authentication $exception) {
                    request()->session()->flash('alert-danger', trans('messages.instructions.card_error') . $exception->getMessage());
                    return back()->withInput();
                } catch (ApiConnection $exception) {
                    request()->session()->flash('alert-danger', trans('messages.instructions.card_error') . $exception->getMessage());
                    return back()->withInput();
                } catch (Permission $exception) {
                    request()->session()->flash('alert-danger', trans('messages.instructions.card_error') . $exception->getMessage());
                    return back()->withInput();
                } catch (Base $exception) {
                    request()->session()->flash('alert-danger', trans('messages.instructions.card_error') . $exception->getMessage());
                    return back()->withInput();
                } catch (\Exception $exception) {
                    request()->session()->flash('alert-danger', trans('messages.instructions.card_error') . $exception->getMessage());
                    return back()->withInput();
                }
                $rf->stripeChargeID = $charge->id;
                $rf->status = 'processed';
                $rf->pmtType = $stripeTokenType;
                $rf->pmtRecd = 1;
                $rf->save();
            } elseif ($rf->cost > 0 && $event->acceptsCash) {
                // cost > 0 and the 'Pay at Door' button was pressed (assumes acceptsCash = 1)
                $rf->status = 'pending';
                $rf->pmtType = 'door';
                $rf->save();
            } elseif ($rf->cost == 0) {
                //$rf->cost must be 0 so there's no charge for it
                $rf->pmtRecd = 1;
                $rf->status = 'processed';
                $rf->pmtType = 'free';
                $rf->save();
            } else {
                // Some weird error occured
                request()->session()->flash('alert-danger', trans('messages.errors.unexpected'));
                return back()->withInput();
            }

            $discountAmt = 0;
            $totalHandle = 0;

            // Cycle through event-registrations and update regStatus based on rf->status
            // Also update full cost and fee information
            foreach ($rf->registrations as $reg) {
                $handleFee = 0;

                if ($reg->ticket->waitlisting()) {
                    $reg->regStatus = 'wait';
                    $reg->ticket->update_count(1, 1);
                } elseif ($rf->status == 'pending') {
                    $reg->regStatus = 'pending';
                } else {
                    $reg->regStatus = 'processed';
                    $reg->ticket->update_count(1, 0);
                }

                // The first registrant should be logged in

                if ($reg->subtotal > 0) {
                    $reg->ccFee = number_format(($reg->subtotal * .029) + .30, 2, '.', ',');
                    $handleFee = number_format(($reg->subtotal * .029) + .30, 2, '.', '');
                    if ($handleFee > 5) {
                        $handleFee = 5;
                    }
                } else if ($reg->origcost > 0) {
                    $handleFee = number_format(($reg->origcost * .029) + .30, 2, '.', '');
                    if ($handleFee > 5) {
                        $handleFee = 5;
                    }
                }
                if (preg_match("/speaker/i", $reg->discountCode)) {
                    $p = Person::find($reg->personID);
                    $p->add_speaker_role();
                }

                $totalHandle += $handleFee;
                $discountAmt += ($reg->origcost - $reg->subtotal);
                $reg->mcentricFee = $handleFee;
                $reg->save();
            }
            // Confirmation code is:  personID-regFinance->regID/seats
            $rf->confirmation = $this->currentPerson->personID . "-" . $rf->regID . "-" . $rf->seats;

            // Need to set ccFee IF the cost > $0
            if ($rf->cost > 0) {
                // Stripe ccFee = 2.9% of $rf->cost + $0.30, no cap
                $rf->ccFee = number_format(($rf->cost * .029) + .30, 2, '.', ',');

                // mCentric Handle fee = 2.9% of $rf->cost + $0.30 capped at $5.00
                $rf->handleFee = number_format($totalHandle, 2, '.', '');
                $rf->orgAmt = number_format($rf->cost - $rf->ccFee - $rf->handleFee, 2, '.', '');
                $rf->discountAmt = number_format($discountAmt, 2, '.', '');
            } else {
                $rf->handleFee = number_format($totalHandle, 2, '.', '');
                $rf->orgAmt = number_format($rf->cost - $rf->ccFee - $rf->handleFee, 2, '.', '');
                $rf->discountAmt = number_format($discountAmt, 2, '.', '');
            }
            if ($rf->orgAmt < 0) {
                $rf->orgAmt = 0;
            }
            // fees above are already $0 unless changed so save.
            $rf->save();
        }
        // update $rf record and each $reg record status

        // Record the Session Selections (if any AND if not already written)
        $written = RegSession::where([
            ['regID', '=', $rf->regID],
            ['eventID', '=', $event->eventID],
            ['creatorID', '=', $this->currentPerson->personID]
        ])->first();

        if (!$written) {
            // inserted code below into this loop to be able to process for each registered person
            // need $reg->personID to save into RegSession record.
            foreach ($rf->registrations as $reg) {
                for ($j = 1; $j <= $event->confDays; $j++) {
                    $z = EventSession::where([
                        ['confDay', '=', $j],
                        ['eventID', '=', $event->eventID]
                    ])->first();
                    $y = Ticket::find($z->ticketID);

                    for ($x = 1; $x <= 5; $x++) {
                        if (request()->input('sess-' . $j . '-' . $x . '-' . $reg->regID)) {
                            // if this is set, the value is the session that was chosen.
                            // Create the RegSession record

                            $rs = new RegSession;
                            $rs->regID = $reg->regID;
                            $rs->personID = $reg->personID;
                            $rs->eventID = $event->eventID;
                            $rs->confDay = $j;
                            $rs->sessionID = request()->input('sess-' . $j . '-' . $x . '-' . $reg->regID);
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

        $x = compact(
            'needSessionPick',
            'event',
            'quantity',
            'loc',
            'rf',
            'person',
            'prefixes',
            'industries',
            'org'
        );

        $receipt_filename = $rf->eventID . "/" . $rf->confirmation . ".pdf";
        try {
            $pdf = PDF::loadView('v1.public_pages.event_receipt', $x);
        } catch (\Exception $exception) {
            request()->session()->flash('alert-warning', trans('messages.errors.no_receipt'));
        }

        Flysystem::connection('s3_receipts')->put(
            $receipt_filename,
            $pdf->output(),
            ['visibility' => AdapterInterface::VISIBILITY_PUBLIC]
        );

        $client = new S3Client([
            'credentials' => [
                'key' => env('AWS_KEY'),
                'secret' => env('AWS_SECRET')
            ],
            'region' => env('AWS_REGION'),
            'version' => 'latest',
        ]);

        $adapter = new AwsS3Adapter($client, env('AWS_BUCKET2'));
        $s3fs = new Filesystem($adapter);
        $event_pdf = $s3fs->getAdapter()->getClient()->getObjectUrl(env('AWS_BUCKET2'), $receipt_filename);

        try {
            // Consider turning this into a notification instead for reliability.
            $person->notify(new ReceiptNotification($rf, $event_pdf));
            // Mail::to($user->login)->send(new EventReceipt($rf, $event_pdf, $x));
        } catch (\Exception $exception) {
            request()->session()->flash('alert-danger', trans('messages.reg_status.mail_broken', ['org' => $org->orgName]));
        }

        return redirect(env('APP_URL').'/show_receipt/' . $rf->regID);
    }

    public function update_payment(Request $request, Registration $reg, RegFinance $rf)
    {
        $now = Carbon::now();
        if ($request->input('Cash')) {
            $pmt = 'cash';
        } else {
            $pmt = 'check';
        }

        $rf->pmtRecd = 1;
        $rf->pmtType = strtolower($pmt);
        $rf->status = 'processed';
        $rf->cancelDate = $now;
        $rf->updaterID = Auth()->user()->id;
        $rf->save();

        $reg->regStatus = 'processed';
        $reg->updaterID = Auth()->user()->id;
        $reg->updateDate = $now;
        $reg->save();

        $reg->ticket->update_count(1, $reg->ticket->waitlisting());

        return Redirect::back();
    }

    public function group_reg1(Request $request)
    {
        $this->currentPerson = Person::find(auth()->user()->id);
        $eventID = request()->input('eventID');
        $seats = 0;
        $total_cost = 0;
        $total_orig = 0;
        $total_handle = 0;
        $today = Carbon::now();

        // Create the stub reg-finance record
        $rf = new RegFinance;
        $rf->personID = $this->currentPerson->personID;
        $rf->eventID = $eventID;
        // Tickets & discount codes do not apply to the reg-finance record
        // $rf->ticketID = $tr;
        // $rf->discountCode = $cr;
        $rf->save();

        $check = request()->input('check');
        // Process up to 15 event-registration entries
        for ($i = 1; $i <= 15; $i++) {
            $personID = request()->input('person-' . $i);
            $firstName = request()->input('firstName-' . $i);
            $lastName = request()->input('lastName-' . $i);
            $email = request()->input('email-' . $i);
            $pmiid = request()->input('pmiid-' . $i);
            $ticketID = request()->input('ticketID-' . $i);
            $code = request()->input('code-' . $i);
            $override = request()->input('override-' . $i);
            $checkin = request()->input('checkin-' . $i);
            if ($code === null || $code == " ") {
                $code = 'N/A';
            }
            if ($personID === null && $firstName !== null) {
                // Perform a quick search to determine if this is a resubmit

                $e = Email::where('emailADDR', $email)->first();
                if ($e) {
                    $p = Person::find($e->personID);
                } else {
                    // create requisite records: person, orgperson
                    $p = new Person;
                    $p->firstName = $firstName;
                    $p->prefName = $firstName;
                    $p->lastName = $lastName;
                    $p->defaultOrgID = $this->currentPerson->defaultOrgID;
                    $p->login = $email;
                    $p->creatorID = $this->currentPerson->personID;
                    $p->save();

                    $u = new User;
                    $u->id = $p->personID;
                    $u->login = $email;
                    $u->name  = $email;
                    $u->email = $email;
                    $u->save();

                    $op = new OrgPerson;
                    $op->personID = $p->personID;
                    $op->orgID = $p->defaultOrgID;
                    $op->OrgStat1 = $pmiid;
                    $op->save();

                    $p->defaultOrgPersonID = $op->id;
                    $p->save();

                    $e = new Email;
                    $e->personID = $p->personID;
                    $e->emailADDR = $email;
                    $e->save();
                }
            } else {
                // get the person record from $personID
                $p = Person::find($personID);
            }

            // Create a registration record for each attendee

            $handle = 0;
            if ($p) {
                // Setup variables for valid attendee
                $t = Ticket::find($ticketID);
                // tr: ticket remember
                $tr = $t->ticketID;
                // cr: code remember
                $cr = $code;
                $seats++;

                $reg = new Registration;
                $reg->rfID = $rf->regID;
                $reg->eventID = $eventID;
                $reg->ticketID = $ticketID;
                $reg->personID = $p->personID;
                if ($p->allergenInfo) {
                    $reg->allergenInfo = $p->allergenInfo;
                }
                $reg->registeredBy = $this->currentPerson->showFullName();
                $reg->discountCode = $code;
                if ($pmiid) {
                    $reg->membership = 'member';
                } else {
                    $reg->membership = 'nonmbr';
                }
                $reg->token = request()->input('_token');
                $reg->creatorID = $this->currentPerson->personID;
                if ($p->affiliation) {
                    $reg->affiliation = $p->affiliation;
                }
                // Defaulting to No for explicit user agreement
                // Add to orgperson / profile
                $reg->canNetwork = 0;
                $reg->isAuthPDU = 0;
                // origcost
                // subtotal
                if ($t->earlyBirdEndDate !== null && $today->lte($t->earlyBirdEndDate)) {
                    // Use earlybird discount pricing as base
                    if ($reg->membership == 'member') {
                        $reg->origcost = $t->memberBasePrice;
                        $reg->subtotal = $t->memberBasePrice - ($t->memberBasePrice * $t->earlyBirdPercent / 100);
                    } else {
                        $reg->origcost = $t->nonmbrBasePrice;
                        $reg->subtotal = $t->nonmbrBasePrice - ($t->nonmbrBasePrice * $t->earlyBirdPercent / 100);
                    }
                } else {
                    // Use non-discount pricing
                    if ($reg->membership == 'member') {
                        $reg->origcost = $t->memberBasePrice;
                        $reg->subtotal = $t->memberBasePrice;
                    } else {
                        $reg->origcost = $t->nonmbrBasePrice;
                        $reg->subtotal = $t->nonmbrBasePrice;
                    }
                }
                if (isset($override)) {
                    $reg->subtotal = $override;
                }
                if ($code) {
                    $dCode = EventDiscount::where([
                        ['eventID', $eventID],
                        ['discountCODE', $code]
                    ])->first();
                    if (null !== $dCode && $dCode->percent > 0) {
                        $reg->subtotal = $reg->subtotal - ($reg->subtotal * $dCode->percent / 100);
                    } else {
                        $reg->subtotal = $reg->subtotal - $dCode->flatAmt;
                    }
                    if ($reg->subtotal < 0) {
                        $reg->subtotal = 0;
                    }
                    if (preg_match("/speaker/i", $code)) {
                        $p->add_speaker_role();
                    }
                }
                $reg->regStatus = 'progress';
                $reg->save();
                if ($check) {
                    $reg->checkin();
                }
                $total_orig = $total_orig + $reg->origcost;
                $total_cost = $total_cost + $reg->subtotal;
                $handle = $reg->subtotal * 0.029;
                if ($handle > 5) {
                    $handle = 5;
                }
                $total_handle = $total_handle + $handle;
                $reg_save = $reg->regID;
            }
        }
        // Update the regfinance record for all of the attendees
        // Show a group receipt
        $rf->seats = $seats;
        $rf->personID = $this->currentPerson->personID;
        $rf->cost = $total_cost;
        $rf->status = 'progress';
        $rf->handleFee = $total_handle;
        $rf->token = request()->input('_token');
        $rf->save();
        return redirect('/groupreg/' . $rf->regID);
    }

    public function group_reg2(Request $request, $id)
    {
        // responds to PATCH /group_reg2/{rf}

        $rf = RegFinance::where('regID', '=', $id)->with('registrations')->first();
        $event = Event::find($rf->eventID);
        $org = Org::find($event->orgID);
        $user = User::find($rf->personID);
        $person = Person::find($rf->personID);
        $prefixes = DB::table('prefixes')->get();
        $industries = DB::table('industries')->get();
        $loc = Location::find($event->locationID);
        $quantity = $rf->seats;
        $this->currentPerson = Person::find(auth()->user()->id);
        $stripeToken = $request->input('stripeToken');
        $total_handle = 0;

        // user can hit "at door" or "credit" buttons.
        // if the cost is $0, the pay button won't show on the form

        if ($rf->status != 'processed') {
            // if cost > $0 AND payment details were given ($stripeToken isset),
            // we need to check stripeToken, stripeEmail, stripeTokenType and record to user table
            if ($rf->cost > 0 && $stripeToken !== null) {
                $stripeEmail = $request->input('stripeEmail');
                $stripeTokenType = $request->input('stripeTokenType');
                Stripe::setApiKey(env('STRIPE_SECRET'));

                // Check if a customer id exists, and retrieve or create
                if (!$user->stripe_id) {
                    $customer = \Stripe\Customer::create(array(
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
                    'description' => "$org->orgName " . trans('messages.headers.reg') .": $event->eventName",
                    'customer' => $user->stripe_id,
                ));
                $rf->stripeChargeID = $charge->id;
                $rf->status = 'processed';
                $rf->pmtType = $stripeTokenType;
                $rf->pmtRecd = 1;
            } elseif ($rf->cost > 0) {
                // cost > 0 and the 'Pay at Door' button was pressed
                $rf->status = 'pending';
                $rf->pmtType = 'door';
            } else {
                $rf->pmtRecd = 1;
                $rf->status = 'processed';
                $rf->pmtType = 'free';
            }

            $discountAmt = 0;
            $handleFee = 0;

            // update $rf record and each $reg record status
            foreach ($rf->registrations as $reg) {
                $reg->regStatus = 'processed';
                $reg->updaterID = auth()->user()->id;

                // Update ticket purchase on all bundle ticket members by $rf->seat
                $ticket = Ticket::find($reg->ticketID);
                $ticket->update_count(1, 0);

                if ($reg->subtotal > 0 || $reg->origcost > 0) {
                    // mCentric Handle fee = 2.9% of $rf->cost + $0.30
                    $reg->subtotal > 0 ? $cost = $reg->subtotal : $cost = $reg->origcost;
                    $handleFee = number_format(($cost * .029) + .30, 2, '.', '');
                    // capped at $5.00
                    if ($handleFee > 5) {
                        $handleFee = number_format(5, 2, '.', '');
                    }
                    $reg->mcentricFee = $handleFee;
                    $total_handle += $handleFee;
                }
                $reg->save();
                $discountAmt += ($reg->origcost - $reg->subtotal - $handleFee);
            }
            // Confirmation code is:  personID-regID-seats
            $rf->confirmation = $this->currentPerson->personID . "-" . $rf->regID . "-" . $rf->seats;
            if ($discountAmt < 0) {
                $discountAmt = 0;
            }

            // Need to set ccFee if cost > $0 and other fees regardless of the cost
            if ($rf->cost > 0) {
                // Stripe ccFee = 2.9% of $rf->cost + $0.30, no cap
                $rf->ccFee = number_format(($rf->cost * .029) + .30, 2, '.', ',');

                // mCentric Handle fee = 2.9% of $rf->cost + $0.30 capped at $5.00
                $rf->handleFee = $total_handle;
                $rf->orgAmt = number_format($rf->cost - $rf->ccFee - $rf->handleFee, 2, '.', '');
                $rf->discountAmt = number_format($discountAmt, 2, '.', '');
            } else {
                $rf->handleFee = $total_handle;
                $rf->orgAmt = number_format($rf->cost - $rf->ccFee - $rf->handleFee, 2, '.', '');
                $rf->discountAmt = number_format($discountAmt, 2, '.', '');
            }
            // fees above are already $0 unless changed so save.
            $rf->isGroupReg = 1;
            $rf->save();
        }

        $x = compact('event', 'quantity', 'loc', 'rf', 'person', 'prefixes', 'industries', 'org');

        $receipt_filename = $rf->eventID . "/" . $rf->confirmation . ".pdf";
        $pdf = PDF::loadView('v1.auth_pages.events.registration.group_receipt', $x)
            ->setOption('disable-javascript', false)
            ->setOption('javascript-delay', 20)
            ->setOption('encoding', 'utf-8');

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

        $adapter = new AwsS3Adapter($client, env('AWS_BUCKET2'));
        $s3fs = new Filesystem($adapter);
        $event_pdf = $s3fs->getAdapter()->getClient()->getObjectUrl(env('AWS_BUCKET2'), $receipt_filename);

        // Mail will need to INSTEAD go to each of the persons attached to Registration records
        try {
            $person->notify(new ReceiptNotification($rf, $event_pdf));
            // Mail::to($user->login)->send(new GroupEventReceipt($rf, $event_pdf, $x));
        } catch (\Exception $exception) {
            request()->session()->flash('alert-danger', trans('messages.reg_status.mail_broken'));
            //$person->notify(new ReceiptNotification($rf, $event_pdf));
        }

        //return view('v1.auth_pages.events.registration.group_receipt', $x);
        return redirect('/show_receipt/' . $rf->regID);
    }

    public function show_group_receipt(RegFinance $rf)
    {
        $quantity = $rf->seats;
        $event = Event::find($rf->eventID);
        $loc = Location::find($event->locationID);
        $person = Person::find($rf->personID);
        $org = Org::find($event->orgID);

        return view('v1.auth_pages.events.registration.group_receipt_authnav', compact('event', 'quantity', 'loc', 'rf', 'person', 'org'));
    }

    public function destroy($id)
    {
        // responds to DELETE /blah/id
    }
}
