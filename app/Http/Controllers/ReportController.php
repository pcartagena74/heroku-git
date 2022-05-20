<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Event;
use App\Models\Org;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Illuminate\Support\Facades\Lang;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show($id = null, $year_string = null)
    {
        $topBits = '';
        $this->currentPerson = Person::find(auth()->user()->id);
        $orgID = $this->currentPerson->defaultOrgID;
        $org = Org::find($orgID);
        $quote_string = Session::get('quote_string');

        if ($quote_string == "''" && null !== $year_string) {
            $year_string = null;
        }

        if (null === $year_string) {
            $year_array = Event::where('orgID', $this->currentPerson->defaultOrgID)
                ->join('event-registration', 'org-event.eventID', '=', 'event-registration.eventID')
                ->select(DB::raw("year(eventStartDate) as 'year'"))
                ->distinct()->orderBy('year', 'asc')->pluck('year');

            $year_string = implode(',', $year_array->toArray());
            $quote_string = "'".implode("','", $year_array->toArray())."'";
            $arr = $year_array->toArray();
            asort($arr);
            $arr = array_reverse($arr);
            $arr = array_slice($arr, 0, 3);
            // $limit_string was introduced to show a limited number of years by default w/o losing any data
            $limit_string = "'".implode("','", $arr)."'";
        } else {
            // This code never executes because nothing is using cookies for this data via the UI
            $year_string = Session::get('year_string');
            $quote_string = Session::get('quote_string');
            $limit_string = Session::get('limit_string');
        }

        $years = Event::where('orgID', $this->currentPerson->defaultOrgID)
            ->join('event-registration', 'org-event.eventID', '=', 'event-registration.eventID')
            ->select(DB::raw("year(eventStartDate) as 'year'"))
            ->whereIn(DB::raw('year(eventStartDate)'), explode(',', $year_string))
            ->distinct()->orderBy('year', 'asc')->get();


        $datastring = '';
        $pluses = [];
        $labels = '';

        foreach ($years as $y) {
            $pluses[$y->year] = 0;
            $labels .= "'$y->year', ";
        }
        rtrim($labels, ',');

        if($id) {
            // when $id = 1, show only member data
            $chart = DB::select('call true_member_report("'.$year_string.'", '. $orgID .')');

            $total = Person::whereNotNull('indName')
                ->where('indName', '<>', '')
                ->where('defaultOrgID', '=', $this->currentPerson->defaultOrgID)
                ->join('org-person as op', function($q) use($orgID) {
                    $q->on('op.personID', '=', 'person.personID');
                    $q->where('op.orgID', '=', $orgID);
                    $q->where('op.OrgStat1', '>', 0);
                })
                ->selectRaw("count('indName') as cnt")->first();

            $indPie = DB::select('select indName, round(count(indName)/?*100, 1) as cnt
                                 from person p
                                 join `org-person` op on (op.personID = p.personID and op.OrgStat1 > 0)
                                 join `organization` o on op.orgID = o.orgID
                                 where o.orgID = ?
                                       and indName is not null and indName <> ""
                                 group by indName', [$total->cnt, $this->currentPerson->defaultOrgID]);
        } else {
            // otherwise, show all attendee data
            $chart = DB::select('call member_report("'.$year_string.'")');

            $total = Person::whereNotNull('indName')
                ->where('indName', '<>', '')
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
        }

        foreach ($chart as $e) {
            if ($e->numEvent >= 8) {
                foreach ($years as $y) {
                    if ($e->{$y->year} === null) {
                        $e->{$y->year} = 0;
                    }
                    $pluses[$y->year] += $e->{$y->year};
                }
                if ($e == last($chart)) {
                    $datastring .= "\n{ "
                        . trans_choice('messages.headers.events',2) . ": '8+ "
                        . trans_choice('messages.headers.events',$e->numEvent) . "', '";
                    foreach ($years as $y) {
                        if ($e->{$y->year} === null) {
                            $e->{$y->year} = 0;
                        }
                        if ($y == $years->last()) {
                            $datastring .= $y->year."': ".$pluses[$y->year];
                        } else {
                            $datastring .= $y->year."': ".$pluses[$y->year].", '";
                        }
                    }
                    rtrim($datastring, ',');
                    $datastring .= '}';
                }
            } else {
                if ($e != reset($chart)) {
                    $datastring .= "\n";
                }
                $datastring .= "{ " .
                    trans_choice('messages.headers.events',2)
                    . ": '".$e->numEvent." ".
                    trans_choice('messages.headers.events',$e->numEvent) .
                    "', '";
                foreach ($years as $y) {
                    if ($e->{$y->year} === null) {
                        $e->{$y->year} = 0;
                    }
                    if ($y == $years->last()) {
                        $datastring .= $y->year."': ".$e->{$y->year};
                    } else {
                        $datastring .= $y->year."': ".$e->{$y->year}.", '";
                    }
                }
                rtrim($datastring, ',');
                $datastring .= '},';
            }
        }

        // Check for existence of "Other" to ensure the chart will work as expected
        $no_other = true;
        foreach ($indPie as $key => $value) {
            if ($value->indName == trans('messages.industries.other')) {
                $no_other = false;
            }
        }
        if ($no_other) {
            $indPie[] = (object) ['indName' => 'Other', 'cnt' => 0];
        }
        $cp = $this->currentPerson;
        $heat_map_work = Address::select(['lati', 'longi'])
            ->where('addrType', 'Work')
            ->where('lati', '!=', '0')
            ->where('longi', '!=', '0')
            ->groupby(['lati', 'longi'])
            ->having(DB::raw('count(lati)'), '>', $org->heatMapDensity)
            ->whereHas('person', function ($query) use ($cp) {
                $query->where('defaultOrgID', '=', $this->currentPerson->defaultOrgID);
            })->get()->toArray();
        $heat_map_work_count = Address::select(['lati', 'longi'])
            ->where('addrType', 'Work')
            ->where('lati', '!=', '0')
            ->where('longi', '!=', '0')
            ->whereHas('person', function ($query) use ($cp) {
                $query->where('defaultOrgID', '=', $this->currentPerson->defaultOrgID);
            })->get()->count();
        $heat_map_home = Address::select(['lati', 'longi'])
            ->where('addrType', 'Home')
            ->where('lati', '!=', '0')
            ->where('longi', '!=', '0')
            ->groupby(['lati', 'longi'])
            ->having(DB::raw('count(lati)'), '>', $org->heatMapDensity)
            ->whereHas('person', function ($query) use ($cp) {
                $query->where('defaultOrgID', '=', $this->currentPerson->defaultOrgID);
            })->get()->toArray();
        $heat_map_home_count = Address::select(['lati', 'longi'])
            ->where('addrType', 'Home')
            ->where('lati', '!=', '0')
            ->where('longi', '!=', '0')
            ->whereHas('person', function ($query) use ($cp) {
                $query->where('defaultOrgID', '=', $this->currentPerson->defaultOrgID);
            })->get()->count();
        $heat_map_other = Address::select(['lati', 'longi'])
            ->where('addrType', '!=', 'Work')
            ->where('addrType', '!=', 'Home')
            ->where('lati', '!=', '0')
            ->where('longi', '!=', '0')
            ->groupby(['lati', 'longi'])
            ->having(DB::raw('count(lati)'), '>', $org->heatMapDensity)
            ->whereHas('person', function ($query) use ($cp) {
                $query->where('defaultOrgID', '=', $this->currentPerson->defaultOrgID);
            })->get()->toArray();
        $heat_map_other_count = Address::select(['lati', 'longi'])
            ->where('addrType', '!=', 'Work')
            ->where('addrType', '!=', 'Home')
            ->where('lati', '!=', '0')
            ->where('longi', '!=', '0')
            ->whereHas('person', function ($query) use ($cp) {
                $query->where('defaultOrgID', '=', $this->currentPerson->defaultOrgID);
            })->get()->count();
        $org_lat_lng = ['lati' => 42.4072, 'longi' => -71.3824]; //Massachusetts lat lng default
        $organization = Org::where('orgID', $orgID)->get()->first();
        if (! empty($organization->lati) && ! empty($organization->longi)) {
            $org_lat_lng['lati'] = $organization->lati;
            $org_lat_lng['longi'] = $organization->longi;
        } else {
            if (! empty($organization->orgAddr1) && ! empty($organization->orgCity) && ! empty($organization->orgState) && ! empty($organization->orgZip)) {
                generateLatLngForAddress($organization, true);
                $org_lat_lng['lati'] = $organization->lati;
                $org_lat_lng['longi'] = $organization->longi;
            } elseif (! empty($organization->orgZip)) {
                $zip_lat_lng = DB::table('ziplatlng')->where('zip', $organization->orgZip)->get()->first();
                if (empty($zip_lat_lng)) {
                    // $org_lat_lng['lati']  = 42.3601;//boston
                    // $org_lat_lng['longi'] = -71.0589;
                } else {
                    $org_lat_lng['lati'] = $zip_lat_lng->lat;
                    $org_lat_lng['longi'] = $zip_lat_lng->lng;
                }
            }
        }

        return view('v1.auth_pages.members.mbr_report', compact('topBits', 'chart', 'years', 'org',
            'datastring', 'labels', 'indPie', 'year_string', 'quote_string', 'orgID', 'heat_map_home', 'limit_string', 'id',
            'heat_map_other', 'heat_map_work', 'org_lat_lng', 'heat_map_work_count', 'heat_map_home_count', 'heat_map_other_count'));
    }

    public function update(Request $request, $id)
    {
        // POST /mbrreport/{id} -- $id is meaningless

        $pk = request()->input('pk');
        $name = request()->input('name');
        $value = request()->input('value');

        if ($name == 'tags') {
            $quote = "'".implode("','", (array) $value)."'";
            $value = implode(',', (array) $value);

            Session::put('year_string', $value);
            Session::put('quote_string', $quote);
            Session::save();

            //return json_encode(array('status' => 'success', 'name' => $name, 'value' => $value, 'pk' => $pk));
        }
    }
}
