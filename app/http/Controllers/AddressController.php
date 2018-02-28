<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Address;
use App\Person;

class AddressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        // responds to /blah
    }

    public function show($id)
    {
        // responds to GET /blah/id
    }

    public function create()
    {
        // responds to /blah/create and shows add/edit form
    }

    public function store(Request $request)
    {
        // responds to POST to /blah and creates, adds, stores the event
        //dd(request()->all());
        $this->currentPerson = Person::find(auth()->user()->id);

        for ($i = 1; $i <= 5; $i++) {
            $adType  = "addrTYPE-" . $i;
            $addr1   = "addr1-" . $i;
            $addr2   = "addr2-" . $i;
            $city    = "city-" . $i;
            $state   = "state-" . $i;
            $zip     = "zip-" . $i;
            $cntryID = "cntryID-" . $i;

            $type = request()->input($adType);
            $ad1  = request()->input($addr1);
            $ad2  = request()->input($addr2);
            $cit  = request()->input($city);
            $sta  = request()->input($state);
            $zi   = request()->input($zip);
            $cnt  = request()->input($cntryID);

            if (!empty($ad1)) {
                $newAddr            = new Address;
                $newAddr->personID  = request()->input('personID');
                $newAddr->addrTYPE  = $type;
                $newAddr->addr1     = $ad1;
                $newAddr->addr2     = $ad2;
                $newAddr->city      = $cit;
                $newAddr->state     = $sta;
                $newAddr->zip       = $zi;
                $newAddr->cntryID   = $cnt;
                $newAddr->creatorID = $this->currentPerson->personID;
                $newAddr->updaterID = $this->currentPerson->personID;
                $newAddr->save();
            }
        }
        if ($this->currentPerson->personID == request()->input('personID')) {
            return redirect('/profile/my');
        } else {
            return redirect("'/profile/" . request()->input('personID') . "'");
        }
    }

    public function edit($id)
    {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update(Request $request, $id)
    {
        // responds to PATCH /blah/id
        $this->currentPerson = Person::find(auth()->user()->id);
        $address = Address::find($id);
        $name    = request()->input('name');
        $name    = substr($name, 0, -1);
        $value   = request()->input('value');

        $address->{$name} = $value;
        $address->updaterID = $this->currentPerson->personID;
        $address->save();
    }

    public function destroy($id)
    {
        // responds to DELETE /blah/id
        $this->currentPerson = Person::find(auth()->user()->id);
        $address = Address::find($id);
        $address->updaterID = $this->currentPerson->personID;
        $address->save();
        $address->delete();

        if (request()->input('personID') == $this->currentPerson->personID) {
            return redirect("/profile/my");
        } else {
            return redirect("/profile/" . $this->currentPerson->personID);
        }
    }
}
