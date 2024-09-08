<?php

namespace App\Http\Controllers;

use App\Models\OrgPerson;
use App\Models\Person;
use App\Models\User;

class PublicFunctionController extends Controller
{
    public function __construct()
    {
        //$this->middleware('guest');
    }

    public function oLookup($pmi_id)
    {
        $op = OrgPerson::where('OrgStat1', '=', $pmi_id)->first();

        if ($op !== null) {
            $u = User::where('id', '=', $op->personID)->first();
            if ($u !== null) {
                $x = $u->password ? 1 : 0;
            } else {
                // Technically, getting here means that the user record doesn't exist BUT should
                $x = 1;
            }
            $p = Person::with('orgperson')->where('personID', '=', $op->personID)->first();
            if ($p !== null && $p->orgperson !== null) {
                return json_encode(['status' => 'success', 'p' => $p, 'pass' => $x,
                    'msg' => trans('messages.modals.confirm2', ['fullname' => $p->showFullName()]), ]);
            } elseif ($p !== null) {
                $p->load('orgperson');

                return json_encode(['status' => 'success', 'p' => $p, 'pass' => $x,
                    'msg' => trans('messages.modals.confirm2', ['fullname' => $p->showFullName()]), ]);
            }
        } else {
            return json_encode(['status' => 'error', 'p' => null, 'op' => $op, 'pmi_id' => $pmi_id]);
        }
    }
}
