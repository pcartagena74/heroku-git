<?php

namespace App\Http\Controllers;

use App\AdminPropGroup;
use App\Email;
use App\Notifications\NewUserAcct;
use App\Org;
use App\OrgPerson;
use App\Permission;
use App\PermissionRole;
use App\Person;
use App\Role;
use App\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash as Hash;
use Validator;
use App\OrgAdminProp;
use App\AdminProp;
use Response;

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
     *
     * Response to GET /panel
     */
    public function index()
    {
        $currentPerson = $this->currentPerson;
        $currentOrg = $this->currentPerson->defaultOrg;
        $admin_props = $currentOrg->admin_props;
        $admin_props_json = json_decode($admin_props, true);

        $prop_list = [];
        $group_list = [];
        // $group = AdminPropGroup::find(1)->with('props')->first();
        // dd($admin_props, $admin_props[2]->prop, $group);
        // dd($admin_props, $admin_props[0]->prop->group);

        foreach ($admin_props as $ap) {
            array_push($prop_list, $ap->propID);
            array_push($group_list, $ap->prop->group->id);
        }

        $group_list = array_unique($group_list, SORT_REGULAR);
        $prop_list = array_unique($prop_list, SORT_REGULAR);
        $groups = AdminPropGroup::whereIn('id', $group_list)->get();

        return Response::view('v1.auth_pages.admin.panel',
                compact('currentPerson', 'currentOrg', 'prop_list', 'groups', 'admin_props', 'admin_props_json'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        dd($request);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     *
     * POST /panel/update (Axios submitted)
     */
    public function update(Request $request)
    {

        $name = request()->input('name');
        $value = request()->input('value');
        $orgID = $this->currentPerson->defaultOrgID;

        $prop = AdminProp::where('name', $name)->firstOrFail();
        $orgProp = OrgAdminProp::updateOrCreate(
            ['propID' => $prop->id, 'orgID' => $orgID, ],
            ['value' => $value]
        );

        try {
            switch ($name) {
                case 'xvar_array':
                    1;
                    break;
                default:
                    $orgProp->value = $value;
            }

            $orgProp->updateDate = now();
            $orgProp->save();

            return ['message' => "Field: $name was updated."];
        } catch (\Exception $e) {
            return ['failure' => "Field: $name was not updated.", 'data' => $orgProp];
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
