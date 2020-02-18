<?php

namespace App\Http\Controllers;

use App\Email;
use App\Notifications\NewUserAcct;
use App\Org;
use App\OrgPerson;
use App\Person;
use App\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash as Hash;
use Validator;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function (Request $request, $next) {
            if (auth()) {
                $this->currentPerson = Person::find(auth()->user()->id);
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
        $currentPerson = $this->currentPerson;
        $currentOrg    = $this->currentPerson->defaultOrg;
        return view('v1.auth_pages.admin.panel', compact('currentPerson', 'currentOrg'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('v1.auth_pages.organization.create_organization');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orgName'               => 'required|min:3|max:50',
            'orgPath'               => 'required|min:3|max:50',
            'formalName'            => 'nullable|max:255',
            'orgAddr1'              => 'nullable|max:255',
            'orgAddr2'              => 'nullable|max:255',
            'orgCity'               => 'nullable|max:100',
            'orgState'              => 'nullable|min:2|max:2',
            'orgZip'                => 'nullable|max:10',
            'orgEmail'              => 'nullable|email',
            'orgPhone'              => 'nullable|max:20',
            'orgFax'                => 'nullable',
            'adminEmail'            => 'nullable',
            'facebookURL'           => 'nullable',
            'orgURL'                => 'nullable',
            'creditLabel'           => 'required',
            'orgHandle'             => 'nullable',
            'adminContactStatement' => 'nullable',
            'techContactStatement'  => 'nullable',
            'existing_user'         => 'required_if:create_user,0',
        ]);
        $validator->setAttributeNames([
            'orgName'               => trans('messages.fields.org_name'),
            'formalName'            => trans('messages.fields.formal_name'),
            'orgAddr1'              => trans('messages.fields.org_addr1'),
            'orgAddr2'              => trans('messages.fields.org_addr2'),
            'orgCity'               => trans('messages.fields.city'),
            'orgState'              => trans('messages.fields.state'),
            'orgZip'                => trans('messages.fields.zip'),
            'orgEmail'              => trans('messages.fields.main_email'),
            'orgPhone'              => trans('messages.fields.main_number'),
            'orgFax'                => trans('messages.fields.org_fax'),
            'adminEmail'            => trans('messages.fields.admin_email'),
            'facebookURL'           => trans('messages.fields.facebook_url'),
            'orgURL'                => trans('messages.fields.org_website'),
            'creditLabel'           => trans('messages.fields.credit_label'),
            'orgHandle'             => trans('messages.fields.twitter_handle'),
            'adminContactStatement' => trans('messages.fields.admin_contact_statement'),
            'techContactStatement'  => trans('messages.fields.tech_contact_statement'),
        ]);
        if ($validator->fails()) {
            return redirect('create_organization')
                ->withErrors($validator)
                ->withInput();
        }
        $create_user = $request->input('create_user');
        $existing_user = $request->input('existing_user');
        // dd($request->input('existing_user'));
        // dd($this->currentPerson);
        $org                        = new Org();
        $org->orgName               = $request->input('orgName');
        $org->formalName            = $request->input('formalName');
        $org->orgAddr1              = $request->input('orgAddr1');
        $org->orgAddr2              = $request->input('orgAddr2');
        $org->orgCity               = $request->input('orgCity');
        $org->orgState              = $request->input('orgState');
        $org->orgZip                = $request->input('orgZip');
        $org->orgEmail              = $request->input('orgEmail');
        $org->orgPhone              = $request->input('orgPhone');
        $org->orgFax                = $request->input('orgFax');
        $org->adminEmail            = $request->input('adminEmail');
        $org->facebookURL           = $request->input('facebookURL');
        $org->orgURL                = $request->input('orgURL');
        $org->creditLabel           = $request->input('creditLabel');
        $org->orgHandle             = $request->input('orgHandle');
        $org->adminContactStatement = $request->input('adminContactStatement');
        $org->techContactStatement  = $request->input('techContactStatement');
        $org->orgPath               = $request->input('orgPath');
        $org->creatorID             = $this->currentPerson->personID;
        $org->updaterID             = $this->currentPerson->personID;
        $org->save();
        $orgID = $org->orgID;

        $email                 = request()->input('email');
        $pmiID                 = request()->input('pmiID');
        $firstName             = request()->input('firstName');
        $lastName              = request()->input('lastName');
        $password              = request()->input('password');
        $notify                = request()->input('notify');
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
        if ($create_user) {
            $op = '';
            $u  = '';
            $e  = '';
            if (check_exists('p', 1, array($firstName, $lastName, $email))
                || check_exists('e', 1, array($email)) || check_exists('op', 1, array($pmiID))) {
                // return redirect(env('APP_URL')."/newuser/create");
                return back()->withInput();
            }

            try {
                DB::beginTransaction();
                $p               = new Person;
                $p->firstName    = $firstName;
                $p->prefName     = $firstName;
                $p->lastName     = $lastName;
                $p->login        = $email;
                $p->defaultOrgID = $orgID;
                $p->creatorID    = $this->currentPerson->personID;
                $p->updaterID    = $this->currentPerson->personID;
                $p->save();

                $op = new OrgPerson;
                if ($pmiID > 0) {
                    $op->OrgStat1 = $pmiID;
                }
                $op->personID  = $p->personID;
                $op->orgID     = $orgID;
                $op->creatorID = $this->currentPerson->personID;
                $op->updaterID = $this->currentPerson->personID;
                $op->save();

                $p->defaultOrgPersonID = $op->id;
                $p->save();

                $u           = new User;
                $u->id       = $p->personID;
                $u->login    = $email;
                $u->name     = $email;
                $u->email    = $email;
                $u->password = $make_pass;
                $u->save();

                $e            = new Email;
                $e->emailADDR = $email;
                $e->personID  = $p->personID;
                $e->isPrimary = 1;
                $e->creatorID = $this->currentPerson->personID;
                $e->updaterID = $this->currentPerson->personID;
                $e->save();
                DB::commit();
            } catch (\Exception $exception) {
                request()->session()->flash('alert-danger', trans('messages.messages.user_create_fail'));
                request()->session()->flash('alert-warning', $exception->getMessage());
                DB::rollBack();
                return back()->withInput();
            }

            if ($notify) {
                $p->notify(new NewUserAcct($p, $password, auth()->user()->id));
            }
            request()->session()->flash('alert-success', trans('messages.messages.new_org_created_successfully'));
            return back();
        } else {
            $person_id = explode('-', $existing_user);
            $id_exist  = false;
            if (is_array($person_id)) {
                if (count($person_id) > 1) {
                    if (is_numeric($person_id[0])) {
                        $person = Person::find($person_id[0]);
                        if ($person) {
                            $id_exist = true;
                        }
                    }
                }
            }
            if(!$id_exist) {
                $org->forceDelete();
                request()->session()->flash('alert-warning', trans('messages.errors.user_not_found'));
                return back()->withInput();
            }
            $op = new OrgPerson;
            if ($pmiID > 0) {
                $op->OrgStat1 = $pmiID;
            }
            $op->personID  = $person_id[0];
            $op->orgID     = $orgID;
            $op->creatorID = $this->currentPerson->personID;
            $op->updaterID = $this->currentPerson->personID;
            $op->save();
            request()->session()->flash('alert-success', trans('messages.messages.new_org_created_successfully'));
            return back();
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
