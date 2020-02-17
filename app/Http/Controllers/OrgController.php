<?php

namespace App\Http\Controllers;

use App\EventType;
use App\Org;
use App\Person;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrgController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
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
        abort(500);
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
