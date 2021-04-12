<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Phone;
use Illuminate\Http\Request;

class PhoneController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {
        // responds to POST to /phones/create and creates, adds, stores the phone number
        // properly takes into account whether user is changing their own info or another user's info
        $this->currentPerson = Person::find(auth()->user()->id);

        for ($i = 1; $i <= 5; $i++) {
            $adType = 'phoneType-'.$i;
            $addr1 = 'phoneNumber-'.$i;

            $type = request()->input($adType);
            $ad1 = request()->input($addr1);

            if (! empty($ad1)) {
                // check to see if this is the database already (someone else's phone)
                if ($inDB = Phone::where('phoneNumber', $ad1)->first()) {
                    // check that the phone in the database actually belongs to the personID getting edited
                    if ($inDB->personID == request()->input('personID')) {
                        $inDB->updaterID = $this->currentPerson->personID;
                        $inDB->save();
                    } else {
                        // something if the personIDs do not match
                    }
                } else {
                    $newAddr = new Phone;
                    $newAddr->personID = request()->input('personID');
                    $newAddr->phoneType = $type;
                    $newAddr->phoneNumber = $ad1;
                    $newAddr->creatorID = $this->currentPerson->personID;
                    $newAddr->updaterID = $this->currentPerson->personID;
                    $newAddr->save();
                }
            }
        }
        if ($this->currentPerson->personID == request()->input('personID')) {
            return redirect('/profile/my');
        } else {
            return redirect('/profile/'.request()->input('personID'));
        }
    }

    public function update(Request $request, $id)
    {
        // responds to POST /phone/id
        $email = Phone::find($id);
        $name = request()->input('name');
        $value = request()->input('value');
        $name = substr($name, 0, -1);
        $email->{$name} = $value;
        $email->save();
    }

    public function destroy($id)
    {
        // responds to POST /phone/id/delete
        $personID = request()->input('personID');
        $this->currentPerson = Person::find(auth()->user()->id);

        $phone = Phone::find($id);
        $phone->updaterID = $this->currentPerson->personID;
        $phone->save();
        $phone->delete();

        if (request()->input('personID') == $this->currentPerson->personID) {
            return redirect('/profile/my');
        } else {
            return redirect('/profile/'.$personID);
        }
    }
}
