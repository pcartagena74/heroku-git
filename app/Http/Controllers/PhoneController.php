<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Person;
use App\PersonPhone;

class PhoneController extends Controller
{
    public function __construct () {
        $this->middleware('auth');
    }

    public function store (Request $request) {
        // responds to POST to /blah and creates, adds, stores the event
        $this->currentPerson = Person::find(auth()->user()->id);

        for($i = 1; $i <= 5; $i++) {
            $adType = "phoneType-" . $i;
            $addr1  = "phoneNumber-" . $i;

            $type = request()->input($adType);
            $ad1  = request()->input($addr1);

            if(!empty($ad1)) {
                // check to see if this is the database already (someone else's phone)
                if($inDB = PersonPhone::where('phoneNumber', $ad1)->first()) {
                    // check that the phone in the database actually belongs to the personID getting edited
                    if($inDB->personID == request()->input('personID')) {
                        $inDB->updaterID = $this->currentPerson->personID;
                        $inDB->save();
                    } else {
                        // something if the personIDs do not match
                    }
                } else {
                    $newAddr              = new PersonPhone;
                    $newAddr->personID    = request()->input('personID');
                    $newAddr->phoneType   = $type;
                    $newAddr->phoneNumber = $ad1;
                    $newAddr->creatorID   = $this->currentPerson->personID;
                    $newAddr->updaterID   = $this->currentPerson->personID;
                    $newAddr->save();
                }
            }
        }
        if($this->currentPerson->personID == request()->input('personID')) {
            return redirect('/profile/my');
        } else {
            return redirect("/profile/" . request()->input('personID'));
        }
    }

    public function update (Request $request, $id) {
        // responds to PATCH /blah/id
        $email          = PersonPhone::find($id);
        $name           = request()->input('name');
        $value          = request()->input('value');
        $name           = substr($name, 0, -1);
        $email->{$name} = $value;
        $email->save();
    }

    public function destroy ($id) {
        // responds to DELETE /blah/id
        $personID = request()->input('personID');
        $this->currentPerson = Person::find(auth()->user()->id);
        $email               = PersonPhone::find($id);
        $email->updaterID    = $this->currentPerson->personID;
        $email->save();
        $email->delete();

        if(request()->input('personID') == $this->currentPerson->personID) {
            return redirect("/profile/my");
        } else {
            return redirect("/profile/" . $personID);
        }
    }
}
