<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Person;

class ActivityController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function index() {
        // responds to /blah
        $this->currentPerson = Person::find(auth()->user()->id);

        $attendance_sql = "SELECT oe.eventID, oe.eventName, oet.etName, date_format(oe.eventStartDate, '%c/%d/%Y') as eventStartDate, 
                              date_format(oe.eventEndDate, '%c/%d/%Y') AS eventEndDate
                           FROM `org-event` oe
                           JOIN `org-event_types` oet on oe.eventTypeID=oet.etID and oet.orgID=?
                           JOIN `event-registration` er on er.eventID = oe.eventID 
                           WHERE (er.regStatus='Active' or er.regStatus='In Progress') AND personID=? AND oe.deleted_at is NULL
                           ORDER BY oe.eventStartDate DESC";

        $bar_sql = "SELECT oe.eventID, date_format(oe.eventStartDate, '%b %Y') as startDate, count(er.regID) as cnt, 
                        (select count(*) from `event-registration` er2 where er2.eventID = oe.eventID and er2.personID=?) as 'attended'
                    FROM `org-event` oe
                    LEFT JOIN `event-registration` er on er.eventID=oe.eventID
                    JOIN `org-event_types` et on et.etID = oe.eventTypeID and et.orgID=?  
                    WHERE et.etID in (1, 9) and oe.isDeleted = 0 AND oe.deleted_at is NULL
                    GROUP BY eventID
                    ORDER BY oe.eventStartDate DESC
                    LIMIT 14";

        $attendance = DB::select($attendance_sql, [$this->currentPerson->defaultOrgID, $this->currentPerson->personID]);
        $bar = DB::select($bar_sql, [$this->currentPerson->personID, $this->currentPerson->defaultOrgID]);

        $datastring = "";  $myevents[] = null;
        foreach ($bar as $bar_row) {
            $label  = $bar_row->startDate;
            $attend = $bar_row->cnt;
            $there  = $bar_row->attended;

            if($there==1) {
                array_push($myevents, $label);
            }
            $datastring .= "{ ChMtg: '" . $label . "', Attendees: " . $attend . "},";
        }
        rtrim($datastring, ",");

        $output_string = "";
        foreach($myevents as $single) {
            $output_string .= " row.label == '" . $single . "' ||";
        }
        $output = substr($output_string, 0, -3);

        $topBits = '';
        return view('v1.auth_pages.dashboard', compact('attendance', 'datastring', 'output', 'topBits'));
    }

    public function show($id) {
        // responds to GET /blah/id

    }

    public function create() {
        // responds to /blah/create and shows add/edit form
    }

    public function store(Request $request) {
        // responds to POST to /blah and creates, adds, stores the event
        dd(request()->all());
    }

    public function edit($id) {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update(Request $request, $id) {
        // responds to PATCH /blah/id
    }

    public function destroy($id) {
        // responds to DELETE /blah/id
    }
}
