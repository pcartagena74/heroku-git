<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 0);

use App\Event;
use App\RegFinance;
use App\Registration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Person;

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
                                  $q->where('regStatus', '!=', 'In Progress');
                              })
                              ->with('event', 'ticket', 'person', 'regfinance')
                              ->get()->sortBy('event.eventStartDate');

        $paid = RegFinance::whereHas(
            'event', function($q) {
            $q->where('eventStartDate', '>=', Carbon::now());
        })
                          ->with('event', 'ticket', 'person', 'registration')
                          ->where('personID', '=', $this->currentPerson->personID)
                          ->whereIn('status', ['Active', 'Processed'])
                          ->get()->sortBy('event.eventStartDate');

        $unpaid = RegFinance::where('personID', '=', $this->currentPerson->personID)
                            ->whereHas(
                                'event', function($q) {
                                $q->where('eventStartDate', '>=', Carbon::now());
                            })
                            ->with('event', 'ticket', 'person', 'registration')
                            ->whereIn('status', ['Payment Pending'])
                            ->get()->sortBy('event.eventStartDate');

        $pending = RegFinance::whereHas(
            'event', function($q) {
            $q->where('eventStartDate', '>=', Carbon::now())
              ->orderBy('eventStartDate');
        })
                             ->with('event', 'ticket', 'person', 'registration')
                             ->where('personID', '=', $this->currentPerson->personID)
                             ->whereIn('status', ['pending', 'In Progress'])
                             ->get()->sortBy('event.eventStartDate');

        $topBits = '';
        return view('v1.auth_pages.members.future_event_list', compact('bought', 'paid', 'unpaid', 'pending', 'topBits'));
    }

    public function index () {
        // responds to /dashboard:  This is the dashboard
        $this->currentPerson = Person::find(auth()->user()->id);
        /*
                // This is deprecated code because withCount is not available with DB::table()
                $attendance = DB::table('org-event as oe')
                                ->join('org-event_types as oet', function($join) {
                                    $join->on('oet.etID', '=', 'oe.eventTypeID');
                                })->join('event-registration as er', 'er.eventID', '=', 'oe.eventID')
                                ->where('er.personID', '=', $this->currentPerson->personID)
                                ->whereIn('oet.orgID', [1, $this->currentPerson->defaultOrgID])
                                ->where('oe.orgID', '=', $this->currentPerson->defaultOrgID)
                                ->where(function($w) {
                                    $w->where('er.regStatus', '=', 'Active')
                                      ->orWhere('er.regStatus', '=', 'In Progress');
                                })
                                ->select('oe.eventID', 'oe.eventName', 'oet.etName', 'oe.eventStartDate', 'oe.eventEndDate')
                                ->orderBy('oe.eventStartDate', 'DESC')->paginate(20);
        */
        $attendance = Event::where('er.personID', '=', $this->currentPerson->personID)
                           ->join('org-event_types as oet', function($join) {
                               $join->on('oet.etID', '=', 'org-event.eventTypeID');
                           })->join('event-registration as er', 'er.eventID', '=', 'org-event.eventID')
                           ->whereIn('oet.orgID', [1, $this->currentPerson->defaultOrgID])
                           ->where('org-event.orgID', '=', $this->currentPerson->defaultOrgID)
                           ->where(function($w) {
                               $w->where('er.regStatus', '=', 'Active')
                                 ->orWhere('er.regStatus', '=', 'In Progress');
                           })
                           ->select('org-event.eventID', 'org-event.eventName', 'oet.etName',
                               'org-event.eventStartDate', 'org-event.eventEndDate',
                               DB::raw('(select count(*) from `event-registration` er2
                                        where er2.eventID = `org-event`.eventID and er2.canNetwork=1) as cnt2'))
                           ->withCount('registrations')
                           ->orderBy('org-event.eventStartDate', 'DESC')->get(20);

        // withCount above does nothing.  See about adding the count of Networking=1 here.
        $bar_sql = "SELECT oe.eventID, date_format(oe.eventStartDate, '%b %Y') as startDate, count(er.regID) as cnt, et.etName as 'label',
                        (select count(*) from `event-registration` er2 where er2.eventID = oe.eventID and er2.personID=?) as 'attended'
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
            $datastring .= "{ ChMtg: '" . $label . "', Attendees: " . $attend . "},";
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
        // responds to GET /blah/id
    }

    public function networking (Request $request) {
        // responds to POST to /networking
        $eventID   = request()->input('eventID');
        $eventName = request()->input('eventName');
        $r         = DB::table('event-registration as er')
                       ->where([
                           ['er.eventID', $eventID],
                           ['er.canNetwork', 1]
                       ])
                       ->join('person as p', 'er.personID', '=', 'p.personID')
                       ->select('p.firstName', 'p.lastName', 'p.login', 'p.compName', 'indName')
                       ->get();
        return json_encode(array('event' => $eventName, 'data' => $r->toArray()));
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
