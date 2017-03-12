<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Person;
use App\Org;
use Illuminate\Support\Facades\DB;
use App\OrgDiscount;

class OrgController extends Controller
{
    public function index() {
        // responds to /blah
        $this->currentPerson = Person::find(auth()->user()->id);
        $orgID = $this->currentPerson->defaultOrgID;

        // This function will eventually need to determine if there are multiple organizations attached to the
        // $this->currentPerson and then render a page allowing selection of a new organization
        // (which would then need to be updated in defaultOrgID) and redirection to the orgsetting/{id} IF
        // user has sufficient permissions

        return redirect('/orgsettings/'. $orgID);
    }

    public function event_defaults() {
        $this->currentPerson = Person::find(auth()->user()->id);
        $current_person = $this->currentPerson;
        $org = Org::find($this->currentPerson->defaultOrgID);

        $discount_codes = DB::table('org-discounts')
            ->where('orgID', $org->orgID)
            ->select('discountID', 'discountCODE', 'percent')
            ->get();

        return view('v1.auth_pages.organization.event_defaults', compact('org', 'current_person', 'discount_codes'));
    }

    public function show($id) {
        // responds to GET /blah/id
        $org = Org::find($id);

        $topBits = [];
        $cData = DB::table('organization')->select(DB::raw('10-( isnull(OSN1) + isnull(OSN2) + isnull(OSN3) + isnull(OSN4) + isnull(OSN5) + isnull(OSN6) + isnull(OSN7) + isnull(OSN8) + isnull(OSN9) + isnull(OSN10)) as cnt'))->where('orgID', $org->orgID)->first();
        $cDate = DB::table('organization')->select(DB::raw('10-( isnull(ODN1) + isnull(ODN2) + isnull(ODN3) + isnull(ODN4) + isnull(ODN5) + isnull(ODN6) + isnull(ODN7) + isnull(ODN8) + isnull(ODN9) + isnull(ODN10)) as cnt'))->where('orgID', $org->orgID)->first();
        array_push($topBits, [8, 'Custom Fields', $cData->cnt . "/10", '', '']);
        array_push($topBits, [3, 'Custom Dates', $cDate->cnt . "/10", '', '']);

        return view('v1.auth_pages.organization.settings', compact('org', 'topBits'));
    }

    public function create() {
        // responds to /blah/create and shows add/edit form
    }

    public function store(Request $request) {
        // responds to POST to /blah and creates, adds, stores the event
        dd(request()->all());
    }

    public function edit($id) {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update(Request $request, $id) {
        // responds to PATCH /blah/id
        $name = request()->input('name');
        $value = request()->input('value');

        $org = Org::find($id);
        $org->{$name} = $value;
        $org->updaterID = auth()->user()->id;
        $org->save();
    }

    public function destroy($id) {
        // responds to DELETE /blah/id
    }
}
