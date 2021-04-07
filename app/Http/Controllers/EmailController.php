<?php

namespace App\Http\Controllers;

use App\Email;
use App\Person;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['show']]);
    }

    public function store(Request $request)
    {
        // responds to POST to /blah and creates, adds, stores the event
        $this->currentPerson = Person::find(auth()->user()->id);

        for ($i = 1; $i <= 5; $i++) {
            $adType = 'emailTYPE-'.$i;
            $addr1 = 'emailADDR-'.$i;

            $type = request()->input($adType);
            $ad1 = request()->input($addr1);

            if (! empty($ad1)) {
                // check to see if this is the database already (someone else's email or in a deleted state)
                if ($inDB = Email::withTrashed()->where('emailADDR', $ad1)->first()) {
                    // check that the email in the database actually belongs to the personID getting edited
                    if ($inDB->personID == request()->input('personID')) {
                        $inDB->updaterID = $this->currentPerson->personID;
                        $inDB->save();
                        $inDB->restore();
                    } else {
                        // something if the personIDs do not match
                    }
                } else {
                    $newAddr = new Email;
                    $newAddr->personID = request()->input('personID');
                    $newAddr->emailTYPE = $type;
                    $newAddr->emailADDR = $ad1;
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
        // responds to PATCH /blah/id
        $email = Email::find($id);
        $name = request()->input('name');
        $value = request()->input('value');
        $name = substr($name, 0, -1);
        $email->{$name} = $value;
        $email->save();
    }

    public function destroy($id)
    {
        // responds to DELETE /blah/id
        $now = Carbon::now()->format('Ymd\TH:i:s.uP');
        $this->currentPerson = Person::find(auth()->user()->id);
        $email = Email::find($id);
        $email->emailADDR = 'deleted_'.$email->emailADDR."_$now";
        $email->updaterID = $this->currentPerson->personID;
        $email->save();
        $email->delete();

        $personID = request()->input('personID');

        if ($personID == $this->currentPerson->personID) {
            return redirect('/profile/my');
        } else {
            return redirect('/profile/'.$personID);
        }
    }

    public function show($email)
    {
        $e = Email::where('emailADDR', '=', $email)->first();

        if (null !== $e) {
            $u = User::where('id', '=', $e->personID)->first();
            $p = Person::with('orgperson')->where('personID', '=', $e->personID)->first();
            if (null === $p->orgperson) {
                $p->load('orgperson');
            }

            return json_encode(['status' => 'success', 'p' => $p, 'pass' => $u->password ? 1 : 0,
                               'msg' => trans('messages.modals.confirm', ['fullname' => $p->showFullName()]), ]);
        } else {
            return json_encode(['status' => 'error', 'p' => null, 'e' => $e, 'email' => $email]);
        }
    }
}
