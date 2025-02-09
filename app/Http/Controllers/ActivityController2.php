<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 0);

use App\Models\Event;
use App\Models\Person;
use App\Models\RegFinance;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;

class ActivityController2 extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function future_index()
    {
        // responds to GET /upcoming
        $this->currentPerson = Person::find(auth()->user()->id);
        $now = Carbon::now();

        // Rejiggering queries to enable the display of Event registrations sorted (and stacked)
        // by Event

        // Registrations bought by someone else for $this->currentPerson

        $bought = Registration::where('personID', $this->currentPerson->personID)
            ->whereHas(
                'event', function ($q) {
                    $q->where('eventEndDate', '>=', Carbon::now());
                })
            ->whereHas(
                'regfinance', function ($q) {
                    $q->where('personID', '!=', $this->currentPerson->personID);
                    $q->where('pmtRecd', '=', 1);
                })
            ->with('event', 'ticket', 'person', 'regfinance')
            ->get()->sortBy('event.eventStartDate');

        /*
        $bought = Event::where([
            ['eventEndDate', '>=', Carbon::now()],
            ['orgID', $this->currentPerson->defaultOrgID]
        ])
            ->whereHas('registrations', function($q){
                $q->where('personID', $this->currentPerson);
            })
            ->with(['registrations' => function ($q) {
                $q->where('personID', $this->currentPerson->personID);
                $q->whereHas('regfinance', function ($q2) {
                    $q2->where('personID', '!=', $this->currentPerson->personID);
                    $q2->where('pmtRecd', 1);
                });
                $q->with('event', 'ticket', 'person', 'regfinance');
            }])
            ->get()->sortBy('eventStartDate');
        */

        $paid = RegFinance::whereHas(
            'event', function ($q) {
                $q->where('eventEndDate', '>=', Carbon::now());
            })
            ->with('event', 'person', 'registrations')
            ->where([
                ['personID', '=', $this->currentPerson->personID],
                ['pmtRecd', '=', 1],
            ])
            ->get()->sortBy('event.eventStartDate');

        /*
    $paid = Event::where([
        ['eventEndDate', '>=', Carbon::now()],
        ['orgID', $this->currentPerson->defaultOrgID]
    ])
        ->whereHas('registrations', function($q){
            $q->where('personID', $this->currentPerson);
        })
            ->with(['registrations' => function ($q) {
                $q->where('personID', $this->currentPerson->personID);
                $q->with('ticket', 'person', 'regfinance');
            }])
            ->get()->sortBy('eventStartDate');
        dd($paid);
        */

        $unpaid = RegFinance::where('personID', '=', $this->currentPerson->personID)
            ->whereHas(
                'event', function ($q) {
                    $q->where('eventEndDate', '>=', Carbon::now());
                })
            ->with('event', 'person', 'registrations')
            ->whereHas(
                'registrations', function ($q) {
                    $q->where('pmtRecd', '=', 0);
                })
            ->whereIn('status', ['pending'])
            ->get()->sortBy('event.eventStartDate');

        $pending = RegFinance::whereHas(
            'event', function ($q) {
                $q->where('eventEndDate', '>=', Carbon::now())
                    ->orderBy('eventStartDate');
            })
            ->with('event', 'person', 'registrations')
            ->where('personID', '=', $this->currentPerson->personID)
            ->whereIn('status', ['pending', 'progress'])
            ->get()->sortBy('event.eventStartDate');

        $wait = RegFinance::where('personID', '=', $this->currentPerson->personID)
            ->whereHas(
                'event', function ($q) {
                    $q->where('eventEndDate', '>=', Carbon::now());
                })
            ->with('event', 'person', 'registrations')
            ->whereHas(
                'registrations', function ($q) {
                    $q->whereIn('regStatus', ['wait']);
                })
            ->whereIn('status', ['wait'])
            ->get()->sortBy('event.eventStartDate');

        $topBits = '';

        return view('v1.auth_pages.members.future_event_list', compact('bought', 'paid', 'unpaid', 'pending', 'topBits', 'wait'));
    }

    public function index()
    {
        // responds to /dashboard:  This is the dashboard
        $this->currentPerson = Person::find(auth()->user()->id);
        $orgID = $this->currentPerson->defaultOrgID;
        $today = Carbon::now();

        $attendance = Event::where('er.personID', '=', $this->currentPerson->personID)
            ->join('org-event_types as oet', function ($join) {
                $join->on('oet.etID', '=', 'org-event.eventTypeID');
            })->join('event-registration as er', 'er.eventID', '=', 'org-event.eventID')
            ->whereIn('oet.orgID', [1, $orgID])
            ->where([
                ['org-event.orgID', '=', $orgID],
                ['eventEndDate', '<', $today],
            ])
            ->whereNull('er.deleted_at')
            ->where(function ($w) {
                $w->where('er.regStatus', '=', 'active')
                    ->orWhere('er.regStatus', '=', 'processed');
            })
            ->select('org-event.eventID', 'org-event.eventName', 'oet.etName',
                'org-event.eventStartDate', 'org-event.eventEndDate',
                DB::raw('(select count(*) from `event-registration` er2
                                        where er2.eventID = `org-event`.eventID and er2.canNetwork=1) as cnt2'))
            ->distinct()
            ->withCount('registrations')
            ->orderBy('org-event.eventStartDate', 'DESC')->get(20);

        $bar2 = Event::select('eventID', 'eventStartDate', 'eventTypeID',
            DB::raw('(select count(*) from `event-registration` er2 where er2.eventID = `org-event`.eventID and er2.personID='
                .$this->currentPerson->personID." and er2.deleted_at is null) as 'attended'"))
            ->where([
                ['orgID', $orgID],
                ['eventEndDate', '<', $today],
            ])->whereIn('eventTypeID', [1, 9])
            ->whereHas('event_type', function ($q) use ($orgID) {
                $q->whereIn('orgID', [1, $orgID]);
            })
            ->withCount('registrations')
            ->with('event_type')
            ->orderBy('eventStartDate', 'DESC')->limit(14)->get();

        $datastring = '';
        $myevents[] = null;
        foreach ($bar2 as $bar_row) {
            $label = $bar_row->eventStartDate->format('M Y').' '.$bar_row->event_type->etName;
            $attend = $bar_row->registrations_count;
            $there = $bar_row->attended;

            if ($there == 1) {
                array_push($myevents, $label);
            }
            $datastring .= "{ ChMtg: '".$label."', ".trans_choice('messages.headers.att', 2).': '.$attend.'},';
        }
        rtrim($datastring, ',');

        $output_string = '';
        foreach ($myevents as $single) {
            if ($single !== null) {
                $output_string .= " row.label == '".$single."' ||";
            }
        }
        if ($output_string == '') {
            $output = 'false';
        } else {
            $output = substr($output_string, 0, -3);
        }

        $topBits = '';

        return view('v1.auth_pages.dashboard', compact('attendance', 'datastring', 'output', 'topBits'));
    }

    public function show($id)
    {
        // responds to GET /activity/{id} where id = personID

        $event_list = Event::join('event-registration', 'org-event.eventID', '=', 'event-registration.eventID')
            ->where('event-registration.personID', '=', $id)
            ->selectRaw('distinct `org-event`.eventStartDate, `org-event`.eventName')
            ->orderBy('org-event.eventStartDate', 'ASC')
            ->get();
        $html = view('v1.modals.activity_modal', compact('event_list'))->render();

        return json_encode(['html' => $html]);
    }

    public function networking(Request $request)
    {
        // responds to POST to /networking
        $eventID = request()->input('eventID');
        $eventName = request()->input('eventName');

        $er = Registration::where([
            ['eventID', '=', $eventID],
            ['canNetwork', '=', 1],
        ])
            ->join('person as p', 'event-registration.personID', '=', 'p.personID')
            //    ->distinct()
            ->select('p.firstName', 'p.lastName', 'p.login', 'p.compName', 'p.indName')
            ->distinct()
            ->orderBy('p.lastName', 'asc')
            ->get();

        return json_encode(['event' => $eventName, 'data' => $er->toArray()]);
    }

    public function create()
    {
        // triggered by GET /become
        return view('v1.auth_pages.members.become');
    }

    public function become(Request $request)
    {
        // triggered by POST /become

        $new_id = request()->input('new_id');
        $cancel = request()->input('cancel');
        $prior_id = auth()->user()->id;

        // "Become" by logging in the $new_id
        $u = User::find($new_id);
        if ($u === null) {
            request()->session()->flash('alert-warning', trans('messages.errors.become_error', ['id' => $new_id]));

            return redirect()->back();
        } else {
            Auth::loginUsingId($new_id, 0);

            // Store the old and new IDs
            if ($cancel != 1) {
                Session::put('become', $new_id);
                Session::put('prior_id', $prior_id);
                Session::save();
            } else {
                Session::forget(['become', 'prior_id']);
            }

            return redirect(env('APP_URL').'/dashboard');
        }
    }

    public function store(Request $request)
    {
        // responds to POST to /blah and creates, adds, stores the event
        dd(request()->all());
    }

    public function edit($id)
    {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update(Request $request, $id)
    {
        // responds to PATCH /blah/id
    }

    public function destroy($id)
    {
        // responds to DELETE /blah/id
    }
}
