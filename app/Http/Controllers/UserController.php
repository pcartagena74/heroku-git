<?php

namespace App\Http\Controllers;

use App\Org;
use App\User;
use App\Person;
use App\OrgPerson;
use App\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function (Request $request, $next) {
            if(auth()){
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
                return back()->withErrors($validator);
            } else {
                $make_pass = 1;
            }
        } else {
            $make_pass = 0;
        }

        // 0. Check for the existence of records before creation (Person, Email, OrgPerson (if PMI ID), etc.)
        // 1. Create Person record (firstName => prefName)
        //    populate defaultOrgID
        //    Create corresponding orgperson record [and populate PMI ID if present]
        // 2. Create corresponding User record
        //    Check for password existence (and validation) and set if present
        // 3. Create person-email record for login

        if(check_exists('p', array($firstName, $lastName, $email))
            || check_exists('e', array($email)) || check_exists('op', array($pmiID))){
            return back()->withInput();
        }

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
        if($pmiID > 0){
            $op->OrgStat1 = $pmiID;
        }
        $op->personID = $p->personID;
        $op->orgID = $orgID;
        $op->creatorID = $this->currentPerson->personID;
        $op->updaterID = $this->currentPerson->personID;
        $op->save();

        $u = new User;
        $u->id = $p->personID;
        $u->login = $email;
        $u->name = $email;
        $u->email = $email;
        if($make_pass){
            $u->password = bcrypt($password);
        }
        $u->save();

        $e = new Email;
        $e->emailADDR = $email;
        $e->personID = $p->personID;
        $e->isPrimary = 1;
        $e->creatorID = $this->currentPerson->personID;
        $e->updaterID = $this->currentPerson->personID;
        $e->save();

        if($notify){
            // $p->notify(new AccountCreation($p, null));
        }

        // Send somewhere...
        request()->session()->flash('alert-success', trans('messages.messages.user_created'));

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