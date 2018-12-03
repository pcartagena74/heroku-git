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

    protected function role_bits(){
        $topBits             = [];
        $this->currentPerson = Person::find(auth()->user()->id);

        $org = Org::find($this->currentPerson->defaultOrgID);

        $board = Person::whereHas('orgperson', function($q) use($org){
            $q->where('orgID', '=', $org->orgID);
        })->whereHas('roles', function($q) use($org){
            $q->where([ ['id', '=', 1], ['orgID', '=', $org->orgID] ]); })->count();

        $speaker = Person::whereHas('orgperson', function($q) use($org){
            $q->where('orgID', '=', $org->orgID);
        })->whereHas('roles', function($q) use($org){
            $q->where([ ['id', '=', 2], ['orgID', '=', $org->orgID] ]); })->count();

        $events = Person::whereHas('orgperson', function($q) use($org){
            $q->where('orgID', '=', $org->orgID);
        })->whereHas('roles', function($q) use($org){
            $q->where([ ['id', '=', 3], ['orgID', '=', $org->orgID] ]); })->count();

        $vols = Person::whereHas('orgperson', function($q) use($org){
            $q->where('orgID', '=', $org->orgID);
        })->whereHas('roles', function($q) use($org){
            $q->where([ ['id', '=', 4], ['orgID', '=', $org->orgID] ]); })->count();

        $spkvol = Person::whereHas('orgperson', function($q) use($org){
            $q->where('orgID', '=', $org->orgID);
        })->whereHas('roles', function($q) use($org){
            $q->where([ ['id', '=', 6], ['orgID', '=', $org->orgID] ]); })->count();

        $round = Person::whereHas('orgperson', function($q) use($org){
            $q->where('orgID', '=', $org->orgID);
        })->whereHas('roles', function($q) use($org){
            $q->where([ ['id', '=', 7], ['orgID', '=', $org->orgID] ]); })->count();

        $admin = Person::whereHas('orgperson', function($q) use($org){
            $q->where('orgID', '=', $org->orgID);
        })->whereHas('roles', function($q) use($org){
            $q->where([ ['id', '=', 8], ['orgID', '=', $org->orgID] ]); })->count();

        $mktg = Person::whereHas('orgperson', function($q) use($org){
            $q->where('orgID', '=', $org->orgID);
        })->whereHas('roles', function($q) use($org){
            $q->where([ ['id', '=', 10], ['orgID', '=', $org->orgID] ]); })->count();

        array_push($topBits, [1, trans('messages.topBits.board'), $board, '', '', '']);
        array_push($topBits, [1, trans('messages.topBits.mktg'), $mktg, '', '', '']);
        array_push($topBits, [1, trans('messages.topBits.events'), $events, '', '', '']);
        array_push($topBits, [1, trans('messages.topBits.rt'), $round, '', '', '', 2]);
        array_push($topBits, [1, trans('messages.topBits.spk_vol'), $spkvol, '', '', '', 2]);
        array_push($topBits, [1, trans('messages.topBits.vol'), $vols, '', '', '', 2]);
        array_push($topBits, [1, trans('messages.topBits.speaker'), $speaker, '', '', '', 2]);
        array_push($topBits, [1, trans('messages.topBits.admin'), $admin, '', '', '']);

        return($topBits);
    }

    public function index($query = null)
    {
        // responds to GET /role_mgmt
        $topBits             = $this->role_bits();
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
        $persons = null;

        if($query !== null){
            $persons = Person::where('firstName', 'LIKE', "%$query%")
                ->orWhere('person.personID', 'LIKE', "%$query%")
                ->orWhere('lastName', 'LIKE', "%$query%")
                ->orWhere('login', 'LIKE', "%$query%")
                ->orWhereHas('orgperson', function ($q) use ($query) {
                    $q->where('OrgStat1', 'LIKE', "%$query%");
                })
                ->orWhereHas('emails', function ($q) use ($query) {
                    $q->where('emailADDR', 'LIKE', "%$query%");
                })
                ->orWhereHas('roles', function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%query%");
                })
                ->whereHas('orgs', function ($q) {
                    $q->where('organization.orgID', '=', $this->currentPerson->defaultOrgID);
                })
                ->join('org-person as op', 'op.personID', '=', 'person.personID')
                ->select(DB::raw('person.personID, person.lastName, person.firstName, person.login, op.OrgStat1'))
                ->with('roles', 'emails')->get();
        }

        /*
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
            ->select(DB::raw("person.personID, concat(firstName, ' ', lastName) AS fullName, op.OrgStat1, op.OrgStat2, compName,
                           title, indName, date_format(RelDate4, '%l/%d/%Y') AS 'Expire',
                           (SELECT count(*) AS 'cnt' FROM `event-registration` er WHERE er.personID=person.personID) AS 'cnt'"))
        });
        */

        dd($persons);

        return view('v1.auth_pages.organization.role_mgmt_search', compact('org', 'roles', 'permissions', 'persons', 'topBits'));
    }

    public function search(Request $request){
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
