<?php

namespace App\Http\Controllers;

use App\Models\Org;
use App\Models\Person;
use App\Models\VolunteerRole;
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
        $roles = VolunteerRole::where('orgID', $o->orgID)->get();
        $json_roles = VolunteerRole::where('volunteer_roles.orgID', $o->orgID)
            ->join('volunteer_service as vs', function ($q) {
                $q->on([
                    ['vs.orgID', '=', 'volunteer_roles.orgID'],
                    ['vs.volunteer_role_id', '=', 'volunteer_roles.id']
                ])
                    ->whereNull('vs.roleEndDate');
            })
            ->join('person as p', function ($q) {
                $q->on('p.personID', '=', 'vs.personID');
            })
            ->select(DB::raw('volunteer_roles.id,
                                    (CASE
                                      WHEN volunteer_roles.title_override IS NOT NULL
                                        THEN volunteer_roles.title_override
                                      ELSE concat("messages.default_roles.", volunteer_roles.title)
                                    END) as Title,
                                    pid,
                                    volunteer_roles.jd_URL as "' . trans('messages.default_roles.jd_url') . '",' .
                                    'concat(p.firstName, " ", p.lastName) as Name'))
            ->distinct()
            ->get();

        foreach ($json_roles as $jr){
            //$jr->Title = trans('messages.default_roles.'.$jr->Title);
            if(preg_match('#^messages#', $jr->Title)) {
                $jr->Title = trans($jr->Title);
            }
            if($jr->pid === null) {
                unset($jr->pid);
            }
        }
        $json_roles = json_encode($json_roles);

        return view('v1.auth_pages.volunteers.show_roles_vue', compact('roles', 'json_roles'));
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        VolunteerRole::create($request->all());

        return redirect()->route('nodes.index')
            ->with('success','Node created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\VolunteerRole  $volunteerRole
     * @return \Illuminate\Http\Response
     */
    public function show(VolunteerRole $volunteerRole)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VolunteerRole  $volunteerRole
     * @return \Illuminate\Http\Response
     */
    public function edit(VolunteerRole $volunteerRole)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VolunteerRole  $volunteerRole
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VolunteerRole $volunteerRole)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VolunteerRole  $volunteerRole
     * @return \Illuminate\Http\Response
     */
    public function destroy(VolunteerRole $volunteerRole)
    {
        //
    }
}
