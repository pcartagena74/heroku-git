<?php

namespace App\Http\Controllers;

use App\Email;
use App\EventType;
use App\Notifications\NewUserAcct;
use App\Org;
use App\OrgPerson;
use App\Permission;
use App\PermissionRole;
use App\Person;
use App\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash as Hash;
use Validator;

class OrgController extends Controller
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

    public function index()
    {
        // responds to /blah
        $this->currentPerson = Person::find(auth()->user()->id);
        $orgId               = $this->currentPerson->defaultOrgID;

        // This function will eventually need to determine if there are multiple organizations attached to the
        // $this->currentPerson and then render a page allowing selection of a new organization
        // (which would then need to be updated in defaultOrgID) and redirection to the orgsetting/{id} IF
        // user has sufficient permissions
        $currentPerson = Person::find(auth()->user()->id);
        $org_list      = $currentPerson->orgs->pluck('orgName', 'orgID')->all();

        if ($currentPerson->orgs->count() > 1) {
            return view('v1.auth_pages.organization.select_organization', compact('org_list', 'orgId'));
        } else {
            return redirect('/orgsettings/' . $orgID);
        }
    }

    public function updateDefaultOrg(Request $request)
    {
        $org                  = request()->input('org');
        $person               = Person::find(auth()->user()->id);
        $person->defaultOrgID = $org;
        if ($person->save()) {
            $message = trans('messages.messages.org_default_update_success');
            $request->session()->flash('alert-success', $message);
        } else {
            $message = trans('messages.errors.org_default_update_failed');
            $request->session()->flash('alert-danger', $message);
        }
        return redirect('/orgsettings');
    }
    public function event_defaults()
    {
        $this->currentPerson = Person::find(auth()->user()->id);
        $current_person      = $this->currentPerson;
        $org                 = Org::find($this->currentPerson->defaultOrgID);

        $discount_codes = DB::table('org-discounts')
            ->where('orgID', $org->orgID)
            ->select('discountID', 'discountCODE', 'percent')
            ->get();

        $event_types = EventType::whereIn('orgID', [1, $current_person->defaultOrgID])->get();

        return view(
            'v1.auth_pages.organization.event_defaults',
            compact('org', 'current_person', 'discount_codes', 'event_types')
        );
    }

    public function show(Request $request, $id)
    {
        // responds to GET /blah/id
        $person = Person::find(auth()->user()->id);
        if (!$person->orgs->contains('orgID', $id)) {
            $request->session()->flash('alert-danger', 'Org id not found');
            return redirect('/');
        }
        $org     = Org::find($id);
        $topBits = [];
        $cData   = DB::table('organization')->select(DB::raw('10-( isnull(OSN1) + isnull(OSN2) + isnull(OSN3) + isnull(OSN4) + isnull(OSN5) + isnull(OSN6) + isnull(OSN7) + isnull(OSN8) + isnull(OSN9) + isnull(OSN10)) as cnt'))->where('orgID', $org->orgID)->first();
        $cDate   = DB::table('organization')->select(DB::raw('10-( isnull(ODN1) + isnull(ODN2) + isnull(ODN3) + isnull(ODN4) + isnull(ODN5) + isnull(ODN6) + isnull(ODN7) + isnull(ODN8) + isnull(ODN9) + isnull(ODN10)) as cnt'))->where('orgID', $org->orgID)->first();
        array_push($topBits, [8, 'Custom Fields', $cData->cnt . "/10", null, '', '', 2]);
        array_push($topBits, [3, 'Custom Dates', $cDate->cnt . "/10", null, '', '', 2]);

        return view('v1.auth_pages.organization.settings', compact('org', 'topBits'));
    }

    public function create()
    {
        // refactored - previously in AdminController
        return view('v1.auth_pages.organization.create_organization');
    }

    public function store(Request $request)
    {
        // refactored - previously in AdminController
        $validation_rules = [
            'orgName'               => 'required|min:3|max:50',
            'orgPath'               => 'required|min:3|max:50|alpha_num|unique:organization,orgpath',
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
            'create_user'           => 'required',
            'existing_user'         => 'required_if:create_user,0',
        ];
        $create_user = $request->input('create_user');
        if (!empty($create_user) && $create_user == 1) {
            $validation_rules['email']                 = 'required|email';
            $validation_rules['firstName']             = 'required|min:3|max:50';
            $validation_rules['lastName']              = 'required|min:3|max:50';
            $validation_rules['password']              = 'required|min:6|confirmed';
            $validation_rules['password_confirmation'] = 'required|min:6';
        }
        $validation_message = [
            'existing_user.required_if' => trans('messages.validation.create_org_existing_user'),
        ];
        $validator = Validator::make($request->all(), $validation_rules, $validation_message);
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
            // dd($validator);
            return redirect('create_organization')
                ->withErrors($validator)
                ->withInput();
        }
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
        $roles = Role::where('orgID', 10)->get();
        foreach ($roles as $key => $value) {
            $new_role        = $value->replicate();
            $new_role->orgID = $orgID;
            $new_role->save();
        }

        $email                 = request()->input('email');
        $pmiID                 = request()->input('pmiID');
        $firstName             = request()->input('firstName');
        $lastName              = request()->input('lastName');
        $password              = request()->input('password');
        $notify                = request()->input('notify');
        $password_confirmation = request()->input('password_confirmation');
        // create org name path on selected disk
        generateDirectoriesForOrg($org);
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
                // assign all roles so this user will act as admin for this organization
                $all_role = Role::where('orgID', $orgID)->get();
                foreach ($all_role as $key => $value) {
                    $u->attachRole($value, ['orgID' => $orgID]);
                }

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
            if (!$id_exist) {
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
            // assign all roles so this user will act as admin for this organization
            $all_role = Role::where('orgID', $orgID)->get();
            $user     = User::find($person_id[0]);
            foreach ($all_role as $key => $value) {
                $user->attachRole($value, ['orgID' => $orgID]);
            }
            $permission = Permission::all();
            $roles      = Role::where('orgID', $orgID)
                ->where(function ($query) {
                    $query->orWhere('name', 'Board')
                        ->orWhere('name', 'Developer')
                        ->orWhere('name', 'Roundtable-Volunteer')
                        ->orWhere('name', 'Event-Volunteer');
                })->get();
            $role_permission_array = [];
            foreach ($roles as $role_key => $role_value) {
                if ($role_value->name == 'Board' || $role_value->name == 'Developer') {
                    foreach ($permission as $per_key => $per_value) {
                        $role_permission_array[] = ['permission_id' => $per_value->id, 'role_id' => $role_value->id];
                    }
                } elseif ($role_value->name == 'Event-Volunteer' || $role_value->name == 'Roundtable-Volunteer') {
                    foreach ($permission as $per_key => $per_value) {
                        if ($per_value->id == 6) {
                            $role_permission_array[] = ['permission_id' => $per_value->id, 'role_id' => $role_value->id];
                        }
                    }
                }
            }
            PermissionRole::insert($role_permission_array);
            request()->session()->flash('alert-success', trans('messages.messages.new_org_created_successfully'));
            return back();
        }
    }

    public function edit($id)
    {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update(Request $request, $id)
    {
        // responds to PATCH /blah/id
        $name    = request()->input('name');
        $value   = request()->input('value');
        $org     = Org::find($id);
        $updater = auth()->user()->id;

        if ($name == 'anonCats') {
            $value         = implode(",", (array) $value);
            $org->anonCats = $value;
        } else {
            // Add logic to change Role information if orgName changes
            $org->{$name} = $value;
        }

        $org->updaterID = $updater;
        $org->save();

    }

    public function destroy($id)
    {
        // responds to DELETE /blah/id
    }
}
