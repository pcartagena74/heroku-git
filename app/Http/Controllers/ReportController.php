<?php

namespace App\Http\Controllers;

use App\Event;
use App\Person;
use Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show($year_string = null)
    {
        $topBits = '';
        $this->currentPerson = Person::find(auth()->user()->id);
        $orgID = $this->currentPerson->defaultOrgID;
        $quote_string = Session::get('quote_string');

        if($quote_string == "''" && null !== $year_string){
            $year_string = null;
        }

        if(null === $year_string){
            $year_array = Event::where('orgID', $this->currentPerson->defaultOrgID)
                ->join('event-registration', 'org-event.eventID', '=', 'event-registration.eventID')
                ->select(DB::raw("year(eventStartDate) as 'year'"))
                ->distinct()->orderBy('year', 'asc')->pluck('year');

            $year_string = implode(",", $year_array->toArray());
            $quote_string = "'" . implode("','", $year_array->toArray()). "'";
        } else {
            $year_string = Session::get('year_string');
            $quote_string = Session::get('quote_string');
        }

        $years = Event::where('orgID', $this->currentPerson->defaultOrgID)
            ->join('event-registration', 'org-event.eventID', '=', 'event-registration.eventID')
            ->select(DB::raw("year(eventStartDate) as 'year'"))
            ->whereIn(DB::raw('year(eventStartDate)'), explode(",", $year_string))
            ->distinct()->orderBy('year', 'asc')->get();

        $datastring = "";
        $pluses = array();
        $labels = "";

        foreach ($years as $y) {
            $pluses{$y->year} = 0;
            $labels .= "'$y->year', ";
        }
        rtrim($labels, ",");

        $chart = DB::select('call member_report("' . $year_string . '")');

        foreach ($chart as $e) {
            if ($e->numEvent >= 8) {
                foreach ($years as $y) {
                    if ($e->{$y->year} === null) {
                        $e->{$y->year} = 0;
                    }
                    $pluses{$y->year} += $e->{$y->year};
                }
                if ($e == last($chart)) {
                    $datastring .= "{ Events: '8+ Events', '";
                    foreach ($years as $y) {
                        if ($e->{$y->year} === null) {
                            $e->{$y->year} = 0;
                        }
                        if ($y == $years->last()) {
                            $datastring .= $y->year . "': " . $pluses{$y->year};
                        } else {
                            $datastring .= $y->year . "': " . $pluses{$y->year} . ", '";
                        }
                    }
                    rtrim($datastring, ",");
                    $datastring .= "}";
                }
            } else {
                if ($e != reset($chart)) {
                    $datastring .= "\n";
                }
                if ($e->numEvent == 1) {
                    $datastring .= "{ Events: '" . $e->numEvent . " Event', '";
                    foreach ($years as $y) {
                        if ($e->{$y->year} === null) {
                            $e->{$y->year} = 0;
                        }
                        if ($y == $years->last()) {
                            $datastring .= $y->year . "': " . $e->{$y->year};
                        } else {
                            $datastring .= $y->year . "': " . $e->{$y->year} . ", '";
                        }
                    }
                    rtrim($datastring, ",");
                    $datastring .= "},";
                } else {
                    $datastring .= "{ Events: '" . $e->numEvent . " Events', '";
                    foreach ($years as $y) {
                        if ($e->{$y->year} === null) {
                            $e->{$y->year} = 0;
                        }
                        if ($y == $years->last()) {
                            $datastring .= $y->year . "': " . $e->{$y->year};
                        } else {
                            $datastring .= $y->year . "': " . $e->{$y->year} . ", '";
                        }
                    }
                    rtrim($datastring, ",");
                    $datastring .= "},";
                }
            }
        }

        $total = Person::whereNotNull('indName')
            ->where('indName', '<>', "")
            ->where('defaultOrgID', '=', $this->currentPerson->defaultOrgID)
            ->selectRaw("count('indName') as cnt")->first();
        // DB::select('select count(indName) as total from person p where p.defaultOrgID = ? and indName is not null and indName <> "" ', [$this->currentPerson->defaultOrgID]);


        $indPie = DB::select('select indName, round(count(indName)/?*100, 1) as cnt
                                 from person p 
                                 join `org-person` op on op.personID = p.personID
                                 join `organization` o on op.orgID = o.orgID 
                                 where o.orgID = ?
                                       and indName is not null and indName <> ""
                                 group by indName', [$total->cnt, $this->currentPerson->defaultOrgID]);

        return view('v1.auth_pages.members.mbr_report', compact('topBits', 'chart', 'years',
            'datastring', 'labels', 'indPie', 'year_string', 'quote_string', 'orgID'));
    }

    public function update(Request $request, $id)
    {
        // POST /mbrreport/{id} -- $id is meaningless

        $pk = request()->input('pk');
        $name = request()->input('name');
        $value = request()->input('value');

        if($name == 'tags'){
            $quote = "'" . implode("','", (array)$value) . "'";
            $value = implode(",", (array)$value);

            Session::put('year_string', $value);
            Session::put('quote_string', $quote);
            Session::save();

            //return json_encode(array('status' => 'success', 'name' => $name, 'value' => $value, 'pk' => $pk));
        }
    }
}
