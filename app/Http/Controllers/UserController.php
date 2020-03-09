<?php

namespace App\Http\Controllers;

use App\Org;
use App\User;
use App\Person;
use App\OrgPerson;
use App\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Notifications\NewUserAcct;
use DB;
use Illuminate\Support\Facades\Hash as Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function (Request $request, $next) {
            if (auth()) {
                $this->currentPerson = Person::find(auth()->user()->id);
            } else {
                $this->currentPerson = null;
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // responds to GET /newuser
        $org = Org::find($this->currentPerson->defaultOrgID);
        return view('v1.auth_pages.admin.newuser', compact('org'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // responds to POST /newuser/create

        $orgID = $this->currentPerson->defaultOrgID;
        $email = request()->input('email');
        $pmiID = request()->input('pmiID');
        $firstName = request()->input('firstName');
        $lastName = request()->input('lastName');
        $password = request()->input('password');
        $notify = request()->input('notify');
        $password_confirmation = request()->input('password_confirmation');

        if ($password !== null) {
            // validate password matching
            $validator = Validator::make($request->all(), [
                'password' => 'required|min:6|confirmed',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            } else {
                $make_pass = Hash::make($password);
            }
        } else {
            $make_pass = null;
        }

        // 0. Check for the existence of records before creation (Person, Email, OrgPerson (if PMI ID), etc.)
        // 1. Create Person record (firstName => prefName)
        //    populate defaultOrgID
        //    Create corresponding orgperson record [and populate PMI ID if present]
        // 2. Create corresponding User record
        //    Check for password existence (and validation) and set if present
        // 3. Create person-email record for login

        if (check_exists('p', 1, array($firstName, $lastName, $email))
            || check_exists('e', 1, array($email)) || check_exists('op', 1, array($pmiID))) {
            // return redirect(env('APP_URL')."/newuser/create");
            return back()->withInput();
        }
        $e = null;
        try {
            DB::beginTransaction();
            $p = new Person;
            $p->firstName = $firstName;
            $p->prefName = $firstName;
            $p->lastName = $lastName;
            $p->login = $email;
            $p->defaultOrgID = $orgID;
            $p->creatorID = $this->currentPerson->personID;
            $p->updaterID = $this->currentPerson->personID;
            $p->save();

            $op = new OrgPerson;
            if ($pmiID > 0) {
                $op->OrgStat1 = $pmiID;
            }
            $op->personID = $p->personID;
            $op->orgID = $orgID;
            $op->creatorID = $this->currentPerson->personID;
            $op->updaterID = $this->currentPerson->personID;
            $op->save();

            $p->defaultOrgPersonID = $op->id;
            $p->save();

            $u = new User;
            $u->id = $p->personID;
            $u->login = $email;
            $u->name = $email;
            $u->email = $email;
            $u->password = $make_pass;
            $u->save();

            $e = new Email;
            $e->emailADDR = $email;
            $e->personID = $p->personID;
            $e->isPrimary = 1;
            $e->creatorID = $this->currentPerson->personID;
            $e->updaterID = $this->currentPerson->personID;
            $e->save();
            DB::commit();
        } catch (\Exception $exception) {
            request()->session()->flash('alert-danger', trans('messages.messages.user_create_fail'));
            request()->session()->flash('alert-warning', "Person: $p, OP: $op, U: $u, E: $e");
            DB::rollBack();
            return back()->withInput();
        }

        if ($notify) {
            $p->notify(new NewUserAcct($p, $password, auth()->user()->id));
        }

        // Send back to same screen but with success message
        $button = "<a class='btn btn-xs btn-primary' href='" . env('APP_URL') . "/profile/$p->personID'><i class='far fa-id-card'></i></a>";
        request()->session()->flash('alert-success', trans('messages.messages.user_created', ['profile_button' => $button]));
        return redirect(env('APP_URL')."/newuser/create");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
