<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\Event;
use App\Models\EventDiscount;
use App\Models\EventSession;
use App\Models\Org;
use App\Models\OrgPerson;
use App\Models\Person;
use App\Models\RegFinance;
use App\Models\Registration;
use App\Models\RegSession;
use App\Models\Ticket;
use App\Models\Track;
use App\Models\User;
use App\Notifications\SetYourPassword;
use App\Notifications\WaitListNoMore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Session;
use Stripe\Stripe;

set_time_limit(0);
ini_set('memory_limit', '-1');

class RegistrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['showRegForm', 'store', 'update', 'processRegForm']]);
    }

    public function index()
    {
        // responds to /blah
    }

    public function processRegForm(Request $request, Event $event): RedirectResponse
    {
        // called by POST /regstep1/{event}
        // Initiating registration for an event from GET /event/{id}
        $discount_code = request()->input('discount_code');
        $tq = [];
        $quantity = 0;

        if ($discount_code === null) {
            $discount_code = '';
        } else {
            $discount_code = '/' . $discount_code;
        }

        $tkts = Ticket::where([
            ['eventID', '=', $event->eventID],
            ['isSuppressed', '=', 0],
        ])
            ->where(fn($q) => $q->where('maxAttendees', '=', 0)->orWhereRaw('maxAttendees - regCount > 0'))
            ->get();

        foreach ($tkts as $ticket) {
            $q = request()->input('q-' . $ticket->ticketID);
            if ($q !== null && $q > 0) {
                array_push($tq, ['t' => $ticket->ticketID, 'q' => $q]);
                $quantity += $q;
            }
        }

        Session::put('req', $request->all());
        Session::save();

        return redirect("/regstep2/$event->eventID/$quantity" . $discount_code);
    }

    /**
     * Two-part form so that login popup can redirect->back() without going to dashboard;
     * Requires use of Session to pass the request object along
     * RISK: the session variables will only survive one redirection
     *
     * @param null $discount_code
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showRegForm(Event $event, $quantity, $discount_code = null): View
    {
        // 2-part form so that login popup can redirect->back() without going to dashboard
        // requires use of Session to pass the request object along
        // RISK: the session variables will only survive one redirection
        $tq = [];

        $member = strtoupper(trans('messages.fields.member'));
        $nonmbr = strtoupper(trans('messages.fields.nonmbr'));

        $org = Org::find($event->orgID);

        $discountChapters = $org->discountChapters;

        // $tkts list only shows tickets that can be purchased - 6/5/2024
        $tkts = Ticket::where([
            ['eventID', '=', $event->eventID],
            ['isSuppressed', '=', 0],
        ])
            ->where(fn($q) => $q->where('maxAttendees', '=', 0)->orWhereRaw('maxAttendees - regCount > 0'))
            ->get();

        if ($req = Session::get('req')) {
            foreach ($tkts as $ticket) {
                $t = $ticket->ticketID;
                if (isset($req['q-' . $t])) {
                    $q = $req['q-' . $t];
                    if ($q > 0) {
                        array_push($tq, ['t' => $t, 'q' => $q]);
                    }
                }
            }
        }

        $certs = DB::table('certifications')->select('certification')->get();

        return view(
            'v1.public_pages.regform_show',
            compact('event', 'discount_code', 'tkts', 'tq', 'member', 'nonmbr', 'quantity', 'discountChapters', 'certs')
        );
    }

    /**
     * Shows a report of registrations for a specific event
     *
     * @param  $param : the slug or eventID for an event
     * @param null $format
     *                        valid values:  'fin' for a finance report
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($param, $format = null)
    {
        // Responds to GET /eventreport/{slug}/{format?}

        try {
            $event = Event::when(
                filter_var($param, FILTER_VALIDATE_INT) !== false,
                function ($query) use ($param) {
                    return $query->where('eventID', $param);
                },
                function ($query) use ($param) {
                    return $query->where('slug', $param);
                }
            )->firstOrFail();
        } catch (\Exception $exception) {
            $message = trans('messages.codes.invalid_id', ['id' => trans('messages.codes.eventID')]);

            return view('v1.public_pages.error_display', compact('message'));
        }

        // list of attendees who have registered, excludes payment pendings
        $regs = Registration::where('eventID', '=', $event->eventID)
            ->whereHas('regfinance', function ($q) {
                $q->where('pmtRecd', '=', 1);
            })->whereIn('regStatus', ['active', 'processed'])
            ->with('regfinance', 'ticket', 'person')->get();

        // Separating out the query because name tags should be printed even if paying 'At Door'
        $nametags = Registration::where('eventID', '=', $event->eventID)
            ->with('regfinance', 'ticket', 'person', 'person.orgperson', 'regsessions', 'event')
            ->orderBy('regID')
            ->get();

        $nametags = $nametags->sortBy(function ($n) {
            return $n->person->lastName;
        });

        // list of attendees who are payment pendings so they are displayed separately
        $deadbeats = Registration::where([
            ['eventID', '=', $event->eventID],
        ])->with('regfinance', 'ticket')
            ->whereHas('regfinance', function ($q) {
                $q->where('pmtRecd', '=', 0);
                $q->where('status', '=', 'pending');
            })
            ->get();

        // list of wait-listed or interrupted registrations
        $notregs = Registration::where('eventID', '=', $event->eventID)
            ->where(function ($q) {
                $q->where('regStatus', '=', 'wait')
                    // Even if Payment Pending is the status, they are still registered
                    ->orWhere('regStatus', '=', 'progress');
            })->with('regfinance', 'ticket')->get();

        $tkts = Ticket::where([
            ['eventID', '=', $event->eventID],
            ['isaBundle', '=', 0],
        ])->get();

        $discPie = Registration::where([
            ['eventID', '=', $event->eventID],
        ])
            ->whereHas('regfinance', function ($query) {
                $query->whereNotIn('pmtType', ['pending']);
                $query->where('pmtRecd', '=', 1);
                $query->whereNull('deleted_at');
            })
            ->select(DB::raw('discountCode, count(discountCode) as cnt, sum(subtotal)-sum(ccFee)-sum(mcentricFee) as orgAmt,
                                    sum(origcost)-sum(subtotal) as discountAmt, sum(mcentricFee) as handleFee,
                                    sum(ccFee) as ccFee, sum(subtotal) as cost'))
            //->whereNull('deleted_at')
            ->groupBy('discountCode')
            ->orderBy('cnt', 'desc')->get();

        $refunded = Registration::where([
            ['eventID', '=', $event->eventID],
        ])
            ->whereHas('regfinance', function ($query) {
                $query->whereNotIn('pmtType', ['pending', 'Processed']);
                $query->where('pmtRecd', '=', 1);
                $query->withTrashed();
            })
            // 9/26/22 - Corrected display of NA (Refunded) Net (only changed first line)
            //->select(DB::raw('discountCode, count(discountCode) as cnt, sum(subtotal)-sum(ccFee)-sum(mcentricFee) as orgAmt,
            ->select(DB::raw('discountCode, count(discountCode) as cnt, 0-sum(ccFee)-sum(mcentricFee) as orgAmt,
                                    sum(origcost)-sum(subtotal) as discountAmt, sum(mcentricFee) as handleFee,
                                    sum(ccFee) as ccFee, sum(subtotal) as cost'))
            ->withTrashed()
            ->whereNotIn('regStatus', ['pending', 'Processed'])
            ->groupBy('discountCode')
            ->orderBy('cnt', 'desc')->get();

        foreach ($refunded as $key => $value) {
            if ($value->discountCode == '' || $value->discountCode === null || $value->discountCode == '0') {
                $value->discountCode = trans('messages.headers.N/A') . '(' . trans('messages.reg_status.refunded') . ')';
            } else {
                $value->discountCode = $value->discountCode . ' (' . trans('messages.reg_status.refunded') . ')';
            }
        }
        $discountCounts = Registration::select(DB::raw('discountCode, count(origcost) as cnt, sum(subtotal) as cost,
                                    sum(ccFee) as ccFee, sum(mcentricFee) as handleFee'))
            ->where([
                ['eventID', '=', $event->eventID],
                ['regStatus', '=', 'processed'],
            ])
            ->groupBy('discountCode')->orderBy('cnt', 'desc')->get();

        // $lessCounts are capturing revenue that would not have been remitted by credit card; mCentric does not pay.
        // select below may need to start at 0 vs. sum(subtotal) for orgAmt.  Need to test.
        $lessCounts = Registration::where('eventID', '=', $event->eventID)
            ->select(DB::raw('discountCode, count(discountCode) as cnt, sum(subtotal)-sum(ccFee)-sum(mcentricFee) as orgAmt, 0, 0, 0, 0'))
            ->whereHas('regfinance', function ($q) {
                $q->whereIn('pmtType', ['door', 'cash', 'check']);
                $q->where('pmtRecd', '=', 1);
            })->groupBy('discountCode')->orderBy('cnt', 'desc')->first();

        foreach ($discPie as $d) {
            if ($d->discountCode == '' || $d->discountCode === null || $d->discountCode == '0') {
                $d->discountCode = trans('messages.headers.N/A');
            }
        }

        $subtotal = Registration::where('eventID', '=', $event->eventID)
            ->select(DB::raw('"discountCode", count("discountCode") as cnt, sum(subtotal)-sum(ccFee)-sum(mcentricFee) as orgAmt,
                                    sum(origcost)-sum(subtotal) as discountAmt, sum(mcentricFee) as handleFee,
                                    sum(ccFee) as ccFee, sum(subtotal) as cost'))
            ->whereHas('regfinance', function ($query) {
                $query->whereNotIn('pmtType', ['pending']);
                $query->where('pmtRecd', '=', 1);
                $query->whereNull('deleted_at');
            })->first();

        $total = Registration::where('eventID', '=', $event->eventID)
            ->select(DB::raw('"discountCode", count("discountCode") as cnt, sum(subtotal)-sum(ccFee)-sum(mcentricFee) as orgAmt,
                                    sum(origcost)-sum(subtotal) as discountAmt, sum(mcentricFee) as handleFee,
                                    sum(ccFee) as ccFee, sum(subtotal) as cost'))
            ->whereHas('regfinance', function ($query) {
                $query->whereNotIn('pmtType', ['pending']);
                $query->whereNull('deleted_at');
            })->first();

        // for calculating all the refunded cc and handlefee
        if ($refunded->isNotEmpty()) {
            $total_cc_from_refund = 0;
            $total_handling_from_refund = 0;

            foreach ($refunded as $key => $value) {
                $total_cc_from_refund += $value->ccFee;
                $total_handling_from_refund += $value->handleFee;
                $discPie->push($value);
            }
            $subtotal->ccFee += $total_cc_from_refund;
            $subtotal->handleFee += $total_handling_from_refund;
            $subtotal->orgAmt -= $total_cc_from_refund + $total_handling_from_refund;
        }

        $discPie->put(count($discPie), $subtotal);
        if ($lessCounts !== null && $lessCounts->cnt > 0) {
            $discPie->put(count($discPie), $lessCounts);
            $subtotal->discountCode = trans('messages.fields.subtotal');
            $lessCounts->discountCode = '&nbsp; &nbsp; <span class="red">' . trans('messages.headers.less_cc') . '</span>';
            $discPie->put(count($discPie), $total);
            $total->discountCode = '&nbsp; &nbsp; &nbsp; &nbsp; ' . trans('messages.fields.total_due');
            $total->orgAmt = $total->orgAmt - $lessCounts->orgAmt;
        } else {
            $subtotal->discountCode = '&nbsp; &nbsp; &nbsp; &nbsp; ' . trans('messages.fields.total_due');
        }

        $refunds = RegFinance::where('eventID', '=', $event->eventID)->whereNotNull('deleted_at')->get();

        if ($event->hasTracks) {
            $tracks = Track::where('eventID', $event->eventID)->get();
        } else {
            $tracks = null;
        }

        return view(
            'v1.auth_pages.events.event-rpt',
            compact('event', 'regs', 'notregs', 'tkts', 'refunds', 'nametags', 'deadbeats', 'discPie', 'tracks', 'discountCounts', 'format')
        );
    }

    public function create()
    {
        // responds to /blah/create and shows add/edit form
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request, Event $event)
    {
        // called by POST /regstep3/{event}/create

        $org = Org::find($event->orgID);
        $logged_in = 0;
        $inDB = 0;
        $flag_dupe = 0;
        $dupe_names = [];
        $show_pass_fields = 0;
        $set_new_user = 0;
        $set_secondary_email = 0;
        $subcheck = 0;
        $sumtotal = 0;

        $quantity = request()->input('quantity');
        $reduction = request()->input('reduction');
        $total = request()->input('total');
        if (is_nan($total)) {
            $total = 0;
        }
        $token = request()->input('_token');

        // Record logged in user info
        if (Auth::check()) {
            $id = auth()->user()->id;
            $u = User::find($id);
            $logged_in = 1;
            if ($u->password === null) {
                // This shouldn't be possible. How can you be logged in and have no password set?
                $show_pass_fields = 1;
            }
            $p = $this->currentPerson = Person::find($id)->load('orgperson');
            $authorID = $p->personID;
            $regBy = $p->showFullName();
        } else {
            // No user logged in; checking to see if first provided email is in the database;
            // If so, force a login by returning to form with input saved and message.
            // Assumptive RISK re: first email is of the person doing the registration -- mitigated through javascript

            $authorID = 0; // placeholder personID until $person record created
            $regBy = null;
            $email = strtolower(request()->input('login'));
            $chk = Email::where('emailADDR', '=', $email)->first();
            if ($chk !== null) {
                $p = Person::find($chk->personID);
                $u = User::find($p->personID);
                if ($u->password === null) {
                    request()->session()->flash('alert-warning', trans('messages.instructions.login_new', ['admin' => $org->adminContactStatement]));
                } else {
                    request()->session()->flash('alert-warning', trans('messages.instructions.login', ['admin' => $org->adminContactStatement]));
                }
                $p->notify(new SetYourPassword($p));

                return back()->withInput();
            }
        }

        //  $resubmit set based on "unknown user" of 0 or the logged in user.
        //  If the eventID is the same AND the prior order isn't "Processed" then this is likely a dupe to erase
        if ($authorID != 0 && $logged_in) {
            $resubmit = RegFinance::where([
                ['personID', '=', $authorID],
                ['eventID', '=', $event->eventID],
                ['status', '!=', 'processed'],
            ])->first();
        } elseif ($authorID == 0 && !$logged_in) {
            // no check for in-progress submissions when $authorID == 0
            $resubmit = null;
        } else {
            // No one should EVER be able to get here.
            exit($org->techContactStatement);
        }

        // Create new, or re-open the stub (only if the stub is a stub for this event), reg-finance record
        if ($resubmit !== null && $resubmit->eventID == $event->eventID) {
            $rf = $resubmit;
            $resubmitted_regs = Registration::where('rfID', '=', $resubmit->regID)->get();
            // if there was a resubmit, delete the old registration records and redo later...
            foreach ($resubmitted_regs as $reg) {
                $reg->debugNotes = 'Deleting due to resubmission.';
                $reg->save();
                $reg->delete();
            }
            $resubmitted_regs = null;
        } else {
            $rf = new RegFinance;
            $resubmitted_regs = null;
        }
        // $rf->regID is either set (stub) or will be upon save.
        // $rf->ticketID is no longer relevant to reg-finance records -- Remove line when no longer in DB
        $rf->creatorID = $authorID;
        $rf->updaterID = $authorID;
        $rf->personID = $authorID;
        $rf->eventID = $event->eventID;
        $rf->seats = $quantity - $reduction;
        $rf->token = $token;
        $rf->cost = $total;
        $rf->save();

        $tkts = Ticket::where([
            ['eventID', '=', $event->eventID],
            ['isSuppressed', '=', 0],
        ])->get();

        // Set $regBy to the first ticket's person info unless someone was already logged in
        if ($regBy === null && !$logged_in) {
            $firstName = ucwords(request()->input('firstName'));
            $lastName = ucwords(request()->input('lastName'));
            $regBy = $firstName . ' ' . $lastName;
        }

        // Registration #1 is assumed "special" because it should be the originating user when self-registering.
        // For each of the registrations
        for ($i = 1; $i <= $quantity - $reduction; $i++) {
            if ($i == 1) {
                $login = strtolower(request()->input('login'));
                // Check to see if the email of the first registrant belongs to the logged-in person
                if ($logged_in && assoc_email($login, $p)) {
                    $person = $this->currentPerson;
                    $inDB = 1;
                    $set_new_user = 0;
                } elseif ($logged_in) {
                    // $login email is not associated with $this->currentPerson but could still be associated with
                    // someone in the database
                    if (check_exists('e', 0, [$login])) {
                        $inDB = 1;
                        $person = Person::where([
                            ['login', $login],
                        ])->first();
                        $set_new_user = 0;
                    } else {
                        $person = null;
                    }
                } else {
                    // No one is logged in and information appears to be new to the DB
                    $person = null;
                    $set_new_user = 1;
                }
                $i_cnt = '';
            } else {
                $person = null;
                $set_new_user = 1;
                $i_cnt = '_' . $i;
            }

            $dupe_check = null;
            $set_secondary_email = 0;

            // 1. Grab the passed variables for the person and registration info
            $prefix = ucwords(request()->input('prefix' . $i_cnt));
            $firstName = ucwords(request()->input('firstName' . $i_cnt));
            $middleName = ucwords(request()->input('middleName' . $i_cnt));
            $lastName = ucwords(request()->input('lastName' . $i_cnt));
            $login = strtolower(request()->input('login' . $i_cnt));
            if ($lastName === null) {
                continue;
            }
            $pmiID = trim(request()->input('OrgStat1' . $i_cnt));
            $pmiID > 0 ?: $pmiID = null;
            $suffix = ucwords(request()->input('suffix' . $i_cnt));
            $prefName = ucwords(request()->input('prefName' . $i_cnt));
            $compName = ucwords(request()->input('compName' . $i_cnt));
            $indName = ucwords(request()->input('indName' . $i_cnt));
            $title = ucwords(request()->input('title' . $i_cnt));
            $chapterRole = ucwords(request()->input('chapterRole' . $i_cnt));
            $eventQuestion = request()->input('eventQuestion' . $i_cnt);
            $eventTopics = request()->input('eventTopics' . $i_cnt);
            $affiliation = request()->input('affiliation' . $i_cnt);
            $certification = request()->input('certifications' . $i_cnt);
            $experience = request()->input('experience' . $i_cnt);
            $dCode = request()->input('discount_code' . $i_cnt);
            $dc = EventDiscount::where([
                ['eventID', '=', $event->eventID],
                ['discountCODE', '=', $dCode],
            ])->first();
            if ($dc === null || $dCode === null || $dCode == ' ') {
                $dCode = 'N/A';
            }
            $ticketID = request()->input('ticketID-' . $i);
            $t = Ticket::find($ticketID);
            $flatamt = request()->input('flatamt' . $i_cnt);
            $percent = request()->input('percent' . $i_cnt);
            $subtotal = request()->input('sub' . $i) * 1;
            $origcost = request()->input('cost' . $i);
            // strip out , from $ figure over $1,000
            $origcost = str_replace(',', '', $origcost);
            if ($event->hasFood) {
                $specialNeeds = request()->input('specialNeeds' . $i_cnt);
                $eventNotes = request()->input('eventNotes' . $i_cnt);
                $allergenInfo = request()->input('allergenInfo' . $i_cnt);
                $cityState = request()->input('cityState' . $i_cnt);
            }

            // Try to assign $person via OrgStat1 unless $person has the value of $this->currentPerson (and so is not null)
            if ($pmiID && ($person === null)) {
                $person = Person::whereHas('orgperson', function ($q) use ($pmiID) {
                    $q->where('OrgStat1', '=', $pmiID);
                })->first();
                $set_new_user = 0;
            }
            // If $person is still not set, try to assign $person via login (email address)
            if ($person === null) {
                $person = Person::whereHas('emails', function ($q) use ($login) {
                    $q->where('emailADDR', '=', $login);
                })->first();
                $set_new_user = 0;
            } else {
                // $person was set from PMI ID; quick check to see if email should be a secondary

                if (strtolower($person->login) != strtolower($login)) {
                    $set_secondary_email = 1;
                }
            }

            try {
                DB::beginTransaction();

                // if we need to create a new $person record, flag for the creation of the other new objects too
                if ($person === null) {
                    $person = new Person;
                    $set_new_user = 1;
                }

                // We have either found the appropriate person record ($p) or have created a new one
                isset($login) && $set_new_user ? $person->login = $login : 1; // only sets $login if new
                if (!$person->is_member($event->orgID)) {
                    // These fields should NOT be updated if $person is in DB AND a PMI ID (OrgStat1) is set.
                    isset($firstName) ? $person->firstName = $firstName : 1;
                    isset($lastName) ? $person->lastName = $lastName : 1;
                }

                isset($prefix) ? $person->prefix = $prefix : 1;
                isset($middleName) ? $person->midName = $middleName : 1;
                isset($suffix) ? $person->suffix = $suffix : 1;
                $person->defaultOrgID = $event->orgID;
                isset($prefName) ? $person->prefName = $prefName : $person->prefName = $firstName;
                isset($compName) ? $person->compName = $compName : 1;
                isset($indName) ? $person->indName = $indName : 1;
                isset($title) ? $person->title = $title : 1;
                isset($experience) ? $person->experience = $experience : 1;
                isset($chapterRole) ? $person->chapterRole = $chapterRole : 1;
                if ($event->hasFood && $allergenInfo !== null) {
                    $person->allergenInfo = implode(',', (array)$allergenInfo);
                    isset($eventNotes) ? $person->allergenNote = $eventNotes : 1;
                }
                isset($affiliation) ? $person->affiliation = implode(',', (array)$affiliation) : 1;
                isset($certification) ? $person->certifications = implode(',', (array)$certification) : 1;
                $person->save();

                if ($pmiID === null) {
                    $regMem = 'nonmbr';
                } else {
                    $regMem = 'member';
                }

                // Only if we had to set a temporary RF record with system owner
                if ($i == 1) {
                    if ($rf->personID == 0) {
                        $rf->personID = $person->personID;
                        $rf->save();
                    }
                }
                if ($set_new_user) {
                    $user = new User;
                    $user->id = $person->personID;
                    $user->name = $login;
                    $user->login = $login;
                    $user->email = $login;
                    $user->save();
                    if ($i == 1 && !Auth::check()) {
                        // log the first ticket's user in if no one is logged in -- ASSUMPTION RISK
                        Auth::loginUsingId($user->id);
                        $rf->personID = $person->personID;
                        $rf->save();
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
                    $person->defaultOrgPersonID = $op->id;
                    $person->save();

                    $email = new Email;
                    $email->personID = $person->personID;
                    $email->emailADDR = $login;
                    $email->isPrimary = 1;
                    $email->save();
                } else {
                    $op = OrgPerson::where([
                        ['personID', '=', $person->personID],
                        ['orgID', '=', $event->orgID],
                    ])->first();
                    // Chance of not getting an $op record from above, if $person exists but for other chapter, so create if needed.
                    if ($op === null) {
                        $op = new OrgPerson;
                        $op->orgID = $event->orgID;
                        $op->personID = $person->personID;
                    }
                    // If not already a member and a PMI ID was provided, update and flag to change ticket price
                    if (!$person->is_member($event->orgID) && isset($pmiID)) {
                        $op->OrgStat1 = $pmiID;
                        $op->updaterID = $person->personID;
                        $op->save();
                        $change_to_member = 1;
                    }
                    $person->defaultOrgPersonID = $op->id;
                    $person->save();
                }

                if ($set_secondary_email) {
                    $email = new Email;
                    $email->personID = $person->personID;
                    $email->emailADDR = $login;
                    $email->save();
                }

                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
                request()->session()->flash('alert-danger', implode(' ', [trans('messages.messages.user_create_fail'), $org->techContactStatement]));

                return back()->withInput();
            }

            // This is a courtesy check to display a message to user that they've already registered/paid for this event.
            $dupe_check = Registration::where([
                ['personID', $person->personID],
                ['eventID', $event->eventID],
                ['regStatus', 'processed'],
                ['deleted_at', null],
            ])->first();

            $tmp_tkt = Ticket::find($ticketID);
            if ($tmp_tkt !== null && $tmp_tkt->soldout()) {
                request()->session()->flash('alert-danger',
                    trans('messages.instructions.sold_out4', ['url' => $event->event_url()]));

                return redirect()->back()->withInput();
            }
            try {
                $reg = new Registration;
                $reg->rfID = $rf->regID;
                $reg->eventID = $event->eventID;
                $reg->ticketID = $ticketID;
                $reg->personID = $person->personID;
                $reg->reportedIndustry = $indName;
                $reg->eventTopics = $eventTopics;

                // Regional Events show the question so pull from form
                if ($event->eventTypeID == 5) {
                    $reg->isFirstEvent = request()->input('isFirstEvent' . $i_cnt) !== null ? 1 : 0;
                } else {
                    // Otherwise, count whether registrations exist for this user
                    if (count($person->registrations) == 0) {
                        $reg->isFirstEvent = 1;
                    }
                }

                $reg->isAuthPDU = request()->input('isAuthPDU' . $i_cnt) !== null ? 1 : 0;
                $reg->eventQuestion = $eventQuestion;
                $reg->canNetwork = request()->input('canNetwork' . $i_cnt) !== null ? 1 : 0;
                $reg->affiliation = implode(',', $affiliation);
                $reg->regStatus = 'progress';
                if ($event->isPrivate && $t->waitlisting()) {
                    $reg->regStatus = 'wait';
                    $rf->status = 'wait';
                }
                $reg->registeredBy = $regBy;
                $reg->token = $token;
                $reg->subtotal = $subtotal;
                if ($regMem == 'member' && $reg->subtotal > $tmp_tkt->memberBasePrice) {
                    $reg->subtotal = $tmp_tkt->memberBasePrice;
                }
                $reg->discountCode = $dCode;
                $reg->origcost = $origcost;
                $reg->membership = $regMem;
                if ($event->hasFood) {
                    $reg->specialNeeds = $specialNeeds;
                    $reg->allergenInfo = implode(',', (array)$allergenInfo);
                    $reg->cityState = $cityState;
                    $reg->eventNotes = $eventNotes;
                }
                $reg->creatorID = $authorID;
                $reg->updaterID = $authorID;

                // This is a correction of the original cost within the database for auditing purposes. 4/14/22
                if ($person->is_member($event->orgID)) {
                    $ocost = $reg->ticket->memberBasePrice;
                } else {
                    $ocost = $reg->ticket->nonmbrBasePrice;
                }
                if ($ocost != $reg->origcost) {
                    $reg->debugNotes .= 'Orig changed from: ' . $reg->origcost . ' to: ' . $ocost . '; ';
                    $reg->origcost = $ocost;
                }
                $handleFee = number_format(($ocost * .029) + .30, 2, '.', '');
                if ($handleFee > 5) {
                    $handleFee = 5;
                }
                $reg->mcentricFee = $handleFee;
                $reg->save();

                if ($dupe_check !== null) {
                    $flag_dupe = 1;
                    array_push($dupe_names, ['reg' => $reg, 'name' => $person->showFullName()]);
                }

                $subcheck += $subtotal;
                $sumtotal += $origcost;
            } catch (\Exception $e) {
                request()->session()->flash('alert-danger',
                    implode(' ', [trans('messages.errors.reg_fail1', ['name' => $person->showFullName()]),
                        $org->techContactStatement,]) . $e->getMessage());

                return redirect()->back()->withInput();
            }
        }

        // Future logic to potentially split off waitlist tickets from non-waitlist tickets would probably go here.
        // 1. Check if all tickets associated with registration records are in waitlist status
        // 2. If yes, do what's here.  Otherwise:
        //    a. split tickets into wait and non.
        //    b. make a $newRF record that will be for NON-waitlist
        //    c. Associate non-waitlist reg records with $newRF
        //    d. Complete $0 waitlist transaction and set a flash message with link
        //    e. Redirect to confirm_registration pointing to $newRF for purchase

        if ($subcheck == $total) {
            $rf->discountAmt = $sumtotal - $subcheck;
            $rf->save();
        } else {
            request()->session()->flash(
                'alert-warning',
                trans('messages.errors.corruption', ['total' => $total, 'check' => $subcheck])
            );

            return Redirect::back()->withErrors(
                ['warning' => trans('messages.errors.corruption', ['total' => $total, 'check' => $subcheck])]
            );
        }

        if ($flag_dupe) {
            request()->session()->flash(
                'dupes',
                trans_choice('messages.warning.dupe_reg', count($dupe_names),
                    ['names' => li_print_array($dupe_names, 'ul')])
            );
            request()->session()->flash(
                'alert-warning',
                trans_choice('messages.warning.dupe_reg', count($dupe_names),
                    ['names' => li_print_array($dupe_names, 'ul')])
            );
        }

        // Everything is saved and updated and such, now display the data back for review
        return redirect('/confirm_registration/' . $rf->regID);
    }

    public function edit($id)
    {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update(Request $request, Registration $reg)
    {
        // responds to Ajax request via POST /reg_verify/{regID}
        // This is the person record for the registration
        $person = Person::find($reg->personID);

        if (auth()->check()) {
            $updater = auth()->user()->id;
        } else {
            // This should never happen.
            $updater = 1;
            // I don't think this will ever end up displaying due to trigger from Ajax event
            request()->session()->flash('alert-danger', trans('messages.errors.unexpected'));
        }

        $name = request()->input('name');
        if (strpos($name, '_')) {
            // when passed from the registration receipt, the $name will have an underscore
            [$name, $field] = array_pad(explode('_', $name, 2), 2, null);
        }
        if (strpos($name, '-')) {
            // when passed from the registration receipt, the $name will have an underscore
            [$name, $field] = array_pad(explode('-', $name, 2), 2, null);
        }
        $value = request()->input('value');

        // Because allergenInfo, allergenNote (as eventNotes) and Industry are reported
        // in registrations and saved to the profile...
        if ($name == 'allergenInfo' && $value !== null) {
            $value = implode(',', (array)$value);
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
            $value = implode(',', (array)$value);
            $person->affiliation = $value;
            $person->updaterID = $updater;
            $person->save;
        }

        //$person            = Person::find($reg->personID);
        $reg->{$name} = $value;
        $reg->updaterID = $updater;
        $reg->save();
    }

    public function promote(Registration $reg): RedirectResponse
    {
        $this->currentPerson = Person::find(auth()->user()->id);
        $event = Event::find($reg->eventID);
        $rf = RegFinance::find($reg->rfID);

        $rf->status = 'pending';
        $rf->pmtType = 'door';
        $rf->updaterID = $this->currentPerson->personID;
        $rf->save();

        $reg->regStatus = 'promoted';
        $reg->updaterID = $this->currentPerson->personID;
        $reg->save();

        $recipient = Person::find($reg->personID);
        // Consider a notification
        $recipient->notify(new WaitListNoMore($reg));

        return redirect(env('APP_URL') . "/eventreport/$event->slug");
    }

    public function destroy(Registration $reg, RegFinance $rf): RedirectResponse
    {
        // responds to DELETE /cancel_registration/{reg}/{rf}
        // 1. Takes $reg->regID and $rf->regID
        // 2. Determine if this is a full or partial refund (if at all)
        // 3. Decrement registration count on ticket(s), sessions as needed

        $needSessionPick = 0;
        $verb = strtolower(trans('messages.headers.canceled'));
        $event = Event::find($reg->eventID);
        $org = Org::find($event->orgID);
        $flag_dupe = 0;
        $dupe_names = [];
        $rf->load('registrations');

        Stripe::setApiKey(env('STRIPE_SECRET'));

        if ($reg->regStatus == 'progress') {
            // for a registration that is in process (or never completed) check if we're catching dupes
            if (count($rf->registrations) > 1) {
                // Deleting this registration, will leave at least 1 on the order.
                // Need to recheck if there are dupes  (can't recall if this is even true here anymore)
                foreach ($rf->registrations as $reg_chk) {
                    $dupe_check = null;
                    if ($reg_chk->regID != $reg->regID) {
                        $dupe_check = Registration::where([
                            ['personID', $reg_chk->personID],
                            ['eventID', $event->eventID],
                            ['regStatus', 'processed'],
                            ['deleted_at', null],
                        ])->with('person')->first();

                        if ($dupe_check !== null) {
                            $flag_dupe = 1;
                            array_push($dupe_names, ['reg' => $reg_chk, 'name' => $reg_chk->person->showFullName()]);
                        }
                    }
                }
                // subtract $reg->subtotal from $rf->cost and save to reflect new total
                $rf->cost -= $reg->subtotal;
                $rf->updaterID = auth()->user()->id;
                $rf->save();
                $reg->delete();
            } else {
                $reg->delete();
                $rf->delete();
            }
        } elseif ($reg->subtotal > 0 && $rf->pmtRecd == 1 && $rf->stripeChargeID) {
            // There's a refund that needs to occur with Stripe
            if ($reg->subtotal == $rf->cost) {
                // This is a total refund and it was paid
                try {
                    \Stripe\Refund::create([
                        'charge' => $rf->stripeChargeID,
                    ]);
                    $reg->regStatus = 'refunded';
                    $rf->status = 'refunded';
                    $rf->save();
                    $reg->save();

                    $reg->ticket->update_count(-1, 0);

                    // Generate Refund Email
                } catch (Exception $e) {
                    request()->session()->flash(
                        'alert-danger',
                        trans('messages.errors.refund_failed', ['rest' => $rf->regID . '.  ' . $org->adminContactStatement])
                    );
                }
                $rf->delete();
                $reg->delete();
            } else {
                // This is a partial refund, so send the amount
                try {
                    \Stripe\Refund::create([
                        'charge' => $rf->stripeChargeID,
                        'amount' => $reg->subtotal * 100,
                    ]);
                    $reg->regStatus = 'refunded';
                    $rf->status = 'partial';
                    $verb = strtolower(trans('messages.headers.refunded'));
                    $rf->save();
                    $reg->save();

                    $reg->ticket->update_count(-1, 0);

                    // Generate Refund Email
                } catch (\Exception $e) {
                    request()->session()->flash('alert-danger', trans('messages.messages.partial_fail',
                            ['rfid' => $rf->regID]) . $org->adminContactStatement);
                }
                $reg->delete();
            }
        } elseif ($rf->seats > 1) {
            // decided against decrementing original seat count
            $reg->regStatus = 'canceled';
            $rf->status = 'p_canceled';
            $rf->save();
            $reg->save();
            $reg->delete();
            $verb = strtolower(trans('messages.headers.canceled'));
        } else {
            $reg->regStatus = 'canceled';
            $rf->status = 'canceled';
            $rf->save();
            $reg->save();
            $reg->delete();
            $rf->delete();
            $verb = strtolower(trans('messages.headers.canceled'));
        }

        // Set a warning message to call the organization if there was an issue...
        // but only if someone paid an amount > $0 and there's no stripeChargeID
        if ($reg->subtotal > 0 && $rf->pmtRecd && $rf->stripeChargeID === null) {
            request()->session()->flash(
                'alert-danger',
                trans('messages.errors.refund_failed', ['rest' => $rf->regID . '.  ' . $org->adminContactStatement])
            );
        }

        // Now, decrement registration counts where required
        $ticket = Ticket::find($reg->ticketID);

        // Decrement the regCount on the ticket if ticket was paid OR 'At Door'
        // Also decrement the attendance of any sessions
        if ($rf->pmtRecd || $rf->pmtType == 'door') {
            $ticket->update_count(-1, 0);

            $sessions = RegSession::where('regID', '=', $reg->regID)->get();
            foreach ($sessions as $s) {
                $e = EventSession::find($s->sessionID);
                // Need to check for null EventSession due to 'shadow' sessions
                if ($e !== null) {
                    if ($e->regCount > 0) {
                        $e->regCount--;
                        $e->save();
                    }
                }
                $s->delete();
            }
        }

        request()->session()->flash('alert-success', trans('messages.reg_status.msg_status', ['id' => $reg->regID, 'verb' => $verb]));
        if ($flag_dupe) {
            request()->session()->flash(
                'alert-warning',
                trans_choice('messages.warning.dupe_reg', count($dupe_names), ['names' => li_print_array($dupe_names, 'ul')])
            );
        }

        //return redirect('/upcoming');
        return redirect()->back()->withInput();
    }
}
