<?php

namespace App\Http\Controllers;

use App\Org;
use App\Permission;
use App\Person;
use App\Role;
use Illuminate\Http\Request;
use App\Event;
use App\EventDiscount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // responds to GET /role_mgmt
        $this->currentPerson = Person::find(auth()->user()->id);
        $org                 = Org::find($this->currentPerson->defaultOrgID);
        $roles               = Role::where([
            ['orgID', '=', $org->orgID],
            ['name', '!=', $org->orgName]
        ])
            // This line is to prevent the display of roles with relevant ID.  0 blocks nothing...
                                   ->whereNotIn('id', [0])
                                   ->with('permissions')
                                   ->get();

        $permissions = Permission::all();

        $persons = Cache::get('all_people', function () {
            $org = Org::find($this->currentPerson->defaultOrgID);
            return Person::join('org-person as op', 'op.personID', '=', 'person.personID')
                         ->with('roles')
                         ->where([
                             ['person.personID', '!=', 1],
                             ['op.orgID', '=', $org->orgID],
                         ])
                         ->select(DB::raw('person.personID, person.lastName, person.firstName, person.login, op.OrgStat1'))
                         ->get();
        });

        return view('v1.auth_pages.organization.role_mgmt', compact('org', 'roles', 'permissions', 'persons'));
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
        dd(request()->all());
    }

    public function edit($id)
    {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update(Request $request, Person $person, Role $role)
    {
        // responds to POST /role/{person}/{id}
        $orgID_needed = 0;

        // toggle the role selected
        $person->roles()->toggle($role->id);

        // Check to see if a role for the orgName is in the DB...
        if (!$person->roles->contains('id', $person->org_role_id()->id)) {
            $orgID_needed = 1;
        }

        // Remove the orgName role if it's the only one...
        if (count($person->roles) == 1 && !$orgID_needed) {
            $person->roles->forget('id', $person->org_role_id()->id);
        }

        // ...or add the orgName role if it's needed.
        if ($orgID_needed) {
            $person->roles()->toggle($person->org_role_id()->id);
        }

        $message =
            '<div class="well bg-blue">' . trans('messages.instructions.role_toggle',
            ['role' => $role->display_name, 'person' => $person->showFullName()]) . "</div>";

        return json_encode(array('status' => 'success', 'message' => $message));
    }

    public function destroy($id)
    {
        // responds to DELETE /blah/id
    }
}
