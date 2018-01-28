<?php

namespace App\Http\Controllers;

use App\Event;
use App\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function member_report()
    {
        $topBits = '';
        $this->currentPerson = Person::find(auth()->user()->id);

        $years = Event::where('orgID', $this->currentPerson->defaultOrgID)
            ->join('event-registration', 'org-event.eventID', '=', 'event-registration.eventID')
            ->select(DB::raw("year(eventStartDate) as 'year'"))
            ->distinct()->orderBy('year', 'desc')->get();

        $y = $years->toArray();
        $y1 = $y[0]['year'];
        $datastring = "";  $eplus = 0;

   /*
        { Events: '1 Event', a: 518, b: 533},
        { Events: '2 Events', a: 153, b: 200},
        { Events: '3 Events', a: 57, b: 77},
        { Events: '4 Events', a: 23, b: 25},
        { Events: '5 Events', a: 16, b: 16},
        { Events: '6 Events', a: 10, b: 2},
        { Events: '7 Events', a: 5, b: 1},
        { Events: '8+ Events', a: 15, b: 20},
    */

        $sql = "select numEvent, count(*) 'cnt'
                FROM (
                  select er.personID, count(er.personID) 'numEvent'
                  from `event-registration` er
                  join `org-event` oe on er.eventID = oe.eventID and oe.deleted_at is null
                  where year(oe.eventStartDate) = ? and er.deleted_at is null
                  group by er.personID
                ) tbl
                GROUP BY numEvent;";
        //$chart = DB::select($sql, ["$y1"]);
        $chart = DB::select($sql, ["2017"]);

        foreach($chart as $e){
            if($e->numEvent >= 8){
                $eplus += $e->cnt;
                if($e == last($chart)){
                    $datastring .= "{ Events: '8+ Events', Attendees: " . $eplus . "},";
                }
            } else {
                if($e != reset($chart)){
                    $datastring .= "\n";
                }
                if($e->numEvent == 1){
                    $datastring .= "{ Events: '" . $e->numEvent . " Event', Attendees: " . $e->cnt . "},";
                } else {
                    $datastring .= "{ Events: '" . $e->numEvent . " Events', Attendees: " . $e->cnt . "},";
                }
            }
        }
        rtrim($datastring, ",");

        return view('v1.auth_pages.members.mbr_report', compact('topBits', 'chart', 'years', 'datastring'));
    }
}
