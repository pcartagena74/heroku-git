<?php

namespace App\Http\Controllers;

use App\Event;
use App\OrgDiscount;
use App\Person;
use Illuminate\Http\Request;

class OrgDiscountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['showDiscount']]);
    }

    public function index()
    {
        // responds to /blah
    }

    public function show($id)
    {
        // responds to GET /blah/id
    }

    public function showDiscount(Request $request, $id)
    {
        // AJAX response to check discountCode (vs. org-discount)
        // responds to POST /blah/id
        $eventID = $id;
        $code = request()->input('discount_code');
        $event = Event::find($eventID);
        $discounts = OrgDiscount::where([
            ['discountCode', $code],
            ['orgID', $event->orgID],
        ])->count();

        $discount = OrgDiscount::where([
            ['discountCode', $code],
            ['orgID', $event->orgID],
        ])->first();

        if ($discounts > 0) {
            $message = '<span><i class="far fa-trophy fa-2x text-success mid_align">&nbsp;</i>'."Code: '".$code."'".' provides a '.$discount->percent.'% discount.</span>';

            return json_encode(['status' => 'success', 'message' => $message, 'percent' => $discount->percent]);
        } else {
            $message = '<span><i class="far fa-warning fa-2x text-warning mid_align">&nbsp;</i>'."Invalid code: '".$code."'</span>";

            return json_encode(['status' => 'error', 'message' => $message]);
        }
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
        $name = request()->input('name');
        $value = request()->input('value');
        if ($value === null) {
            $value = 0;
        }
        $this->currentPerson = Person::find(auth()->user()->id);
        $discount = OrgDiscount::find($id);

        if ($name == 'discountCODE'.$id) {
            $discount->discountCODE = $value;
        } elseif ($name == 'percent'.$id) {
            $discount->percent = $value;
        } else {
            // something unexpected occurred
            dd(request()->all());
        }
        $discount->updaterID = $this->currentPerson->personID;
        $discount->save();
    }

    public function destroy($id)
    {
        // responds to DELETE /blah/id
    }
}
