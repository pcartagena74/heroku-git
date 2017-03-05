<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Location;
use App\Person;

class LocationController extends Controller
{
    public function __construct () {
        $this->middleware('auth');
    }

    public function index () {
        // responds to /blah
        $this->currentPerson = Person::find(auth()->user()->id);
        $locations           = DB::table('event-location')->where('orgID', $this->currentPerson->defaultOrgID)
                                 ->select('locID', 'locName', 'addr1', 'addr2', 'city', 'state', 'zip')->get();
        //dd($locations);
        $topBits = '';
        return view('v1.auth_pages.events.list-locations', compact('locations', 'topBits'));
    }

    public function show ($id) {
        // responds to GET /blah/id
        $loc = Location::find($id);
        return $loc->toJson();
    }

    public function create () {
        // responds to /blah/create and shows add/edit form
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
        $this->currentPerson = Person::find(auth()->user()->id);
        $locID               = request()->input('locID');
        $location            = Location::find($locID);

        if(request()->input('locName')) {
            $location->locName = request()->input('locName');
        } elseif(request()->input('addr1')) {
            $location->addr1 = request()->input('addr1');
        } elseif(request()->input('addr2')) {
            $location->addr2 = request()->input('addr2');
        } elseif(request()->input('city')) {
            $location->city = request()->input('city');
        } elseif(request()->input('state')) {
            $location->state = request()->input('state');
        } elseif(request()->input('zip')) {
            $location->zip = request()->input('zip');
        }
        $location->updaterID = $this->currentPerson->personID;
        $location->save();

        return json_encode(array('input' => request(), 'personID' => $this->currentPerson->personID, 'orgID' => $this->currentPerson->defaultOrgID));
    }

    public function destroy ($id) {
        // responds to DELETE /blah/id
    }
}
