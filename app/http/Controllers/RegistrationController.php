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
use App\Notifications\SetYourPassword;

class RegistrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['showRegForm', 'store2', 'update', 'processRegForm']]);
    }

    public function index()
    {
        // responds to /blah
    }

    public function processRegForm(Request $request, Event $event)
    {
        // Initiating registration for an event from /event/{id}
        $discount_code = request()->input('discount_code');
        $tq = []; $quantity = 0;

        if ($discount_code === null) {
            $discount_code = '';
        } else {
            $discount_code = "/" . $discount_code;
        }

        if(1){
            $tkts = Ticket::where([
                ['eventID', '=', $event->eventID],
                ['isSuppressed', '=', 0],
                ['isDeleted', '=', 0]
            ])->get();

            foreach ($tkts as $ticket){
                $q = request()->input('q-' . $ticket->ticketID);
                if($q !== null && $q > 0){
                    array_push($tq, ['t' => $ticket->ticketID, 'q' => $q]);
                    $quantity += $q;
                }
            }

            $member = strtoupper(trans('messages.fields.member'));
            $nonmbr = strtoupper(trans('messages.fields.nonmbr'));
            return view('v1.public_pages.varTKT_register',
                   compact('event', 'discount_code', 'tkts', 'tq', 'member', 'nonmbr', 'quantity'));

            // return json_encode(['tq' => $tq]);

        } else {
            // This is no longer run given above
            /*
            $ticket = Ticket::find(request()->input('ticketID'));
            $quantity = request()->input('quantity');

            // Finish the route variables needed here and add the route
            return redirect("/regstep2/$event->eventID/$ticket->ticketID/$quantity/" . $discount_code);
            */
        }

    }

    public function showRegForm(Event $event, Ticket $ticket, $quantity, $discount_code = null) {
        //    public function showRegForm (Request $request, Event $event) {
        // Registering for an event from /register/{tkt}/{q}/{dCode?}
        /*
        $ticket        = Ticket::find(request()->input('ticketID'));
        $quantity      = request()->input('quantity');
        $discount_code = request()->input('discount_code');
        $event         = Event::find($ticket->eventID);
        */

        // NO LONGER IN USE

        $member = strtoupper(trans('messages.fields.member'));
        $nonmbr = strtoupper(trans('messages.fields.nonmbr'));

        $tkts = Ticket::where([
            ['eventID', '=', $event->eventID],
            ['isSuppressed', '=', 0]
        ])
        ->get();

        return view('v1.public_pages.register_new',
                    compact('ticket','event', 'quantity', 'discount_code', 'member', 'nonmbr', 'tkts'));
    }

    /**
     * Shows a report of registrations for a specific event
     *
     * @param $param : the slug or eventID for an event
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($param)
    {
        // Responds to GET /eventreport/{slug]

        try {
            $event = Event::when(filter_var($param, FILTER_VALIDATE_INT) !== false,
                function ($query) use ($param) {
                    return $query->where('eventID', $param);
                },
                function ($query) use ($param) {
                    return $query->where('slug', $param);
                }
            )->firstOrFail();
        } catch (\Exception $exception) {
            $message = "The event ID used is not valid.";
            return view('v1.public_pages.error_display', compact('message'));
        }

        // list of attendees who have registered, including payment pendings so they are listed everywhere needed
        $regs = Registration::where('eventID', '=', $event->eventID)
            ->where(function ($q) {
                $q->where('regStatus', '=', 'Active')
                    ->orWhere('regStatus', '=', 'Processed')
                    ->orWhere('regStatus', '=', 'Payment Pending');
            })->with('regfinance', 'ticket', 'person')->get();

        // list of attendees who are payment pendings so they are displayed separately
        $deadbeats = Registration::where([
            ['eventID', '=', $event->eventID],
            ['regStatus', '=', 'Payment Pending'],
            ])->with('regfinance', 'ticket')->get();

        // list of wait-listed or interrupted registrations
        $notregs = Registration::where('eventID', '=', $event->eventID)
            ->where(function ($q) {
                $q->where('regStatus', '=', 'Wait List')
                    ->orWhere('regStatus', '=', 'pending')
                    ->orWhere('regStatus', '=', 'In Progress');
            })->with('regfinance', 'ticket')->get();

        $tkts = Ticket::where([
            ['eventID', '=', $event->eventID],
            ['isaBundle', '=', 0]
        ])->get();

        $discPie = DB::table('event-registration')
            ->select(DB::raw('discountCode, count(discountCode) as cnt, sum(subtotal)-sum(ccFee)-sum(mcentricFee) as orgAmt,
                                    sum(origcost)-sum(subtotal) as discountAmt, sum(mcentricFee) as handleFee, 
                                    sum(ccFee) as ccFee, sum(subtotal) as cost'))
            ->where([
                ['eventID', '=', $event->eventID],
               // ['regStatus', '=', 'Processed']
            ])
            ->whereNull('deleted_at')
            ->groupBy('discountCode')
            ->orderBy('cnt', 'desc')->get();

        $discountCounts = DB::table('event-registration')
            ->select(DB::raw('discountCode, count(origcost) as cnt, sum(subtotal) as cost,
                                    sum(ccFee) as ccFee, sum(mcentricFee) as handleFee'))
            ->where([
                ['eventID', '=', $event->eventID],
               // ['regStatus', '=', 'Processed']
            ])
            ->whereNull('deleted_at')
            ->groupBy('discountCode')
            ->orderBy('cnt', 'desc')->get();

        foreach ($discPie as $d) {
            if ($d->discountCode == '' || $d->discountCode === null || $d->discountCode == '0') {
                $d->discountCode = 'N/A';
            }
        }

        $total = DB::table('event-registration')
            ->select(DB::raw('"discountCode", count(discountCode) as cnt, sum(subtotal)-sum(ccFee)-sum(mcentricFee) as orgAmt,
                                    sum(origcost)-sum(subtotal) as discountAmt, sum(mcentricFee) as handleFee, 
                                    sum(ccFee) as ccFee, sum(subtotal) as cost'))
            ->where([
                ['eventID', '=', $event->eventID],
               // ['regStatus', '=', 'Processed']
            ])
            ->whereNull('deleted_at')
            ->first();

        $total->discountCode = 'Total';

        $discPie->put(count($discPie), $total);

        $refunds = RegFinance::where('eventID', '=', $event->eventID)->whereNotNull('deleted_at')->get();

        if ($event->hasTracks) {
            $tracks = Track::where('eventID', $event->eventID)->get();
        } else {
            $tracks = null;
        }
        return view('v1.auth_pages.events.event-rpt', compact('event', 'regs', 'notregs', 'tkts', 'refunds',
                                                        'deadbeats', 'discPie', 'tracks', 'discountCounts'));
    }

    public function create()
    {
        // responds to /blah/create and shows add/edit form
    }

    /**
     * @param Request $request
     * @param Event $event
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */

    public function store2 (Request $request, Event $event) {

        $show_pass_fields = 0; $set_new_user = 0; $set_secondary_email = 0; $subcheck = 0; $sumtotal = 0;
        $quantity = request()->input('quantity');
        $total = request()->input('total');
        $token = request()->input('_token');
        $resubmit = RegFinance::where('token', $token)->first();

        if (Auth::check()) {
            $this->currentPerson = Person::find(auth()->user()->id)->load('orgperson');
            $authorID = $this->currentPerson->personID;
            $regBy = $this->currentPerson->firstName . " " . $this->currentPerson->lastName;
        } else {
            // No user logged in; checking to see if first email is in the database;
            // Should force a login -- return to form with input saved.  Assumptive RISK re: first
            $authorID = 1; $regBy = '';
            $email = request()->input('login');
            $chk = Email::where('emailADDR', '=', $email)->first();
            if(null !== $chk) {
                $p = Person::find($chk->personID);
                request()->session()->flash('alert-warning', trans('messages.instructions.login'));
                $p->notify(new SetYourPassword($p));
                return back()->withInput();
            }
        }

        // Create or re-open the stub reg-finance record
        if(null !== $resubmit){
            $rf = $resubmit;
            $resubmitted_regs = Registration::where('rfID', '=', $resubmit->regID)->get();
            // if there was a resubmit, delete the old registration records and redo later...
            foreach ($resubmitted_regs as $reg) {
                $reg->delete();
            }
            $resubmitted_regs = null;
        } else {
            $rf = new RegFinance();
            $resubmitted_regs = null;
        }
        // $rf->regID = $reg->regID;
        // $rf->ticketID = $ticketID;
        $rf->creatorID = $authorID;
        $rf->updaterID = $authorID;
        $rf->personID = $authorID;
        $rf->eventID = $event->eventID;
        $rf->seats = $quantity;
        $rf->token = $token;
        $rf->cost = $total;
        $rf->save();

        $tkts = Ticket::where([
            ['eventID', '=', $event->eventID],
            ['isSuppressed', '=', 0],
            ['isDeleted', '=', 0]
            ])->get();

        // Set $regBy to the first ticket's person info unless someone was already logged in
        if(null === $regBy){
            $firstName = ucwords(request()->input('firstName'));
            $lastName = ucwords(request()->input('lastName'));
            $regBy = $firstName . " " . $lastName;
        }

        // For each of the registrations
        for($i = 1; $i <= $quantity; $i++){
            $person = null; $set_new_user = 0;
            $change_to_member = 0;
            if($i>1){ $i_cnt = '_'.$i; } else { $i_cnt = ''; }

            // 1. Grab the passed variables for the person and registration info
            $prefix = ucwords(request()->input('prefix'.$i_cnt));
            $firstName = ucwords(request()->input('firstName'.$i_cnt));
            $middleName = ucwords(request()->input('middleName'.$i_cnt));
            $lastName = ucwords(request()->input('lastName'.$i_cnt));
            $login = request()->input('login'.$i_cnt);
            $pmiID = ucwords(request()->input('OrgStat1'.$i_cnt));
            $suffix = ucwords(request()->input('suffix'.$i_cnt));
            $prefName = ucwords(request()->input('prefName'.$i_cnt));
            $compName = ucwords(request()->input('compName'.$i_cnt));
            $indName = ucwords(request()->input('indName'.$i_cnt));
            $title = ucwords(request()->input('title'.$i_cnt));
            $chapterRole = ucwords(request()->input('chapterRole'.$i_cnt));
            $eventQuestion = request()->input('eventQuestion'.$i_cnt);
            $eventTopics = request()->input('eventTopics'.$i_cnt);
            $affiliation = request()->input('affiliation'.$i_cnt);
            $experience = request()->input('experience'.$i_cnt);
            $dCode = request()->input('discount_code'.$i_cnt);
            if ($dCode === null || $dCode == " ") { $dCode = 'N/A'; }
            $ticketID = request()->input('ticketID-'.$i);
            $t = Ticket::find($ticketID);
            $flatamt = request()->input('flatamt'.$i_cnt);
            $percent = request()->input('percent'.$i_cnt);
            $subtotal = request()->input('sub'.$i);
            $origcost = request()->input('cost'.$i);
            // strip out , from $ figure over $1,000
            $origcost = str_replace(',', '', $origcost);
            if ($event->hasFood) {
                $specialNeeds = request()->input('specialNeeds'.$i_cnt);
                $eventNotes = request()->input('eventNotes'.$i_cnt);
                $allergenInfo = request()->input('allergenInfo'.$i_cnt);
                $cityState = request()->input('cityState'.$i_cnt);
            }

            // Try to assign $p via OrgStat1
            $person = Person::whereHas('orgperson', function($q) use($pmiID) {
                $q->where('OrgStat1', '=', $pmiID);
            })->first();
            // If not set, try to assign $p via login (email address)
            if(null === $person) {
                $person = Person::whereHas('emails', function($q) use($login) {
                    $q->where('emailADDR', '=', $login);
                })->first();
            } else {
                // PMI ID was set; quick check to see if email should be a secondary
               if($person->login != $login) {
                   $set_secondary_email = 1;
               }
            }
            if(null === $person) {
                $person = new Person;
            }

            $subcheck += $subtotal;
            $sumtotal += $origcost;

            // We have either found the appropriate person record ($p) or have created a new one
            $person->prefix = $prefix;
            $person->firstName = $firstName;
            $person->midName = $middleName;
            $person->lastName = $lastName;
            $person->suffix = $suffix;
            $person->defaultOrgID = $event->orgID;
            $person->prefName = $prefName;
            $person->compName = $compName;
            $person->indName = $indName;
            $person->title = $title;
            $person->experience = $experience;
            $person->chapterRole = $chapterRole;
            $person->login = $login;
            if ($event->hasFood && $allergenInfo !== null) {
                $person->allergenInfo = implode(",", (array)$allergenInfo);
                $person->allergenNote = $eventNotes;
            }
            $person->affiliation = implode(",", (array)$affiliation);
            $person->save();

            if (null === $pmiID) {
                $regMem = trans('messages.fields.nonmbr');
            } else {
                $regMem = trans('messages.fields.member');
            }

            if($set_new_user) {
                $user = new User();
                $user->id = $person->personID;
                $user->login = $login;
                $user->email = $login;
                $user->save();
                if($i==1 && !Auth::check()) {
                    // log the first ticket's user in if no one is logged in -- ASSUMPTION RISK
                    Auth::loginUsingId($user->id);
                    $show_pass_fields = 1;
                }

                $op = new OrgPerson;
                $op->orgID = $event->orgID;
                $op->personID = $person->personID;
                if ($pmiID) {
                    $op->OrgStat1 = $pmiID;
                    $change_to_member = 1;
                }
                $op->save();

                $email = new Email;
                $email->personID = $person->personID;
                $email->emailADDR = $login;
                $email->isPrimary = 1;
                $email->save();
            } else {
                $op = OrgPerson::where([
                    ['personID', '=', $person->personID],
                    ['orgID', '=', $event->orgID]
                ])->first();
                // If not already a member and a PMI ID was provided, update and flag to change ticket price
                if(!$person->is_member($event->orgID) && $pmiID){
                    $op->OrgStat1 = $pmiID;
                    $op->updaterID = $person->personID;
                    $op->save();
                    $change_to_member = 1;
                }
            }

            if($set_secondary_email){
                $email = new Email;
                $email->personID = $person->personID;
                $email->emailADDR = $login;
                $email->save();
            }

            $reg = new Registration;
            $reg->rfID = $rf->regID;
            $reg->eventID = $event->eventID;
            $reg->ticketID = $ticketID;
            $reg->personID = $person->personID;
            $reg->reportedIndustry = $indName;
            $reg->eventTopics = $eventTopics;
            $reg->isFirstEvent = request()->input('isFirstEvent'.$i_cnt) !== null ? 1 : 0;
            $reg->isAuthPDU = request()->input('isAuthPDU'.$i_cnt) !== null ? 1 : 0;
            $reg->eventQuestion = $eventQuestion;
            $reg->canNetwork = request()->input('canNetwork'.$i_cnt) !== null ? 1 : 0;
            $reg->affiliation = implode(",", $affiliation);
            $reg->regStatus = 'In Progress';
            if ($t->waitlisting()) {
                $reg->regStatus = 'Wait List';
            }
            $reg->registeredBy = $regBy;
            $reg->token = $token;
            $reg->subtotal = $subtotal;
            $reg->discountCode = $dCode;
            $reg->origcost = $origcost;
            $reg->membership = $regMem;
            if ($event->hasFood) {
                $reg->specialNeeds = $specialNeeds;
                $reg->allergenInfo = implode(",", (array)$allergenInfo);
                $reg->cityState = $cityState;
                $reg->eventNotes = $eventNotes;
            }
            $reg->creatorID = $authorID;
            $reg->updaterID = $authorID;
            $reg->save();
        }

        if($subcheck == $total){
            $rf->discountAmt = $sumtotal - $subcheck;
            $rf->save();
        } else {
            request()->session()->flash('alert-warning', "Something funky happened with the math. 
                The form may have been inadvertantly corrupted. subtotal: $total, validation_check: $subcheck");
            return Redirect::back()->withErrors(
                ['warning' => "Something funky happened with the math.  Corruption occured: subcheck: $subcheck, subtotal: $total"]);
        }

        // Everything is saved and updated and such, now display the data back for review
        return redirect('/confirm_registration/' . $rf->regID);
    }

    public function store(Request $request, Event $event)
    {
        // responds to POST to /regstep3/{event}/create and creates, adds, stores the registration record(s)
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
        if (Auth::check()) {
            $this->currentPerson = Person::find(auth()->user()->id)->load('orgperson');
            //$this->currentPerson->load('orgperson');
        }
        $show_pass_fields = 0;

        $checkEmail = request()->input('login');

        $prefix = ucwords(request()->input('prefix'));
        $firstName = ucwords(request()->input('firstName'));
        $middleName = ucwords(request()->input('middleName'));
        $lastName = ucwords(request()->input('lastName'));
        $pmiID = ucwords(request()->input('OrgStat1'));
        $suffix = ucwords(request()->input('suffix'));
        $prefName = ucwords(request()->input('prefName'));
        $compName = ucwords(request()->input('compName'));
        $indName = ucwords(request()->input('indName'));
        $title = ucwords(request()->input('title'));
        $chapterRole = ucwords(request()->input('chapterRole'));
        $eventQuestion = request()->input('eventQuestion');
        $eventTopics = request()->input('eventTopics');
        $affiliation = request()->input('affiliation');
        $experience = request()->input('experience');
        $dCode = request()->input('discount_code');
        if ($dCode === null || $dCode == " ") {
            $dCode = 'N/A';
        }
        $ticketID = request()->input('ticketID');
        $t = Ticket::find($ticketID);
        $subtotal = request()->input('sub1');
        $origcost = request()->input('cost1');
        // strip out , from $ figure over $1,000
        $origcost = str_replace(',', '', $origcost);
        if ($event->hasFood) {
            $specialNeeds = request()->input('specialNeeds');
            $eventNotes = request()->input('eventNotes');
            $allergenInfo = request()->input('allergenInfo');
            $cityState = request()->input('cityState');
        }

        // put in some validation to ensure that nothing was tampered with
        // check $percent against whatever it should be based on submitted $dCode

        $total = request()->input('total');

        $subcheck = $subtotal;

        $email = Email::where('emailADDR', $checkEmail)->first();

        if (!Auth::check() && $email === null) {
            // Not logged in and email is not in database; must create
            $person = new Person;
            $person->prefix = $prefix;
            $person->firstName = $firstName;
            $person->midName = $middleName;
            $person->lastName = $lastName;
            $person->suffix = $suffix;
            $person->defaultOrgID = $event->orgID;
            $person->prefName = $prefName;
            $person->compName = $compName;
            $person->indName = $indName;
            $person->title = $title;
            $person->experience = $experience;
            $person->chapterRole = $chapterRole;
            $person->login = $checkEmail;
            if ($event->hasFood && $allergenInfo !== null) {
                $person->allergenInfo = implode(",", (array)$allergenInfo);
                $person->allergenNote = $eventNotes;
            }
            $person->affiliation = implode(",", $affiliation);
            $person->save();

            // Need to create a user record with new personID

            $user = new User();
            $user->id = $person->personID;
            $user->login = $checkEmail;
            $user->email = $checkEmail;
            $user->save();
            Auth::loginUsingId($user->id);
            $show_pass_fields = 1;
            // send email notification with password setting stuff

            $this->currentPerson = $person;

            $op = new OrgPerson;
            $op->orgID = $event->orgID;
            $op->personID = $person->personID;
            if ($pmiID) {
                $op->OrgStat1 = $pmiID;
            }
            $op->save();

            $email = new Email;
            $email->personID = $person->personID;
            $email->emailADDR = $checkEmail;
            $email->isPrimary = 1;
            $email->save();

            $regBy = $person->firstName . " " . $person->lastName;
            $regMem = trans('messages.fields.nonmbr');
        } elseif (!Auth::check() && $email !== null) {
            // Not logged in and email is in the database;
            // Should force a login -- return to form with input saved.
            //dd('No one logged in but main email is in DB');
            $person = Person::find($email->personID);
            request()->session()->flash('alert-warning', trans('messages.instructions.login'));
            $person->notify(new SetYourPassword($person));
            return back()->withInput();
        } elseif (Auth::check() && ($email->personID == $this->currentPerson->personID)) {
            // the email entered belongs to the person logged in; ergo in DB
            // This is where a change would be made for a provided PMI ID
            $person = $this->currentPerson;
            if ($person->orgperson->OrgStat1 === null) {
                $regMem = trans('messages.fields.nonmbr');
            } else {
                $regMem = trans('messages.fields.member');
            }
            $person->prefix = $prefix;
            // only non-members can edit first & last name
            if ($regMem == trans('messages.fields.nonmbr')) {
                $person->firstName = $firstName;
                $person->lastName = $lastName;
            }
            if ($middleName) {
                $person->midName = $middleName;
            }
            if ($suffix) {
                $person->suffix = $suffix;
            }
            $person->defaultOrgID = $event->orgID;
            $person->prefName = $prefName;
            if ($compName) {
                $person->compName = $compName;
            }
            if ($indName) {
                $person->indName = $indName;
            }
            if ($title) {
                $person->title = $title;
            }
            if ($experience) {
                $person->experience = $experience;
            }
            if ($chapterRole) {
                $person->chapterRole = $chapterRole;
            }
            if ($event->hasFood) {
                if ($allergenInfo) {
                    $person->allergenInfo = implode(",", (array)$allergenInfo);
                    $person->allergenNote = $eventNotes;
                }
            }
            if ($affiliation) {
                $person->affiliation = implode(",", $affiliation);
            }
            $person->save();

            $regBy = $person->firstName . " " . $person->lastName;
        } elseif (Auth::check() && ($email->personID != $this->currentPerson->personID)) {
            // someone logged in is registering for someone else in the DB (usually CAMI)

            // this is AFU b/c CAMI (or anyone else) wouldn't be able to put in first/last/email that isn't theirs
            // into first position of registration.

            $person = Person::find($email->personID);
            if ($person->orgperson->OrgStat1 === null) {
                $regMem = trans('messages.fields.nonmbr');
            } else {
                $regMem = trans('messages.fields.member');
            }
            $person->prefix = $prefix;
            // only non-members can edit first & last name
            if ($regMem == trans('messages.fields.nonmbr')) {
                $person->firstName = $firstName;
                $person->lastName = $lastName;
            }
            if ($middleName) {
                $person->midName = $middleName;
            }
            if ($suffix) {
                $person->suffix = $suffix;
            }
            $person->defaultOrgID = $event->orgID;
            $person->prefName = $prefName;
            if ($compName) {
                $person->compName = $compName;
            }
            if ($indName) {
                $person->indName = $indName;
            }
            if ($title) {
                $person->title = $title;
            }
            if ($experience) {
                $person->experience = $experience;
            }
            if ($chapterRole) {
                $person->chapterRole = $chapterRole;
            }
            if ($event->hasFood) {
                if ($allergenInfo) {
                    $person->allergenInfo = implode(",", (array)$allergenInfo);
                    $person->allergenNote = $eventNotes;
                }
            }
            if ($affiliation) {
                $person->affiliation = implode(",", $affiliation);
            }
            $person->save();
        } else {
            // someone logged in is registering for someone else NOT in the DB

            $person = new Person;
            $person->prefix = $prefix;
            $person->firstName = $firstName;
            $person->midName = $middleName;
            $person->lastName = $lastName;
            $person->suffix = $suffix;
            $person->defaultOrgID = $event->orgID;
            $person->prefName = $prefName;
            $person->compName = $compName;
            $person->indName = $indName;
            $person->title = $title;
            $person->experience = $experience;
            $person->chapterRole = $chapterRole;
            if ($event->hasFood) {
                $person->allergenInfo = implode(",", (array)$allergenInfo);
                $person->allergenNote = $eventNotes;
            }
            $person->affiliation = implode(",", $affiliation);
            $person->creatorID = $this->currentPerson->personID;
            $person->updaterID = $this->currentPerson->personID;
            $person->save();

            // Need to create a user record with new personID

            $user = new User();
            $user->id = $person->personID;
            $user->login = $checkEmail;
            $user->email = $checkEmail;
            $user->save();
            $show_pass_fields = 1;

            $op = new OrgPerson;
            $op->orgID = $event->orgID;
            $op->personID = $person->personID;
            if ($pmiID) {
                $op->OrgStat1 = $pmiID;
            }
            $op->save();

            $email = new Email;
            $email->personID = $person->personID;
            $email->emailADDR = $checkEmail;
            $email->isPrimary = 1;
            $email->save();

            $regBy = $this->currentPerson->firstName . " " . $this->currentPerson->lastName;
            $regMem = trans('messages.fields.nonmbr');
        }

        $reg = new Registration;
        $reg->eventID = $event->eventID;
        $reg->ticketID = request()->input('ticketID');
        $reg->personID = $person->personID;
        $reg->reportedIndustry = $indName;
        $reg->eventTopics = $eventTopics;
        $reg->isFirstEvent = request()->input('isFirstEvent') !== null ? 1 : 0;
        $reg->isAuthPDU = request()->input('isAuthPDU') !== null ? 1 : 0;
        $reg->eventQuestion = $eventQuestion;
        $reg->canNetwork = request()->input('canNetwork') !== null ? 1 : 0;
        $reg->affiliation = implode(",", $affiliation);
        $reg->regStatus = 'In Progress';
        if ($t->waitlisting()) {
            $reg->regStatus = 'Wait List';
        }
        $reg->registeredBy = $regBy;
        $reg->token = request()->input('_token');
        $reg->subtotal = $subtotal;
        $reg->discountCode = $dCode;
        $reg->origcost = $origcost;
        $reg->membership = $regMem;
        if ($event->hasFood) {
            $reg->specialNeeds = $specialNeeds;
            $reg->allergenInfo = implode(",", (array)$allergenInfo);
            $reg->cityState = $cityState;
            $reg->eventNotes = $eventNotes;
        }
        $reg->save();

        $start_reg = $reg->regID;

        // ----------------------------------------------------------

        for ($i = 2; $i <= $quantity; $i++) {
            $prefix = ucwords(request()->input('prefix' . "_$i"));
            $firstName = ucwords(request()->input('firstName' . "_$i"));
            $middleName = ucwords(request()->input('middleName' . "_$i"));
            $lastName = ucwords(request()->input('lastName' . "_$i"));
            $suffix = ucwords(request()->input('suffix' . "_$i"));
            $prefName = ucwords(request()->input('prefName' . "_$i"));
            $compName = ucwords(request()->input('compName' . "_$i"));
            $pmiID = ucwords(request()->input('OrgStat1' . "_$i"));
            $indName = ucwords(request()->input('indName' . "_$i"));
            $title = ucwords(request()->input('title' . "_$i"));
            $experience = ucwords(request()->input('experience' . "_$i"));
            $affiliation = request()->input('affiliation' . "_$i");
            $eventQuestion = request()->input('eventQuestion' . "_$i");
            $eventTopics = request()->input('eventTopics' . "_$i");
            $checkEmail = request()->input('login' . "_$i");
            $subtotal = request()->input('sub' . $i);
            $origcost = request()->input('cost' . $i);

            if ($event->hasFood) {
                $specialNeeds = request()->input('specialNeeds' . "_$i");
                $eventNotes = request()->input('eventNotes' . "_$i");
                $allergenInfo = request()->input('allergenInfo' . "_$i");
                $cityState = request()->input('cityState' . "_$i");
            }

            $subcheck += $subtotal;

            $email = Email::where('emailADDR', $checkEmail)->first();

            // Someone IS going to be logged in; the first person

            if (Auth::check() && $email === null) {
                // Someone logged in and email is not in database; must create
                $person = new Person;
                $person->prefix = $prefix;
                $person->firstName = $firstName;
                $person->midName = $middleName;
                $person->lastName = $lastName;
                $person->suffix = $suffix;
                $person->defaultOrgID = $event->orgID;
                $person->prefName = $prefName;
                $person->compName = $compName;
                $person->indName = $indName;
                $person->title = $title;
                $person->experience = $experience;
                $person->chapterRole = $chapterRole;
                $person->login = $checkEmail;
                if ($event->hasFood) {
                    $person->allergenInfo = implode(",", (array)$allergenInfo);
                    $person->allergenNote = $eventNotes;
                }
                $person->affiliation = implode(",", $affiliation);
                $person->save();

                $op = new OrgPerson;
                $op->orgID = $event->orgID;
                $op->personID = $person->personID;
                if ($pmiID) {
                    $op->OrgStat1 = $pmiID;
                }
                $op->save();

                // Need to create a user record with new personID

                $user = new User();
                $user->id = $person->personID;
                $user->login = $checkEmail;
                $user->email = $checkEmail;
                $user->save();

                // need to send a notification to the new user re: password setting

                $email = new Email;
                $email->personID = $person->personID;
                $email->emailADDR = $checkEmail;
                $email->isPrimary = 1;
                $email->save();

                $regBy = $person->firstName . " " . $person->lastName;
                $regMem = trans('messages.fields.nonmbr');
            } elseif (Auth::check() && ($email->personID == $this->currentPerson->personID)) {
                // the email entered belongs to the person logged in; ergo in DB
                // addresses #2 - whatever should NOT be the same as the first
                $person = $this->currentPerson;
                if ($person->orgperson->OrgStat1 === null) {
                    $regMem = trans('messages.fields.nonmbr');
                } else {
                    $regMem = trans('messages.fields.member');
                }
                $person->prefix = $prefix;
                // only non-members can edit first & last name
                if ($regMem == trans('messages.fields.nonmbr')) {
                    $person->firstName = $firstName;
                    $person->lastName = $lastName;
                }
                if ($middleName) {
                    $person->midName = $middleName;
                }
                if ($suffix) {
                    $person->suffix = $suffix;
                }
                $person->defaultOrgID = $event->orgID;
                $person->prefName = $prefName;
                if ($compName) {
                    $person->compName = $compName;
                }
                if ($indName) {
                    $person->indName = $indName;
                }
                if ($title) {
                    $person->title = $title;
                }
                if ($experience) {
                    $person->experience = $experience;
                }
                if ($chapterRole) {
                    $person->chapterRole = $chapterRole;
                }
                if ($event->hasFood) {
                    if ($allergenInfo) {
                        $person->allergenInfo = implode(",", (array)$allergenInfo);
                        $person->allergenNote = $eventNotes;
                    }
                }
                if ($affiliation) {
                    $person->affiliation = implode(",", $affiliation);
                }
                $person->save();
            } elseif (Auth::check() && ($email->personID != $this->currentPerson->personID)) {
                // someone logged in is registering someone else in the DB (usually CAMI)
                $person = Person::find($email->personID);
                if ($person->orgperson->OrgStat1 === null) {
                    $regMem = trans('messages.fields.nonmbr');
                } else {
                    $regMem = trans('messages.fields.member');
                }
                $person->prefix = $prefix;
                // only non-members can edit first & last name
                if ($regMem == trans('messages.fields.nonmbr')) {
                    $person->firstName = $firstName;
                    $person->lastName = $lastName;
                }
                if ($middleName) {
                    $person->midName = $middleName;
                }
                if ($suffix) {
                    $person->suffix = $suffix;
                }
                $person->defaultOrgID = $event->orgID;
                $person->prefName = $prefName;
                if ($compName) {
                    $person->compName = $compName;
                }
                if ($indName) {
                    $person->indName = $indName;
                }
                if ($title) {
                    $person->title = $title;
                }
                if ($experience) {
                    $person->experience = $experience;
                }
                if ($chapterRole) {
                    $person->chapterRole = $chapterRole;
                }
                if ($event->hasFood) {
                    if ($allergenInfo) {
                        $person->allergenInfo = implode(",", (array)$allergenInfo);
                        $person->allergenNote = $eventNotes;
                    }
                }
                if ($affiliation) {
                    $person->affiliation = implode(",", $affiliation);
                }
                $person->save();
            } else {
                // this is a rehash of the first option
                dd("shouldn't have gotten here");
            }
            if ($dCode === null || $dCode = " ") {
                $dCode = 'N/A';
            }

            $reg = new Registration;
            $reg->eventID = $event->eventID;
            $reg->ticketID = request()->input('ticketID');
            $reg->personID = $person->personID;
            $reg->reportedIndustry = $indName;
            $reg->eventTopics = $eventTopics;
            $reg->isFirstEvent = request()->input('isFirstEvent') !== null ? 1 : 0;
            $reg->isAuthPDU = request()->input('isAuthPDU') !== null ? 1 : 0;
            $reg->eventQuestion = $eventQuestion;
            $reg->canNetwork = request()->input('canNetwork') !== null ? 1 : 0;
            $reg->affiliation = implode(",", $affiliation);
            $reg->regStatus = 'In Progress';
            if ($t->waitlisting()) {
                $reg->regStatus = 'Wait List';
            }
            $reg->registeredBy = $regBy;
            $reg->discountCode = $dCode;
            $reg->token = request()->input('_token');
            $reg->subtotal = $subtotal;
            $reg->origcost = $origcost;
            $reg->membership = $regMem;
            if ($event->hasFood) {
                $reg->cityState = $cityState;
                $reg->allergenInfo = implode(",", (array)$allergenInfo);
                $reg->specialNeeds = $specialNeeds;
                $reg->eventNotes = $eventNotes;
            }
            $reg->save();
        }

        // ----------------------------------------------------------
        if ($subcheck != $total) {
            request()->session()->flash('alert-warning', "Something funky happened with the math. 
                The form may have been inadvertantly hacked.  subcheck: $subcheck, total: $total");
            return Redirect::back()->withErrors();
//                ['warning' => "Something funky happened with the math.  Don't hack the form!  subcheck: $subcheck, total: $total"]);
        } else {
            $rf = new RegFinance;
            $rf->regID = $reg->regID;
            $rf->creatorID = $this->currentPerson->personID;
            $rf->updaterID = $this->currentPerson->personID;
            $rf->personID = $this->currentPerson->personID;
            $rf->ticketID = $ticketID;
            $rf->eventID = $event->eventID;
            $rf->seats = $quantity;
            $rf->cost = $total;
            $rf->discountCode = $dCode;
            $rf->token = request()->input('_token');
            if ($flatamt > 0) {
                $rf->discountAmt = $flatamt;
            } else {
                $rf->discountAmt = $total - $subcheck;
            }
            Auth::check() ? $rf->creatorID = auth()->user()->id : $rf->creatorID = 1;
            Auth::check() ? $rf->updaterID = auth()->user()->id : $rf->updaterID = 1;
            if ($t->waitlisting()) {
                $rf->status = 'Wait List';
            }
            $rf->save();

            // This is the attempt to repair the parent/child relationship between
            // event-registration and reg-finance prior to overhaul

            for ($i = 1; $i <= $quantity; $i++) {
                $start_reg = $start_reg + $i - 1;
                $reg = Registration::find($start_reg);
                $reg->rfID = $rf->regID;
                $reg->save();
            }

            // Everything is saved and updated and such, now display the data back for review
            return redirect('/confirm_registration/' . $reg->regID);
        }
    }

    public function edit($id)
    {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update(Request $request, Registration $reg)
    {
        // responds to POST /reg_verify/{regID}
        // This is the person record for the registration
        $person = Person::find($reg->personID);

        if (auth()->check()) {
            $updater = auth()->user()->id;
        } else {
            $updater = 1;
        }

        $name = request()->input('name');
        if (strpos($name, '_')) {
            // when passed from the registration receipt, the $name will have an underscore
            list($name, $field) = array_pad(explode("_", $name, 2), 2, null);
        }
        if (strpos($name, '-')) {
            // when passed from the registration receipt, the $name will have an underscore
            list($name, $field) = array_pad(explode("-", $name, 2), 2, null);
        }
        $value = request()->input('value');

        // Because allergenInfo, allergenNote (as eventNotes) and Industry are reported
        // in registrations and saved to the profile...
        if ($name == 'allergenInfo' && $value !== null) {
            $value = implode(",", (array)$value);
            $person->allergenInfo = $value;
            $person->updaterID = $updater;
            $person->save;
        } elseif ($name == 'eventNotes') {
            $person->allergenInfo = $value;
            $person->updaterID = $updater;
            $person->save;
        } elseif ($name == 'indName') {
            $person->indName = $value;
            $person->updaterID = $updater;
            $person->save;
        } elseif ($name == 'affiliation') {
            $value = implode(",", (array)$value);
            $person->affiliation = $value;
            $person->updaterID = $updater;
            $person->save;
        }

        //$person            = Person::find($reg->personID);
        $reg->{$name} = $value;
        $reg->updaterID = $updater;
        $reg->save();
    }

    public function destroy(Registration $reg, RegFinance $rf)
    {
        // responds to DELETE /cancel_registration/{reg}/{rf}
        // 1. Takes $reg->regID and $rf->regID
        // 2. Determine if this is a full or partial refund (if at all)
        // 3. Decrement registration count on ticket(s), sessions as needed

        $needSessionPick = 0;
        $verb = 'canceled';
        $event = Event::find($reg->eventID);
        $org = Org::find($event->orgID);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        if ($reg->regStatus == 'pending') {
            // the registration was never finalized, sessions weren't picked, so delete
            $reg->delete();
        } elseif ($reg->subtotal > 0 && $rf->pmtRecd == 1 && $rf->stripeChargeID) {
            // There's a refund that needs to occur with Stripe
            if ($reg->subtotal == $rf->cost) {
                // This is a total refund and it was paid
                try {
                    \Stripe\Refund::create(array(
                        "charge" => $rf->stripeChargeID,
                    ));
                    $reg->regStatus = 'Refunded';
                    $rf->status = 'Refunded';
                    $rf->save();
                    $reg->save();

                    // Generate Refund Email
                } catch (Exception $e) {
                    request()->session()->flash('alert-danger', 'The attempt to get a refund failed with order: ' . $rf->regID . '.' . $org->adminContactStatement);
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
                    $rf->status = 'Partially Refunded';
                    $verb = 'refunded';
                    $rf->save();
                    $reg->save();

                    // Generate Refund Email
                } catch (\Exception $e) {
                    request()->session()->flash('alert-danger', 'The attempt to get a partial refund failed witih order; ' . $rf->regID . '. ' . $org->adminContactStatement);
                }
                $reg->delete();
            }
        } elseif ($rf->seats > 1) {
            $reg->regStatus = 'Canceled';
            $rf->status = 'Partially Canceled';
            // decided against decrementing original seat count
            // $rf->seats      = $rf->seats - 1;
            $rf->save();
            $reg->save();
            $reg->delete();
            $verb = 'canceled';
        } else {
            $reg->regStatus = 'Canceled';
            $rf->status = 'Canceled';
            $rf->save();
            $reg->save();
            $reg->delete();
            $rf->delete();
            $verb = 'canceled';
        }

        // Set a warning message to call the organization if there was an issue...
        // but only if someone paid an amount > $0 and there's no stripeChargeID
        if ($reg->subtotal > 0 && $rf->pmtRecd && $rf->stripeChargeID === null) {
            request()->session()->flash('alert-danger', 'The attempt to get a refund failed. ' . $org->adminContactStatement);
        }

        // Now, decrement registration counts where required
        $ticket = Ticket::find($reg->ticketID);

        // Check the tickets associated with this registration and see if there are sessions
        if ($ticket->isaBundle) {
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
        if ($rf->pmtRecd || $rf->pmtType == 'At Door') {
            foreach ($tickets as $t) {
                $t->regCount = $t->regCount - 1;
                $t->save();
            }

            $sessions = RegSession::where('regID', '=', $reg->regID)->get();
            foreach ($sessions as $s) {
                $e = EventSession::find($s->sessionID);
                if ($e->regCount > 0) {
                    $e->regCount--;
                }
                $e->save();
                $s->delete();
            }
        }

        request()->session()->flash('alert-success', 'The registration with id ' . $reg->regID . ' has been ' . $verb);
        //return redirect('/upcoming');
        return redirect()->back()->withInput();
    }
}
