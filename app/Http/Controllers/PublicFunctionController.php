<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Person;
use App\User;
use App\OrgPerson;

class PublicFunctionController extends Controller
{

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function oLookup($pmi_id)
    {
        $op = OrgPerson::where('OrgStat1', '=', $pmi_id)->first();

        if (null !== $op) {
            $u = User::where('id', '=', $op->personID)->first();
            if (null !== $u) {
                $x = $u->password ? 1 : 0;
            } else {
                $x = 1;
            }
            $p = Person::with('orgperson')->where('personID', '=', $op->personID)->first();
            if (null === $p->orgperson) {
                $p->load('orgperson');
            }
            return json_encode(array('status' => 'success', 'p' => $p, 'pass' => $x,
                'msg' => trans('messages.modals.confirm2', ['fullname' => $p->showFullName()])));
        } else {
            return json_encode(array('status' => 'error', 'p' => null, 'op' => $op, 'pmi_id' => $pmi_id));
        }
    }
}
