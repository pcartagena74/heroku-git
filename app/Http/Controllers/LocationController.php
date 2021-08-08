<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // responds to GET /locations
        $this->currentPerson = Person::find(auth()->user()->id);
        $locations = DB::table('event-location')->where('orgID', $this->currentPerson->defaultOrgID)
                                 ->whereNull('deleted_at')
                                 ->orderBy('locName')
                                 ->select(
                                     'locID',
                                     'locName',
                                     'addr1',
                                     'addr2',
                                     'city',
                                     'state',
                                     'zip',
                                     DB::raw('(select count(*) from `org-event` oe where oe.locationID = `event-location`.locID) as cnt'),
                                     'locNote'
                                 )->get();

        $topBits = '';

        return view('v1.auth_pages.events.list-locations', compact('locations', 'topBits'));
    }

    public function show($id)
    {
        // responds to GET /blah/id
        $loc = Location::find($id);

        return $loc->toJson();
    }

    public function create()
    {
        // responds to /blah/create and shows add/edit form
    }

    public function store(Request $request)
    {
        // responds to POST to /locations/create and creates, adds, stores the event
        $this->currentPerson = Person::find(auth()->user()->id);
        $count = 0;

        for ($i = 1; $i <= 5; $i++) {
            $locName = 'locName-'.$i;
            $addr1 = 'addr1-'.$i;
            $addr2 = 'addr2-'.$i;
            $city = 'city-'.$i;
            $state = 'state-'.$i;
            $zip = 'zip-'.$i;
            $cntryID = 'cntryID-'.$i;

            $loc = request()->input($locName);
            $ad1 = request()->input($addr1);
            $ad2 = request()->input($addr2);
            $cit = request()->input($city);
            $sta = request()->input($state);
            $zi = request()->input($zip);
            $cnt = request()->input($cntryID);

            if (! empty($loc)) {
                $count++;
                $l = new Location;
                $l->orgID = $this->currentPerson->defaultOrgID;
                $l->locName = $loc;
                $l->addr1 = $ad1;
                $l->addr2 = $ad2;
                $l->city = $cit;
                $l->state = $sta;
                $l->zip = $zi;
                $l->countryID = $cnt;
                $l->creatorID = $this->currentPerson->personID;
                $l->updaterID = $this->currentPerson->personID;
                $l->save();
            }
        }
        request()->session()->flash('alert-success', trans_choice('messages.headers.count_added', $count, ['count' => $count]));

        return redirect(env('APP_URL').'/locations');
    }

    public function edit($id)
    {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update(Request $request)
    {
        // responds to POST /location/update
        $this->currentPerson = Person::find(auth()->user()->id);
        $locID = request()->input('locID');
        $action = request()->input('action');
        $location = Location::find($locID);

        if ($action != 'delete') {
            if (request()->input('locName')) {
                $location->locName = request()->input('locName');
            } elseif (request()->input('addr1')) {
                $location->addr1 = request()->input('addr1');
            } elseif (request()->input('addr2')) {
                $location->addr2 = request()->input('addr2');
            } elseif (request()->input('city')) {
                $location->city = request()->input('city');
            } elseif (request()->input('state')) {
                $location->state = request()->input('state');
            } elseif (request()->input('zip')) {
                $location->zip = request()->input('zip');
            }
            $location->updaterID = $this->currentPerson->personID;
            $location->save();
        } else {
            $location->updaterID = $this->currentPerson->personID;
            $location->save();
            $location->delete();
        }

        return json_encode(['input' => request()->all(), 'personID' => $this->currentPerson->personID, 'orgID' => $this->currentPerson->defaultOrgID]);
    }

    public function destroy($id)
    {
        // responds to DELETE /blah/id
    }
}
