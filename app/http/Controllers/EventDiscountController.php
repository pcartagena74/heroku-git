<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Event;
use App\EventDiscount;

class EventDiscountController extends Controller
{
    public function __construct () {
        $this->middleware('auth', ['except' => ['showDiscount']]);
    }

    public function index() {
        // responds to /blah
    }

    public function show($id) {
        // responds to GET /blah/id
    }

    public function showDiscount(Request $request, $id) {
        // AJAX response to check discountCode (vs. org-discount)
        // responds to POST /blah/id
        $eventID = $id;
        $code = request()->input('discount_code');
        $event = Event::find($eventID);
        $discounts = EventDiscount::where([
            ['discountCode', $code],
            ['orgID', $event->orgID]
        ])->count();

        $discount = EventDiscount::where([
            ['discountCode', $code],
            ['orgID', $event->orgID]
        ])->first();

        if($discounts > 0) {
            if($discount->percent == 0) {
                $discount_text = "$" . $discount->flatAmt;
            } else {
                $discount_text = $discount->percent . "%";
            }
            $message = '<span><i class="fa fa-trophy fa-2x text-success mid_align">&nbsp;</i>' . "Code: '" . $code . "'" . " provides a " . $discount_text . " discount.</span>";
            return json_encode(array('status' => 'success', 'message' => $message, 'percent' => $discount->percent, 'flatAmt' => $discount->flatAmt));
        } else {
            $message = '<span><i class="fa fa-warning fa-2x text-warning mid_align">&nbsp;</i>' . "Invalid code: '" . $code ."'</span>";
            return json_encode(array('status' => 'error','message' => $message));
        }
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
    }

    public function destroy($id) {
        // responds to DELETE /blah/id
    }
}
