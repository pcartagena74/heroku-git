<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Event;
use App\EventDiscount;
use App\Person;
use App\Org;
use Illuminate\Support\Facades\DB;
use App\RegFinance;

class EventDiscountController extends Controller
{
    public function __construct () {
        $this->middleware('auth')->except('showDiscount');
    }

    public function index () {
        // responds to /blah
    }

    public function show (Event $event) {
        // responds to GET /blah/id
        $this->currentPerson = Person::find(auth()->user()->id);
        $current_person      = $this->currentPerson;
        $org                 = Org::find($this->currentPerson->defaultOrgID);

        $discount_codes = DB::table('event-discounts')
                            ->where([
                                ['orgID', $org->orgID],
                                ['eventID', $event->eventID],
                            ])
                            ->select('discountID', 'discountCODE', 'percent', 'flatAmt')
                            ->orderBy('discountID', 'DESC')
                            ->get();

        return view('v1.auth_pages.events.event_discounts', compact('org', 'event', 'current_person', 'discount_codes'));
    }

    public function showDiscount (Request $request, $id) {
        // AJAX response to check discountCode (vs. org-discount)
        // responds to POST /blah/id
        $eventID   = $id;
        $code      = request()->input('discount_code');
        $event     = Event::find($eventID);
        $discounts = EventDiscount::where([
            ['discountCode', $code],
            ['orgID', $event->orgID],
            ['eventID', $event->eventID]
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
            $message =
                '<span><i class="fa fa-trophy fa-2x text-success mid_align">&nbsp;</i>' . "Code: '" . $code . "'" . " provides a " . $discount_text . " discount.</span>";
            return json_encode(array('status' => 'success', 'message' => $message, 'percent' => $discount->percent, 'flatAmt' => $discount->flatAmt));
        } else {
            $message =
                '<span><i class="fa fa-warning fa-2x text-warning mid_align">&nbsp;</i>' . "Invalid code: '" . $code . "'</span>";
            return json_encode(array('status' => 'error', 'message' => $message, 'percent' => 0, 'flatAmt' => 0));
        }
    }

    public function create () {
        // responds to /blah/create and shows add/edit form
    }

    public function store (Request $request) {
        // responds to POST to /blah and creates, adds, stores the eventDiscount

        $this->currentPerson = Person::find($request->input('personID'));
        $event               = Event::find($request->input('eventID'));

        for($i = 1; $i <= 5; $i++) {
            $discountCode = "discountCode" . $i;
            $percent      = "percent" . $i;
            $flatAmt      = "flatAmt" . $i;

            $dc           = $request->input($discountCode);
            $pc           = $request->input($percent);
            $fa           = $request->input($flatAmt);

            $pc !== null ?: $pc = '0';
            $fa !== null ?: $fa = '0.00';

            if($dc !== null) {
                $ed = new EventDiscount;
                $ed->orgID = $event->orgID;
                $ed->eventID = $event->eventID;
                $ed->discountCODE = $dc;
                $ed->percent = $pc;
                $ed->flatAmt = $fa;
                $ed->creatorID = $this->currentPerson->personID;
                $ed->updaterID = $this->currentPerson->personID;
                $ed->save();
            }
        }
        return redirect("/eventdiscount/$event->eventID");
    }

    public function edit ($id) {
        // responds to GET /blah/id/edit and shows the add/edit form
    }

    public function update (Request $request, $id) {
        // responds to PATCH /blah/id
    }

    public function destroy ($id) {
        // responds to DELETE /blah/id
        $this->currentPerson = Person::find(auth()->user()->id);
        $discount              = EventDiscount::Find($id);
        $eventID             = $discount->eventID;

        //check to see if any regIDs have this ticketID
        if(RegFinance::where('discountCode', $discount->discountCODE)->count() > 0) {
            // soft-delete if there are registrations that used this discountCode
            $discount->updaterID = $this->currentPerson->personID;
            $discount->save();
            $discount->delete();
        } else {
            // else just remove from DB
            DB::table('event-discounts')->where('discountID', $id)->delete();
        }

        return redirect("/eventdiscount/" . $eventID);
    }
}
