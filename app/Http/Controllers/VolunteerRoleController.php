<?php

namespace App\Http\Controllers;

use App\Models\Org;
use App\Models\Person;
use App\Models\VolunteerRole;
use App\Models\VolunteerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $o = Org::where('orgID', $this->currentPerson->defaultOrgID)->first();

        [$json_roles, $option_string] = volunteer_data($o, null);

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
     * @param \Illuminate\Http\Request $request
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
     * @param \App\Models\Org $org
     * @return \Illuminate\Http\Response
     */
    public function show(Org $org)
    {
        $p = $this->currentPerson;

        [$json_roles, $option_string] = volunteer_data($org, $p);

        return view('v1.auth_pages.volunteers.show_roles', compact('option_string', 'json_roles'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\VolunteerRole $volunteerRole
     * @return \Illuminate\Http\Response
     */
    public function edit(VolunteerRole $volunteerRole)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\VolunteerRole $volunteerRole
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VolunteerRole $volunteerRole)
    {
        $return_msg = [];
        $now = Carbon::now();
        $newnode = request()->input('newnode');
        $oldnode = request()->input('oldnode');
        $orgID = $volunteerRole->orgID;

        if (array_key_exists('pid', $newnode) && $newnode['pid'] != $oldnode['pid']) {
            $volunteerRole->pid = $newnode['pid'];
        }
        if ($newnode[trans('messages.fields.title')] != $oldnode[trans('messages.fields.title')]) {
            $volunteerRole->title_override = $newnode["Title"];
        }
        if ($newnode[trans('messages.default_roles.jd_url')] != $oldnode[trans('messages.default_roles.jd_url')]) {
            $volunteerRole->jd_URL = $newnode[trans('messages.default_roles.jd_url')];
        }
        if (($newnode[trans('messages.fields.name')] != $oldnode[trans('messages.fields.name')]) &&
            (null !== $oldnode[trans('messages.fields.name')])) {
            $now = Carbon::now();
            // If the name of the officer is changed and the oldnode wasn't null,
            // 1. Find the correct VolunteerService record and give it an end date
            // 2. Make a new VolunteerService record
            $vs = VolunteerService::where([
                ['orgID', $orgID],
                ['volunteer_role_id', $newnode['id']],
                ['personID', $oldnode['personID']]
            ])
                ->whereNull('roleEndDate')
                ->first();
            $vs->roleEndDate = $now;
            if (null !== $volunteerRole->title_override) {
                $vs->title_save = $volunteerRole->title_override;
            } else {
                $vs->title_save = trans('messages.default_role' . $volunteerRole->title);
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

            $return_msg = ['vs_new_vs_msg' => "Old personID: $vs->personID"];
        }
        if ((null === $oldnode[trans('messages.default_roles.end')]) &&
            ($newnode[trans('messages.default_roles.end')] != $oldnode[trans('messages.default_roles.end')])) {

            $vs = VolunteerService::where([
                ['orgID', $orgID],
                ['volunteer_role_id', $newnode['id']],
                ['personID', $oldnode['personID']]
            ])
                ->whereNull('roleEndDate')
                ->first();
            $vs->roleEndDate = $newnode[trans('messages.default_roles.end')];
            $vs->save();
            array_push($return_msg, ['vs_end_msg' => "New VS end date: $vs->end"]);
        }

        $volunteerRole->updaterID = $this->currentPerson->personID;
        $volunteerRole->updated_at = $now;
        $volunteerRole->save();

        array_push($return_msg, ['status' => 'success', 'statusCode' => 200]);

        return json_encode($return_msg);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\VolunteerRole $volunteerRole
     * @return \Illuminate\Http\Response
     */
    public function destroy(VolunteerRole $volunteerRole)
    {
        //
    }
}
