<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 0);

use App\Event;
use App\Person;
use App\RegFinance;
use App\Registration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function __construct () {
        $this->middleware('auth');
    }

    public function future_index () {
        // responds to GET /upcoming
        $this->currentPerson = Person::find(auth()->user()->id);
        $now                 = Carbon::now();

        // Registrations bought by someone else for $this->currentPerson
        $bought = Registration::where('personID', $this->currentPerson->personID)
                              ->whereHas(
                                  'event', function($q) {
                                  $q->where('eventStartDate', '>=', Carbon::now());
                              })
                              ->whereHas(
                                  'regfinance', function($q) {
                                  $q->where('personID', '!=', $this->currentPerson->personID);
                                  $q->where('pmtRecd', '=', 1);
                              })
                              ->with('event', 'ticket', 'person', 'regfinance')
                              ->get()->sortBy('event.eventStartDate');

        $paid = RegFinance::whereHas(
            'event', function($q) {
            $q->where('eventStartDate', '>=', Carbon::now());
        })
                          ->with('event', 'person', 'registrations')
                          ->where([
                              ['personID', '=', $this->currentPerson->personID],
                              ['pmtRecd', '=', 1]
                          ])
                          //->whereIn('status', [trans('messages.reg_status.active'), trans('messages.reg_status.processed')])
                          ->get()->sortBy('event.eventStartDate');

        $unpaid = RegFinance::where('personID', '=', $this->currentPerson->personID)
                            ->whereHas(
                                'event', function($q) {
                                $q->where('eventStartDate', '>=', Carbon::now());
                            })
                            ->with('event', 'person', 'registrations')
                            ->whereHas(
                                'registrations', function($q) {
                                    $q->where('pmtRecd', '=', 0);
                            })
                            ->whereIn('status', [trans('messages.reg_status.pending')])
                            ->get()->sortBy('event.eventStartDate');

        $pending = RegFinance::whereHas(
            'event', function($q) {
            $q->where('eventStartDate', '>=', Carbon::now())
              ->orderBy('eventStartDate');
        })
                             ->with('event', 'person', 'registrations')
                             ->where('personID', '=', $this->currentPerson->personID)
                             ->whereIn('status', ['pending', trans('messages.reg_status.progress')])
                             ->get()->sortBy('event.eventStartDate');

        $topBits = '';

        return view('v1.auth_pages.members.future_event_list', compact('bought', 'paid', 'unpaid', 'pending', 'topBits'));
    }

    public function index () {
        // responds to /dashboard:  This is the dashboard
        $this->currentPerson = Person::find(auth()->user()->id);

        $attendance = Event::where('er.personID', '=', $this->currentPerson->personID)
                           ->join('org-event_types as oet', function($join) {
                               $join->on('oet.etID', '=', 'org-event.eventTypeID');
                           })->join('event-registration as er', 'er.eventID', '=', 'org-event.eventID')
                           ->whereIn('oet.orgID', [1, $this->currentPerson->defaultOrgID])
                           ->where('org-event.orgID', '=', $this->currentPerson->defaultOrgID)
                           ->whereNull('er.deleted_at')
                           ->where(function($w) {
                               $w->where('er.regStatus', '=', trans('messages.reg_status.active'))
                                 ->orWhere('er.regStatus', '=', trans('messages.reg_status.processed'));
                           })
                           ->select('org-event.eventID', 'org-event.eventName', 'oet.etName',
                               'org-event.eventStartDate', 'org-event.eventEndDate',
                               DB::raw('(select count(*) from `event-registration` er2
                                        where er2.eventID = `org-event`.eventID and er2.canNetwork=1) as cnt2'))
                           ->distinct()
                           ->withCount('registrations')
                           ->orderBy('org-event.eventStartDate', 'DESC')->get(20);

        // withCount above does nothing.  See about adding the count of Networking=1 here.
        $bar_sql = "SELECT oe.eventID, date_format(oe.eventStartDate, '%b %Y') as startDate, count(er.regID) as cnt, et.etName as 'label',
                        (select count(*) from `event-registration` er2 where er2.eventID = oe.eventID and er2.personID=? and er2.deleted_at is null) as 'attended'
                    FROM `org-event` oe
                    LEFT JOIN `event-registration` er on er.eventID=oe.eventID
                    JOIN `org-event_types` et on et.etID = oe.eventTypeID AND oe.eventTypeID in (1, 9)
                    WHERE oe.isDeleted = 0 AND oe.deleted_at is NULL
                    GROUP BY eventID
                    ORDER BY oe.eventStartDate DESC
                    LIMIT 14";

        // $attendance = DB::select($attendance_sql, [$this->currentPerson->defaultOrgID, $this->currentPerson->personID]);
        $bar = DB::select($bar_sql, [$this->currentPerson->personID]);

        $datastring = "";
        $myevents[] = null;
        foreach($bar as $bar_row) {
            $label  = $bar_row->startDate . " " . $bar_row->label;
            $attend = $bar_row->cnt;
            $there  = $bar_row->attended;

            if($there == 1) {
                array_push($myevents, $label);
            }
            $datastring .= "{ ChMtg: '" . $label . "', " . trans_choice('messages.headers.att', 2) . ": " . $attend . "},";
        }
        rtrim($datastring, ",");

        $output_string = "";
        foreach($myevents as $single) {
            if($single !== null) {
                $output_string .= " row.label == '" . $single . "' ||";
            }
        }
        if($output_string == '') {
            $output = 'false';
        } else {
            $output = substr($output_string, 0, -3);
        }

        $topBits = '';
        return view('v1.auth_pages.dashboard', compact('attendance', 'datastring', 'output', 'topBits'));
    }

    public function show ($id) {
        // responds to GET /activity/{id} where id = personID

        $event_list = Event::join('event-registration', 'org-event.eventID', '=', 'event-registration.eventID')
           ->where('event-registration.personID', '=', $id)
            ->selectRaw("distinct `org-event`.eventStartDate, `org-event`.eventName")
            ->orderBy('org-event.eventStartDate','ASC')
           ->get();
        $html = view('v1.modals.activity_modal', compact('event_list'))->render();
        return json_encode(array('html'=>$html));
    }

    public function networking (Request $request) {
        // responds to POST to /networking
        $eventID   = request()->input('eventID');
        $eventName = request()->input('eventName');

        $er = Registration::where([
            ['eventID', '=', $eventID],
            ['canNetwork', '=', 1]
        ])
            ->join('person as p', 'event-registration.personID', '=', 'p.personID')
        //    ->distinct()
            ->select('p.firstName', 'p.lastName', 'p.login', 'p.compName', 'p.indName')
            ->orderBy('p.lastName', 'asc')
            ->get();
        return json_encode(array('event' => $eventName, 'data' => $er->toArray()));
    }

    public function create () {
        // triggered by GET /become
        return view('v1.auth_pages.members.become');
    }

    public function become (Request $request) {
        // triggered by POST /become
        $new_id = request()->input('new_id');
        Auth::loginUsingId($new_id, 0);
        return redirect(env('APP_URL') . "/dashboard");
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
