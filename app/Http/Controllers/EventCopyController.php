<?php

namespace App\Http\Controllers;

use App\Event;
use App\EventDiscount;
use App\EventSession;
use App\Location;
use App\Org;
use App\OrgDiscount;
use App\Person;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventCopyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
    }

    /**
     * Response to GET /eventcopy/{$slug}
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($param)
    {
        $this->currentPerson = Person::find(auth()->user()->id);

        $today = Carbon::now();
        $event = Event::where('eventID', '=', $param)
            ->orWhere('slug', '=', $param)
            ->firstOrFail();
        $org = Org::find($event->orgID);

        $e = $event->replicate();
        $e->creatorID = $this->currentPerson->personID;
        $e->slug = 'temporary_slug_placeholder';
        $e->isActive = 0;
        $e->eventStartDate = $today->addDay();
        $e->eventEndDate = $today->addDay();
        // this is here until we decide to copy EVERYTHING associated with a PD Day event
        $e->hasTracks = 0;
        $e->save();
        $e->slug = $e->eventID;
        $e->save();

        $event = $e;

        $this->currentPerson = Person::find(auth()->user()->id);
        $current_person = $this->currentPerson = Person::find(auth()->user()->id);
        $exLoc = Location::find($event->locationID);
        $page_title = trans('messages.headers.copy_event');

        // Create a stub for the default ticket for the event
        $label = Org::find($this->currentPerson->defaultOrgID);
        $tkt = new Ticket;
        $tkt->ticketLabel = $label->defaultTicketLabel;
        $tkt->availabilityEndDate = $event->eventStartDate;
        $tkt->eventID = $event->eventID;
        $tkt->earlyBirdPercent = $label->earlyBirdPercent;
        $tkt->earlyBirdEndDate = Carbon::now();
        $tkt->save();

        // Create a mainSession for the default ticket for the event
        $mainSession = new EventSession;
        $mainSession->trackID = 0;
        $mainSession->eventID = $event->eventID;
        $mainSession->ticketID = $tkt->ticketID;
        $mainSession->sessionName = 'def_sess';
        $mainSession->confDay = 0;
        $mainSession->start = $event->eventStartDate;
        $mainSession->end = $event->eventEndDate;
        $mainSession->order = 0;
        $mainSession->creatorID = $this->currentPerson->personID;
        $mainSession->updaterID = $this->currentPerson->personID;
        $mainSession->save();

        $event->mainSession = $mainSession->sessionID;
        $event->updaterID = $this->currentPerson->personID;
        $event->save();

        // A copied event should always get the discount codes.
        $orgDiscounts = OrgDiscount::where([
            ['orgID', $this->currentPerson->defaultOrgID],
            ['discountCODE', '<>', ''],
        ])->get();

        foreach ($orgDiscounts as $od) {
            $ed = new EventDiscount;
            $ed->orgID = $od->orgID;
            $ed->eventID = $event->eventID;
            $ed->discountCODE = $od->discountCODE;
            $ed->percent = $od->percent;
            $ed->creatorID = $this->currentPerson->personID;
            $ed->updaterID = $this->currentPerson->personID;
            $ed->save();
        }

        //return view('v1.auth_pages.events.add-edit_form', compact('current_person', 'page_title', 'event', 'exLoc'));
        //return redirect('/event/' . $event->eventID . '/edit');
        return view('v1.auth_pages.events.add-edit_form', compact('current_person', 'page_title', 'event', 'exLoc', 'org'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
