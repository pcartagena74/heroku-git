<?php

namespace App\Http\Controllers;

use App\Org;
use App\Person;
use App\OrgPerson;
use App\Registration;
use App\RegSession;
use App\User;
use Illuminate\Http\Request;
use App\Ticket;
use App\Event;
use App\Email;
use App\RegFinance;
use App\Track;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use App\EventSession;

class RegistrationController extends Controller
{
    public function __construct () {
        $this->middleware('auth', ['except' => ['showRegForm', 'store', 'update', 'processRegForm']]);
    }

    public function index () {
        // responds to /blah
    }

    public function processRegForm (Request $request, Event $event) {
        // Initiating registration for an event from /event/{id}
        $ticket        = Ticket::find(request()->input('ticketID'));
        $quantity      = request()->input('quantity');
        $discount_code = request()->input('discount_code');

        if($discount_code === null) {
            $discount_code = '';
        } else {
            $discount_code = "/" . $discount_code;
        }

        // Finish the route variables needed here and add the route
        return redirect("/regstep2/$event->eventID/$ticket->ticketID/$quantity" . $discount_code);
    }

    public function showRegForm (Event $event, Ticket $ticket, $quantity, $discount_code = null) {
        //    public function showRegForm (Request $request, Event $event) {
        // Registering for an event from /register/{tkt}/{q}/{dCode?}
        /*
        $ticket        = Ticket::find(request()->input('ticketID'));
        $quantity      = request()->input('quantity');
        $discount_code = request()->input('discount_code');
        $event         = Event::find($ticket->eventID);
        */
        if($event->hasFood) {
            return view('v1.public_pages.register', compact('ticket', 'event', 'quantity', 'discount_code'));
        } else {
            return view('v1.public_pages.register-no-food', compact('ticket', 'event', 'quantity', 'discount_code'));
        }
    }

    public function show ($param) {

        $event = Event::where('eventID', '=', $param)
                      ->orWhere('slug', '=', $param)
                      ->firstOrFail();

        $regs = Registration::where('eventID', '=', $event->eventID)
                            ->where(function($q) {
                                $q->where('regStatus', '=', 'Active')
                                  ->orWhere('regStatus', '=', 'Processed');
                            })->with('regfinance', 'ticket')->get();

        $tkts = Ticket::where([
            ['eventID', '=', $event->eventID],
            ['isaBundle', '=', 0]
        ])->get();

        $discPie = DB::table('reg-finance')
                     ->select(DB::raw('discountCode, sum(seats) as cnt, sum(orgAmt) as orgAmt,
                                       sum(discountAmt) as discountAmt, sum(handleFee) as  handleFee,
                                       sum(ccFee) as ccFee, sum(cost) as cost'))
                     ->where([
                         ['eventID', '=', $event->eventID],
                         ['status', '!=', 'pending'],
                         ['status', '!=', 'In Progress'],
                         ['status', '!=', 'Cancelled'],
                         ['status', '!=', 'Canceled']
                     ])
                     ->whereNull('deleted_at')
                     ->groupBy('discountCode')
                     ->orderBy('cnt', 'desc')->get();

        foreach($discPie as $d) {
            if($d->discountCode == '' || $d->discountCode === null) {
                $d->discountCode = 'N/A';
            }
        }

        $total = DB::table('reg-finance')
                   ->select(DB::raw('"discountCode", sum(seats) as cnt, sum(orgAmt) as orgAmt,
                                       sum(discountAmt) as discountAmt, sum(handleFee) as  handleFee,
                                       sum(ccFee) as ccFee, sum(cost) as cost'))
                   ->where([
                       ['eventID', '=', $event->eventID],
                       ['status', '!=', 'pending'],
                       ['status', '!=', 'In Progress'],
                       ['status', '!=', 'Cancelled'],
                       ['status', '!=', 'Canceled']
                   ])
                   ->whereNull('deleted_at')
                   ->first();

        $total->discountCode = 'Total';

        $discPie->put(count($discPie), $total);

        $refs = RegFinance::where('eventID', '=', $event->eventID)->whereNotNull('cancelDate')->get();

        if($event->hasTracks) {
            $tracks = Track::where('eventID', $event->eventID)->get();
            return view('v1.auth_pages.events.event-rpt', compact('event', 'regs', 'tkts', 'refs', 'discPie', 'tracks'));
        } else {
            return view('v1.auth_pages.events.event-rpt', compact('event', 'regs', 'tkts', 'refs', 'discPie'));
        }
    }

    public function create () {
        // responds to /blah/create and shows add/edit form
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store (Request $request, Event $event) {
        // responds to POST to /regstep3/{event}/create and creates, adds, stores the event
        // check if someone logged in
        // if so, get person record and update
        // if not, check to see if email matches any person record and update
        // else add a person record and email record
        // record registration_form1 answers
        // LOOP if quantity > 1 and add new person records avoiding duplicates as possible
        // display registration_form2

        //$event    = Event::find(request()->input('eventID'));
        $resubmit = Registration::where('token', request()->input('_token'))->first();
        $quantity = request()->input('quantity');
        if(Auth::check()) {
            $this->currentPerson = Person::find(auth()->user()->id)->load('orgperson');
            //$this->currentPerson->load('orgperson');
        }
        $show_pass_fields = 0;

        $checkEmail = request()->input('login');

        $prefix        = ucwords(request()->input('prefix'));
        $firstName     = ucwords(request()->input('firstName'));
        $middleName    = ucwords(request()->input('middleName'));
        $lastName      = ucwords(request()->input('lastName'));
        $suffix        = ucwords(request()->input('suffix'));
        $prefName      = ucwords(request()->input('prefName'));
        $compName      = ucwords(request()->input('compName'));
        $indName       = ucwords(request()->input('indName'));
        $title         = ucwords(request()->input('title'));
        $eventQuestion = request()->input('eventQuestion');
        $eventTopics   = request()->input('eventTopics');
        $affiliation   = request()->input('affiliation');
        $flatamt       = request()->input('flatamt');
        $percent       = request()->input('percent');
        $dCode         = request()->input('discount_code');
        if($dCode === null || $dCode == " ") {
            $dCode = 'N/A';
        }
        $ticketID      = request()->input('ticketID');
        $t = Ticket::find($ticketID);
        $subtotal      = request()->input('sub1');
        $origcost      = request()->input('cost1');
        // strip out , from $ figure over $1,000
        $origcost      = str_replace(',', '', $origcost);
        if($event->hasFood) {
            $specialNeeds = request()->input('specialNeeds');
            $eventNotes   = request()->input('eventNotes');
            $allergenInfo = request()->input('allergenInfo');
            $cityState    = request()->input('cityState');
        }

        // put in some validation to ensure that nothing was tampered with
        // check $percent against whatever it should be based on submitted $dCode

        $total = request()->input('total');

        $subcheck = $subtotal;

        $email = Email::where('emailADDR', $checkEmail)->first();

        if(!Auth::check() && $email === null) {
            // Not logged in and email is not in database; must create
            $person               = new Person;
            $person->prefix       = $prefix;
            $person->firstName    = $firstName;
            $person->midName      = $middleName;
            $person->lastName     = $lastName;
            $person->suffix       = $suffix;
            $person->defaultOrgID = $event->orgID;
            $person->prefName     = $prefName;
            $person->compName     = $compName;
            $person->indName      = $indName;
            $person->title        = $title;
            $person->login        = $checkEmail;
            if($event->hasFood && $allergenInfo !== null) {
                $person->allergenInfo = implode(",", (array)$allergenInfo);
            }
            $person->affiliation = implode(",", $affiliation);
            $person->save();

            // Need to create a user record with new personID

            $user        = new User();
            $user->id    = $person->personID;
            $user->login = $checkEmail;
            $user->email = $checkEmail;
            $user->save();
            Auth::loginUsingId($user->id);
            $show_pass_fields = 1;
            // send email notification with password setting stuff

            $this->currentPerson = $person;

            $op           = new OrgPerson;
            $op->orgID    = $event->orgID;
            $op->personID = $person->personID;
            $op->save();

            $email            = new Email;
            $email->personID  = $person->personID;
            $email->emailADDR = $checkEmail;
            $email->isPrimary = 1;
            $email->save();

            $regBy  = $person->firstName . " " . $person->lastName;
            $regMem = 'Non-Member';

        } elseif(!Auth::check() && $email !== null) {
            // Not logged in and email is in the database;
            // Should force a login -- return to form with input saved.
            //dd('No one logged in but main email is in DB');
            request()->session()->flash('alert-warning',
                "You have an account that we've created for you. Please click the login button. 
             If you haven't yet set a password, we'll send one to your email address.");
            return back()->withInput();

        } elseif(Auth::check() && ($email->personID == $this->currentPerson->personID)) {
            // the email entered belongs to the person logged in; ergo in DB
            $person = $this->currentPerson;
            if($person->orgperson->OrgStat1 === null) {
                $regMem = 'Non-Member';
            } else {
                $regMem = 'Member';
            }
            $person->prefix = $prefix;
            // only non-members can edit first & last name
            if($regMem == 'Non-Member') {
                $person->firstName = $firstName;
            }
            if($middleName) {
                $person->midName = $middleName;
            }
            // only non-members can edit first & last name
            if($regMem == 'Non-Member') {
                $person->lastName = $lastName;
            }
            if($suffix) {
                $person->suffix = $suffix;
            }
            $person->defaultOrgID = $event->orgID;
            $person->prefName     = $prefName;
            if($compName) {
                $person->compName = $compName;
            }
            if($indName) {
                $person->indName = $indName;
            }
            if($title) {
                $person->title = $title;
            }
            if($event->hasFood) {
                if($allergenInfo) {
                    $person->allergenInfo = implode(",", (array)$allergenInfo);
                }
            }
            if($affiliation) {
                $person->affiliation = implode(",", $affiliation);
            }
            $person->save();

            $regBy = $person->firstName . " " . $person->lastName;

        } elseif(Auth::check() && ($email->personID != $this->currentPerson->personID)) {
            // someone logged in is registering for someone else in the DB (usually CAMI)
            $person = Person::find($email->personID);
            if($person->orgperson->OrgStat1 === null) {
                $regMem = 'Non-Member';
            } else {
                $regMem = 'Member';
            }
            $person->prefix = $prefix;
            // only non-members can edit first & last name
            if($regMem == 'Non-Member') {
                $person->firstName = $firstName;
            }
            if($middleName) {
                $person->midName = $middleName;
            }
            // only non-members can edit first & last name
            if($regMem == 'Non-Member') {
                $person->lastName = $lastName;
            }
            if($suffix) {
                $person->suffix = $suffix;
            }
            $person->defaultOrgID = $event->orgID;
            $person->prefName     = $prefName;
            if($compName) {
                $person->compName = $compName;
            }
            if($indName) {
                $person->indName = $indName;
            }
            if($title) {
                $person->title = $title;
            }
            if($event->hasFood) {
                if($allergenInfo) {
                    $person->allergenInfo = implode(",", (array)$allergenInfo);
                }
            }
            if($affiliation) {
                $person->affiliation = implode(",", $affiliation);
            }
            $person->save();

        } else {
            // someone logged in is registering for someone else NOT in the DB
            $person               = new Person;
            $person->prefix       = $prefix;
            $person->firstName    = $firstName;
            $person->midName      = $middleName;
            $person->lastName     = $lastName;
            $person->suffix       = $suffix;
            $person->defaultOrgID = $event->orgID;
            $person->prefName     = $prefName;
            $person->compName     = $compName;
            $person->indName      = $indName;
            $person->title        = $title;
            if($event->hasFood) {
                $person->allergenInfo = implode(",", (array)$allergenInfo);
            }
            $person->affiliation = implode(",", $affiliation);
            $person->creatorID   = $this->currentPerson->personID;
            $person->updaterID   = $this->currentPerson->personID;
            $person->save();

            // Need to create a user record with new personID

            $user        = new User();
            $user->id    = $person->personID;
            $user->login = $checkEmail;
            $user->email = $checkEmail;
            $user->save();
            $show_pass_fields = 1;

            $op           = new OrgPerson;
            $op->orgID    = $event->orgID;
            $op->personID = $person->personID;
            $op->save();

            $email            = new Email;
            $email->personID  = $person->personID;
            $email->emailADDR = $checkEmail;
            $email->isPrimary = 1;
            $email->save();

            $regBy  = $this->currentPerson->firstName . " " . $this->currentPerson->lastName;
            $regMem = 'Non-Member';
        }

        $reg                   = new Registration;
        $reg->eventID          = $event->eventID;
        $reg->ticketID         = request()->input('ticketID');
        $reg->personID         = $person->personID;
        $reg->reportedIndustry = $indName;
        $reg->eventTopics      = $eventTopics;
        $reg->isFirstEvent     = request()->input('isFirstEvent') !== null ? 1 : 0;
        $reg->isAuthPDU        = request()->input('isAuthPDU') !== null ? 1 : 0;
        $reg->eventQuestion    = $eventQuestion;
        $reg->canNetwork       = request()->input('canNetwork') !== null ? 1 : 0;
        $reg->affiliation      = implode(",", $affiliation);
        $reg->regStatus        = 'In Progress';
        if($t->waitlisting()) {
            $reg->regStatus        = 'Wait List';
        }
        $reg->registeredBy     = $regBy;
        $reg->token            = request()->input('_token');
        $reg->subtotal         = $subtotal;
        $reg->discountCode     = $dCode;
        $reg->origcost         = $origcost;
        $reg->membership       = $regMem;
        if($event->hasFood) {
            $reg->specialNeeds = $specialNeeds;
            $reg->allergenInfo = implode(",", (array)$allergenInfo);
            $reg->cityState    = $cityState;
            $reg->eventNotes   = $eventNotes;
        }
        $reg->save();

        // ----------------------------------------------------------

        for($i = 2; $i <= $quantity; $i++) {

            $prefix        = ucwords(request()->input('prefix' . "_$i"));
            $firstName     = ucwords(request()->input('firstName' . "_$i"));
            $middleName    = ucwords(request()->input('middleName' . "_$i"));
            $lastName      = ucwords(request()->input('lastName' . "_$i"));
            $suffix        = ucwords(request()->input('suffix' . "_$i"));
            $prefName      = ucwords(request()->input('prefName' . "_$i"));
            $compName      = ucwords(request()->input('compName' . "_$i"));
            $indName       = ucwords(request()->input('indName' . "_$i"));
            $title         = ucwords(request()->input('title' . "_$i"));
            $eventQuestion = request()->input('eventQuestion' . "_$i");
            $eventTopics   = request()->input('eventTopics' . "_$i");
            $checkEmail    = request()->input('login' . "_$i");
            $subtotal      = request()->input('sub' . $i);
            $origcost      = request()->input('cost' . $i);

            if($event->hasFood) {
                $specialNeeds = request()->input('specialNeeds' . "_$i");
                $eventNotes   = request()->input('eventNotes' . "_$i");
                $allergenInfo = request()->input('allergenInfo' . "_$i");
                $cityState    = request()->input('cityState' . "_$i");
            }

            $subcheck += $subtotal;

            $email = Email::where('emailADDR', $checkEmail)->first();

            // Someone IS going to be logged in; the first person

            if(Auth::check() && $email === null) {
                // Someone logged in and email is not in database; must create
                $person               = new Person;
                $person->prefix       = $prefix;
                $person->firstName    = $firstName;
                $person->midName      = $middleName;
                $person->lastName     = $lastName;
                $person->suffix       = $suffix;
                $person->defaultOrgID = $event->orgID;
                $person->prefName     = $prefName;
                $person->compName     = $compName;
                $person->indName      = $indName;
                $person->title        = $title;
                $person->login        = $checkEmail;
                if($event->hasFood) {
                    $person->allergenInfo = implode(",", (array)$allergenInfo);
                }
                $person->affiliation = implode(",", $affiliation);
                $person->save();

                $op           = new OrgPerson;
                $op->orgID    = $event->orgID;
                $op->personID = $person->personID;
                $op->save();

                // Need to create a user record with new personID

                $user        = new User();
                $user->id    = $person->personID;
                $user->login = $checkEmail;
                $user->email = $checkEmail;
                $user->save();

                // need to send a notification to the new user re: password setting

                $email            = new Email;
                $email->personID  = $person->personID;
                $email->emailADDR = $checkEmail;
                $email->isPrimary = 1;
                $email->save();

                $regBy  = $person->firstName . " " . $person->lastName;
                $regMem = 'Non-Member';

            } elseif(Auth::check() && ($email->personID == $this->currentPerson->personID)) {
                // the email entered belongs to the person logged in; ergo in DB
                // addresses #2 - whatever should NOT be the same as the first
                $person = $this->currentPerson;
                if($person->orgperson->OrgStat1 === null) {
                    $regMem = 'Non-Member';
                } else {
                    $regMem = 'Member';
                }
                $person->prefix = $prefix;
                // only non-members can edit first & last name
                if($regMem == 'Non-Member') {
                    $person->firstName = $firstName;
                }
                if($middleName) {
                    $person->midName = $middleName;
                }
                // only non-members can edit first & last name
                if($regMem == 'Non-Member') {
                    $person->lastName = $lastName;
                }
                if($suffix) {
                    $person->suffix = $suffix;
                }
                $person->defaultOrgID = $event->orgID;
                $person->prefName     = $prefName;
                if($compName) {
                    $person->compName = $compName;
                }
                if($indName) {
                    $person->indName = $indName;
                }
                if($title) {
                    $person->title = $title;
                }
                if($event->hasFood) {
                    if($allergenInfo) {
                        $person->allergenInfo = implode(",", (array)$allergenInfo);
                    }
                }
                if($affiliation) {
                    $person->affiliation = implode(",", $affiliation);
                }
                $person->save();

            } elseif(Auth::check() && ($email->personID != $this->currentPerson->personID)) {
                // someone logged in is registering someone else in the DB (usually CAMI)
                $person = Person::find($email->personID);
                if($person->orgperson->OrgStat1 === null) {
                    $regMem = 'Non-Member';
                } else {
                    $regMem = 'Member';
                }
                $person->prefix = $prefix;
                // only non-members can edit first & last name
                if($regMem == 'Non-Member') {
                    $person->firstName = $firstName;
                }
                if($middleName) {
                    $person->midName = $middleName;
                }
                // only non-members can edit first & last name
                if($regMem == 'Non-Member') {
                    $person->lastName = $lastName;
                }
                if($suffix) {
                    $person->suffix = $suffix;
                }
                $person->defaultOrgID = $event->orgID;
                $person->prefName     = $prefName;
                if($compName) {
                    $person->compName = $compName;
                }
                if($indName) {
                    $person->indName = $indName;
                }
                if($title) {
                    $person->title = $title;
                }
                if($event->hasFood) {
                    if($allergenInfo) {
                        $person->allergenInfo = implode(",", (array)$allergenInfo);
                    }
                }
                if($affiliation) {
                    $person->affiliation = implode(",", $affiliation);
                }
                $person->save();

            } else {
                // this is a rehash of the first option
                dd("shouldn't have gotten here");
            }
            if($dCode === null || $dCode = " ") {
                $dCode = 'N/A';
            }

            $reg                   = new Registration;
            $reg->eventID          = $event->eventID;
            $reg->ticketID         = request()->input('ticketID');
            $reg->personID         = $person->personID;
            $reg->reportedIndustry = $indName;
            $reg->eventTopics      = $eventTopics;
            $reg->isFirstEvent     = request()->input('isFirstEvent') !== null ? 1 : 0;
            $reg->isAuthPDU        = request()->input('isAuthPDU') !== null ? 1 : 0;
            $reg->eventQuestion    = $eventQuestion;
            $reg->canNetwork       = request()->input('canNetwork') !== null ? 1 : 0;
            $reg->affiliation      = implode(",", $affiliation);
            $reg->regStatus        = 'In Progress';
            if($t->waitlisting()){
                $reg->regStatus        = 'Wait List';
            }
            $reg->registeredBy     = $regBy;
            $reg->discountCode     = $dCode;
            $reg->token            = request()->input('_token');
            $reg->subtotal         = $subtotal;
            $reg->origcost         = $origcost;
            $reg->membership       = $regMem;
            if($event->hasFood) {
                $reg->cityState    = $cityState;
                $reg->allergenInfo = implode(",", (array)$allergenInfo);
                $reg->specialNeeds = $specialNeeds;
                $reg->eventNotes   = $eventNotes;
            }
            $reg->save();
        }

        // ----------------------------------------------------------
        if($subcheck != $total) {
            request()->session()->flash('alert-warning', "Something funky happened with the math. 
                Don't hack the form!  subcheck: $subcheck, total: $total");
            return Redirect::back()->withErrors();
//                ['warning' => "Something funky happened with the math.  Don't hack the form!  subcheck: $subcheck, total: $total"]);
        } else {

            $rf               = new RegFinance;
            $rf->regID        = $reg->regID;
            $rf->creatorID    = $this->currentPerson->personID;
            $rf->updaterID    = $this->currentPerson->personID;
            $rf->personID     = $this->currentPerson->personID;
            $rf->ticketID     = $ticketID;
            $rf->eventID      = $event->eventID;
            $rf->seats        = $quantity;
            $rf->cost         = $total;
            $rf->discountCode = $dCode;
            $rf->token        = request()->input('_token');
            if($flatamt > 0) {
                $rf->discountAmt = $flatamt;
            } else {
                $rf->discountAmt = $total - $subcheck;
            }
            Auth::check() ? $rf->creatorID = auth()->user()->id : $rf->creatorID = 1;
            Auth::check() ? $rf->updaterID = auth()->user()->id : $rf->updaterID = 1;
            if($t->waitlisting()){
                $rf->status       = 'Wait List';
            }
            $rf->save();

            // Everything is saved and updated and such, now display the data back for review
            return redirect('/confirm_registration/' . $reg->regID);
        }
    }

    public function edit ($id) {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update (Request $request, Registration $reg) {
        // responds to PATCH /blah/id
        // This is the person record of the registration
        $person = Person::find($reg->personID);

        if(auth()->check()) {
            $updater = auth()->user()->id;
        } else {
            $updater = 1;
        }

        $name = request()->input('name');
        if(strpos($name, '-')) {
            // when passed from the registration receipt, the $name will have a dash
            list($name, $field) = array_pad(explode("-", $name, 2), 2, null);
        }
        $value = request()->input('value');

        // Because allergenInfo and Industry are reported in registrations and saved to the profile...
        if($name == 'allergenInfo' && $value !== null) {
            $value                = implode(",", (array)$value);
            $person->allergenInfo = $value;
            $person->updaterID    = $updater;
            $person->save;
        } elseif($name == 'indName') {
            $person->indName   = $value;
            $person->updaterID = $updater;
            $person->save;
        }

        //$person            = Person::find($reg->personID);
        $reg->{$name}   = $value;
        $reg->updaterID = $updater;
        $reg->save();
    }

    public function destroy (Registration $reg, RegFinance $rf) {
        // responds to DELETE /cancel_registration/{reg}/{rf}
        // 1. Takes $reg->regID and $rf->regID
        // 2. Determine if this is a full or partial refund (if at all)
        // 3. Decrement registration count on ticket(s), sessions as needed

        $needSessionPick = 0;
        $verb            = 'canceled';
        $event           = Event::find($reg->eventID);
        $org             = Org::find($event->orgID);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        if($reg->regStatus == 'pending') {
            // the registration was never finalized, sessions weren't picked, so delete
            $reg->delete();
        } elseif($reg->subtotal > 0 && $rf->stripeChargeID) {
            // There's a refund that needs to occur with Stripe
            if($reg->subtotal == $rf->cost) {
                // This is a total refund
                try {
                    \Stripe\Refund::create(array(
                        "charge" => $rf->stripeChargeID,
                    ));
                    $reg->regStatus = 'Refunded';
                    $rf->status     = 'Refunded';
                    $rf->save();
                    $reg->save();

                    // Generate Refund Email

                } catch(Exception $e) {
                    request()->session()->flash('alert-danger', 'The attempt to get a refund failed. ' . $org->adminContactStatement);
                }
                $rf->delete();
                $reg->delete();
            } else {
                // This is a partial refund, so send the amount
                try {
                    \Stripe\Refund::create(array(
                        "charge" => $rf->stripeChargeID,
                        "amount" => $reg->subtotal * 100,
                    ));
                    $reg->regStatus = 'Refunded';
                    $rf->status     = 'Partially Refunded';
                    $verb           = 'refunded';
                    $rf->save();
                    $reg->save();

                    // Generate Refund Email

                } catch(\Exception $e) {
                    request()->session()->flash('alert-danger', 'The attempt to get a refund failed. ' . $org->adminContactStatement);
                }
                $reg->delete();
            }
        } elseif($rf->seats > 1) {
            $reg->regStatus = 'Canceled';
            $rf->status     = 'Partially Canceled';
            // decided against decrementing original seat count
            // $rf->seats      = $rf->seats - 1;
            $rf->save();
            $reg->save();
            $reg->delete();
            $verb = 'canceled';
        } else {
            $reg->regStatus = 'Canceled';
            $rf->status     = 'Canceled';
            $rf->save();
            $reg->save();
            $reg->delete();
            $rf->delete();
            $verb = 'canceled';
        }

        // Set a warning message to call the organization if there was an issue.
        if($reg->subtotal > 0 && $rf->stripeChargeID === null) {
            request()->session()->flash('alert-danger', 'The attempt to get a refund failed. ' . $org->adminContactStatement);
        }

        // Now, decrement registration counts where required
        $ticket = Ticket::find($reg->ticketID);

        // Check the tickets associated with this registration and see if there are sessions
        if($ticket->isaBundle) {
            $tickets = Ticket::join('bundle-ticket as bt', 'bt.ticketID', 'event-tickets.ticketID')
                             ->where([
                                 ['bt.bundleID', '=', $ticket->ticketID],
                                 ['event-tickets.eventID', '=', $reg->eventID]
                             ])
                             ->get();
        } else {
            // Collection of 1 ticket (when not a bundle) for code uniformity
            $tickets = Ticket::where('ticketID', '=', $rf->ticketID)->get();
        }

        // Decrement the regCount on the ticket if ticket was paid OR 'At Door'
        if($rf->pmtRecd || $rf->pmtType == 'At Door') {
            foreach($tickets as $t) {
                $t->regCount = $t->regCount - 1;
                $t->save();
            }

            $sessions = RegSession::where('regID', '=', $reg->regID)->get();
            foreach($sessions as $s) {
                $e = EventSession::find($s->sessionID);
                if($e->regCount > 0){
                    $e->regCount--;
                } $e->save();
                $s->delete();
            }
        }

        request()->session()->flash('alert-success', 'The registration with id ' . $reg->regID . ' has been ' . $verb);
        return redirect('/upcoming');
    }
}
