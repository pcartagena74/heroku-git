<?php

namespace App\Http\Controllers;

use App\Org;
use App\Permission;
use App\Person;
use App\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // DB::enableQueryLog();
        //dd(DB::getQueryLog());
    }

    protected function role_bits()
    {
        $topBits = [];
        $p = $this->currentPerson = Person::find(auth()->user()->id);

        $board = count_roles(1);
        $speaker = count_roles(2);
        $events = count_roles(3);
        $vols = count_roles(4);
        // Room for growth should another role be needed at id=5
        $spkvol = count_roles(6);
        $round = count_roles(7);
        $admin = count_roles(8);
        $mktg = count_roles(9);
        // ID 10 is the Developer role which is not advertised/counted

        array_push($topBits, [1, trans('messages.topBits.board'), $board, '', '', '']);
        array_push($topBits, [1, trans('messages.topBits.mktg'), $mktg, '', '', '']);
        array_push($topBits, [1, trans('messages.topBits.events'), $events, '', '', '']);
        array_push($topBits, [1, trans('messages.topBits.rt'), $round, '', '', '', 2]);
        array_push($topBits, [1, trans('messages.topBits.spk_vol'), $spkvol, '', '', '', 2]);
        array_push($topBits, [1, trans('messages.topBits.vol'), $vols, '', '', '', 2]);
        array_push($topBits, [1, trans('messages.topBits.speaker'), $speaker, '', '', '', 2]);
        array_push($topBits, [1, trans('messages.topBits.admin'), $admin, '', '', '']);

        return $topBits;
    }

    public function index($query = null)
    {
        // responds to GET /role_mgmt
        $topBits = $this->role_bits();
        $p = $this->currentPerson = Person::find(auth()->user()->id);
        $org = Org::find($this->currentPerson->defaultOrgID);
        $roles = Role::where('id', '<=', 10)
            // This line is to prevent the display of roles with relevant ID.  0 blocks nothing...
            ->whereNotIn('id', [0, 5])
            ->with('permissions')
            ->get();

        $permissions = Permission::all();
        $persons = null;

        // DB::enableQueryLog();

        if ($query !== null) {
            $persons = Person::orWhere('firstName', 'LIKE', "%$query%")
                ->orWhere('lastName', 'LIKE', "%$query%")
                ->orWhere('login', 'LIKE', "%$query%")
                ->orWhere('personID', 'LIKE', "%$query%")
                ->orWhereHas('orgperson', function ($q) use ($query) {
                    $q->where('OrgStat1', 'LIKE', "%$query%");
                })
                ->orWhereHas('emails', function ($q) use ($query) {
                    $q->where('emailADDR', 'LIKE', "%$query%");
                })
                ->with('roles', 'orgperson')
                ->get();
        }

        return view('v1.auth_pages.organization.role_mgmt_search',
            compact('org', 'roles', 'permissions', 'persons', 'topBits'));
    }

    public function search(Request $request)
    {
        $string = $request->input('string');

        return redirect('/role_mgmt/'.$string);
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
        //as toggle does not offer extra parameter to be added like attach does we are removing toggle and manually attaching or detaching it.
        $attach = true;
        $admin = Person::find(auth()->user()->id);
        $user_role_pivot = DB::table('role_user')
            ->select(['role_id', 'orgID'])
            ->where(['user_id' => $person->personID, 'orgID' => $admin->defaultOrgID, 'role_id' => $role->id])
            ->get();
        if ($user_role_pivot->isNotEmpty()) {
            DB::table('role_user')
                ->where(['user_id' => $person->personID, 'orgID' => $admin->defaultOrgID, 'role_id' => $role->id])
                ->delete();
        } else {
            $person->roles()->attach($role->id, ['org_id' => $admin->defaultOrgID]);
        }

        // $person->roles()->toggle($role->id, ['orgID' => $person->defaultOrg]);

        //check if user has any role associated if not do not run below code
        //not needed now as orgname role is not needed admin will be the admin of that org
        if (isset($person->org_role_id()->id) && false) {
            // Check to see if a role for the orgName is in the DB...
            if (! $person->roles->contains('id', $person->org_role_id()->id)) {
                $orgID_needed = 1;
            }
            // Remove the orgName role if it's the only one...
            if (count($person->roles) == 1 && ! $orgID_needed) {
                $person->roles->forget('id', $person->org_role_id()->id);
            }

            // ...or add the orgName role if it's needed.
            if ($orgID_needed) {
                $person->roles()->toggle($person->org_role_id()->id, ['org_id' => $person->defaultOrgID]);
            }
        }

        /* not needed as ticketit admin is fixed and agent will be org admins
        /update user as ticketit agent if is admin
        if ($role->name == 'Admin') {
        $user = User::find($person->personID);
        if ($user->ticketit_agent == 1) {
        $user->ticketit_agent = 0;
        } else {
        $user->ticketit_agent = 1;
        }
        $user->save();
        }
         */

        $message =
            '<div class="well bg-blue">'.trans(
                'messages.instructions.role_toggle',
                ['role' => $role->display_name, 'person' => $person->showFullName()]
            ).'</div>';

        return json_encode(['status' => 'success', 'message' => $message]);
    }

    public function destroy($id)
    {
        // responds to DELETE /blah/id
    }
}
