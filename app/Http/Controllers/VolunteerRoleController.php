<?php

namespace App\Http\Controllers;

use App\Models\Org;
use App\Models\Person;
use App\Models\VolunteerRole;
use App\Models\VolunteerService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VolunteerRoleController extends Controller
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
        $org = Org::where('orgID', $this->currentPerson->defaultOrgID)->first();

        // Check that there are volunteer roles for the current organization
        $defaultRoles = VolunteerRole::where('orgID', $org->orgID)->where('title', '!=', 'role')->get();

        // This is a correction measure to create the data
        if (count($defaultRoles) == 0 || $defaultRoles === null) {
            $pid = null;
            $defaultRoles = VolunteerRole::where('orgID', 1)->where('title', '!=', 'role')->get();

            foreach ($defaultRoles as $r) {
                $new_role = $r->replicate();
                $new_role->orgID = $org->orgID;
                if ($pid !== null) {
                    $new_role->pid = $pid;
                }
                $new_role->save();
                if ($r->pid === null) {
                    $pid = $new_role->id;
                }
            }
        }

        [$json_roles, $option_string] = volunteer_data($org, null);

        return view('v1.auth_pages.volunteers.show_roles', compact('option_string', 'json_roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // pid (parent id) is the only field we will know.  create and return the new node's id.
        $pid = request()->input('pid');

        $vr = new VolunteerRole;
        $vr->title = 'role';
        $vr->orgID = $this->currentPerson->defaultOrgID;
        $vr->creatorID = $this->currentPerson->personID;
        $vr->pid = $pid;
        $vr->save();

        return json_encode(['status' => 'success', 'statusCode' => 200, 'id' => $vr->id]);
        // return redirect()->route('nodes.index')->with('success', 'Node created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Org $org)
    {
        $p = $this->currentPerson;

        // Check that there are volunteer roles for the current organization
        $defaultRoles = VolunteerRole::where('orgID', $org->orgID)->where('title', '!=', 'role')->get();

        // This is a correction measure to create the data
        if (count($defaultRoles) == 0 || $defaultRoles === null) {
            $pid = null;
            $defaultRoles = VolunteerRole::where('orgID', 1)->where('title', '!=', 'role')->get();

            foreach ($defaultRoles as $r) {
                $new_role = $r->replicate();
                $new_role->orgID = $org->orgID;
                if ($pid !== null) {
                    $new_role->pid = $pid;
                }
                $new_role->save();
                if ($r->pid === null) {
                    $pid = $new_role->id;
                }
            }
        }

        [$json_roles, $option_string] = volunteer_data($org, $p);

        return view('v1.auth_pages.volunteers.show_roles', compact('option_string', 'json_roles'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(VolunteerRole $volunteerRole)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VolunteerRole $volunteerRole)
    {
        // Outline all of the possible updates - even when org/position/assignment is fresh

        $return_msg = [];
        $success = '';
        $now = Carbon::now();
        $newnode = request()->input('newnode');
        $oldnode = request()->input('oldnode');
        $orgID = $volunteerRole->orgID;

        // fields that can be updated as booleans to track updates
        $name = 0;
        $title = 0;
        $start = 0;
        $end = 0;
        $job_desc = 0;
        // this is a tracker when personID gets changed as a result of other changes.
        $pID_update = 0;

        // Logic
        // -----
        // First check if oldnode has a personID b/c VR and VS records are in play.
        // if oldnode(personID is null) and newnode(personID) VR and new VS to create
        // if oldnode(personID) & newnode(personID) are the same; VR/VS updates
        // if oldnode and newnode personIDs exist but different; VR updates; old VS and new VS

        // Called when PID is changed;  A role is dragged under a role (new or otherwise)
        if (array_key_exists('pid', $newnode) && $newnode['pid'] != $oldnode['pid']) {
            $volunteerRole->pid = $newnode['pid'];
            $success .= 'pid updated; ';
        }

        // Called when the title is changed.  The title_override field is used when taken as user input
        if ($newnode[trans('messages.fields.title')] != $oldnode[trans('messages.fields.title')]) {
            $volunteerRole->title_override = $newnode['Title'];
            $success .= 'title updated; ';
        }

        // Called when the JD URL is entered.
        if ($newnode[trans('messages.default_roles.jd_url')] != $oldnode[trans('messages.default_roles.jd_url')]) {
            $volunteerRole->jd_URL = $newnode[trans('messages.default_roles.jd_url')];
            $success .= 'url updated; ';
        }

        // Called when the name is changed from a previous name.
        if (($newnode[trans('messages.fields.name')] != $oldnode[trans('messages.fields.name')]) &&
            ($oldnode[trans('messages.fields.name')] !== null)) {
            $now = Carbon::now();
            // If the name of the officer is changed and the oldnode wasn't null,
            // 1. Find the correct VolunteerService record and give it an end date
            // 2. Make a new VolunteerService record
            // ***************
            // 3. Need to figure out how to find and populate personID based on the name
            // ***************

            // With a name change, we want to try to determine if it's an edit or role change
            if ($oldnode['personID'] !== null) {
                // Without a personID in oldnode, this would be an edit/update
                // 1. Look for a stub record or create it.
                $vs = VolunteerService::where([
                    ['orgID', $orgID],
                    ['volunteer_role_id', $newnode['id']],
                    // ,['personID', $oldnode['personID']]
                ])
                    ->whereNull('roleEndDate')
                    ->firstOrNew;
            } else {
                // With a personID in oldnode, this is a name change and might be a role change
                // 1. Look for existing record with personID
                $vs = VolunteerService::where([
                    ['orgID', $orgID],
                    ['volunteer_role_id', $newnode['id']],
                    ['personID', $oldnode['personID']],
                ])
                    ->whereNull('roleEndDate')
                    ->first();

                // 2. Check if the time in role is > 30 days.  Makes it less likely to be a correction.
                if ($now->diffInDays($vs->roleStartDate) < 30) {
                    // This is just a name change
                    $vs->updaterID = $this->currentPerson->personID;
                    $vs->save();
                } else {
                    // This is a role change
                    $vs->roleEndDate = $now;

                    // Since we're closing out the VolunteerService record, save the title
                    // This is just in case titles are restructured in the future, the correct title can be reported.
                    if ($volunteerRole->title_override !== null) {
                        $vs->title_save = $volunteerRole->title_override;
                    } else {
                        $vs->title_save = trans('messages.default_role'.$volunteerRole->title);
                    }
                    $vs->updaterID = $this->currentPerson->personID;
                    $vs->save();

                    $vs2 = new VolunteerService;
                    $vs2->orgID = $orgID;
                    $vs2->roleStartDate = $now;
                    $vs2->volunteer_role_id = $vs->volunteer_role_id;
                    $vs2->personID = $newnode[trans('messages.fields.name')];
                    $vs2->creatorID = $this->currentPerson->personID;
                    $vs2->save();
                }
            }

            $return_msg = ['vs_new_vs_msg' => "Old personID: $vs->personID"];
            $success .= 'name updated; ';
        }

        // Change of start date
        if ($oldnode[trans('messages.default_roles.start')] != $newnode[trans('messages.default_roles.start')]) {
            $vs = VolunteerService::where([
                ['orgID', $orgID],
                ['volunteer_role_id', $newnode['id']],
                ['personID', $oldnode['personID']],
            ])
                ->whereNull('roleEndDate')
                ->firstOrNew();

            $vs->roleStartDate = $newnode[trans('messages.default_roles.start')];
            $vs->save();
            array_push($return_msg, ['vs_end_msg' => "New VS end date: $vs->end"]);
            $success .= 'enddate updated; ';
        }
        // Change of end date when original was null.
        if (($oldnode[trans('messages.default_roles.end')] === null) &&
            ($newnode[trans('messages.default_roles.end')] != $oldnode[trans('messages.default_roles.end')])) {

            $vs = VolunteerService::where([
                ['orgID', $orgID],
                ['volunteer_role_id', $newnode['id']],
                ['personID', $oldnode['personID']],
            ])
                ->whereNull('roleEndDate')
                ->firstOrNew();
            $vs->roleEndDate = $newnode[trans('messages.default_roles.end')];
            $vs->save();
            array_push($return_msg, ['vs_end_msg' => "New VS end date: $vs->end"]);
            $success .= 'enddate updated;';
        }

        $volunteerRole->updaterID = $this->currentPerson->personID;
        $volunteerRole->updated_at = $now;
        $volunteerRole->save();

        array_push($return_msg, ['status' => $success, 'statusCode' => 200]);

        return json_encode($return_msg);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(VolunteerRole $volunteerRole)
    {
        //
    }
}
