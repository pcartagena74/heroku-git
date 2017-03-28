<?php
namespace App\Http\Controllers;
ini_set('max_execution_time', 0);

/*

    public function __construct() {
        $this->middleware('auth');
    }

 */

use App\OrgDiscount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Event;
use App\Person;
use App\Location;
use App\Org;
use App\Ticket;
use App\EventDiscount;

class EventController extends Controller
{
    public function __construct () {
        $this->middleware('auth', ['except' => ['show']]);
    }

    public function index () {
        // responds to /events

        $topBits = '';

        $current_sql = "SELECT e.eventID, e.eventName, date_format(e.eventStartDate, '%c/%d/%Y %l:%i %p') AS eventStartDateF,
                        date_format(e.eventEndDate, '%c/%d/%Y %l:%i %p') AS eventEndDateF, e.isActive, e.eventStartDate, e.eventEndDate,
                        count(er.ticketID) AS 'cnt', et.etName
                    FROM `org-event` e
                    LEFT JOIN `event-registration` er ON er.eventID=e.eventID
                    LEFT JOIN `org-event_types` et ON et.etID = e.eventTypeID AND et.orgID = e.orgID
                    WHERE e.orgID = (SELECT orgID FROM `org-person` op WHERE op.personID=?)
                        AND eventStartDate >= NOW() AND e.deleted_at is null
                    GROUP BY e.eventID, e.eventName, e.eventStartDate, e.eventEndDate, e.isActive, e.eventStartDate
                    ORDER BY e.eventStartDate ASC";

        $past_sql = "SELECT e.eventID, e.eventName, date_format(e.eventStartDate, '%c/%d/%Y %l:%i %p') AS eventStartDateF,
                    date_format(e.eventEndDate, '%c/%d/%Y %l:%i %p') AS eventEndDateF, e.isActive, e.eventStartDate,
                    count(er.ticketID) AS 'cnt', et.etName
                 FROM `org-event` e
                 LEFT JOIN `event-registration` er ON er.eventID=e.eventID
                 LEFT JOIN `org-event_types` et ON et.etID = e.eventTypeID AND et.orgID = e.orgID
                 WHERE e.orgID = (SELECT orgID FROM `org-person` op WHERE op.personID=?)
                    AND eventStartDate < NOW() AND e.deleted_at is null
                 GROUP BY e.eventID, e.eventName, e.eventStartDate, e.eventEndDate, e.isActive, e.eventStartDate
                 ORDER BY e.eventStartDate ASC";

        $current_person = $this->currentPerson = Person::find(auth()->user()->id);
        $current_events = DB::select($current_sql, [$this->currentPerson->personID]);

        $past_events = DB::select($past_sql, [$this->currentPerson->personID]);

        return view('v1.auth_pages.events.list', compact('current_events', 'past_events', 'topBits', 'current_person'));
    }

    public function show ($id) {
        // responds to GET /events/id
        if(auth()->guest()) {
            $current_person = 0;
        } else {
            $this->currentPerson = Person::find(auth()->user()->id);
            $current_person      = $this->currentPerson;
        }

        $event     = Event::find($id);
        $event_loc = Location::where('locID', $event->locationID)->first();
        $org_stuff = Org::where('orgID', $event->orgID)->select('orgPath', 'orgLogo')->first();
        $bundles   =
            Ticket::where([
                ['isaBundle', 1],
                ['isDeleted', 0],
                ['eventID', $event->eventID],
            ])->get()->sortByDesc('availableEndDate');

        $tickets =
            Ticket::where([
                ['isaBundle', 0],
                ['isDeleted', 0],
                ['eventID', $event->eventID]
            ])->get()->sortByDesc('availableEndDate');

        return view('v1.public_pages.display_event', compact('event', 'current_person', 'bundles', 'tickets', 'event_loc', 'org_stuff'));
    }

    public function create () {
        // responds to /events/create and shows add/edit form
        $this->currentPerson = Person::find(auth()->user()->id);
        $current_person      = $this->currentPerson;
        $page_title          = 'Create New Event';

        return view('v1.auth_pages.events.add-edit_form', compact('current_person', 'page_title'));
    }

    public function store (Request $request) {
        // responds to POST to /events and creates, adds, stores the event
        $today = Carbon::now();
        $this->currentPerson = Person::find(auth()->user()->id);
        $event               = new Event;

        if(request()->input('locationID') != '') {
            $location = Location::find(request()->input('locationID'));
            $locName  = request()->input('locName');
            $addr1    = request()->input('addr1');
            if($location->locName == $locName && $location->addr1 == $addr1) {
                $event->locationID = $location->locID;
            } else {
                $loc            = new Location;
                $loc->locName   = request()->input('locName');
                $loc->addr1     = request()->input('addr1');
                $loc->addr2     = request()->input('addr2');
                $loc->city      = request()->input('city');
                $loc->state     = request()->input('state');
                $loc->zip       = request()->input('zip');
                $loc->creatorID = $this->currentPerson->personID;
                $loc->updaterID = $this->currentPerson->personID;
                $loc->save();
                $event->locationID = $loc->locID;
            }
        } else {
            $loc            = new Location;
            $loc->locName   = request()->input('locName');
            $loc->addr1     = request()->input('addr1');
            $loc->addr2     = request()->input('addr2');
            $loc->city      = request()->input('city');
            $loc->state     = request()->input('state');
            $loc->zip       = request()->input('zip');
            $loc->creatorID = $this->currentPerson->personID;
            $loc->updaterID = $this->currentPerson->personID;
            $loc->save();
            $event->locationID = $loc->locID;
        }
        $event->orgID            = $this->currentPerson->defaultOrgID;
        $event->eventName        = request()->input('eventName');
        $event->eventDescription = request()->input('eventDescription');
        $event->catID            = request()->input('catID');
        $event->eventTypeID      = request()->input('eventTypeID');
        $event->eventInfo        = request()->input('eventInfo');
        $event->eventStartDate   = request()->input('eventStartDate');
        $event->eventEndDate     = request()->input('eventEndDate');
        $event->eventTimeZone    = request()->input('eventTimeZone');
        $event->contactOrg       = request()->input('contactOrg');
        $event->contactEmail     = request()->input('contactEmail');
        $event->contactDetails   = request()->input('contactDetails');
        $event->showLogo         = request()->input('showLogo');
        $event->hasFood          = request()->input('hasFood');
        /*
         *  Add these later:
         *  image1
         *  image2
         *  shortURL
         *  refund Note
         *  event tags
         */
        $label                = Org::find($this->currentPerson->defaultOrgID)
                                   ->select('defaultTicketLabel', 'earlyBirdPercent')->first();
        $event->earlyDiscount = $label->earlyBirdPercent;
        $event->earlyBirdDate = Carbon::now();
        $event->creatorID     = $this->currentPerson->personID;
        $event->updaterID     = $this->currentPerson->personID;
        $event->save();

        // Create a stub for the default ticket for the event
        $tkt                      = new Ticket;
        $tkt->ticketLabel         = $label->defaultTicketLabel;
        $tkt->availabilityEndDate = $event->eventStartDate;
        $tkt->eventID             = $event->eventID;
        $tkt->earlyBirdPercent    = $label->earlyBirdPercent;
        $tkt->earlyBirdEndDate    = Carbon::now();
        $tkt->save();

        if($event->eventStartDate > $today) {
            $orgDiscounts = OrgDiscount::where([
                ['orgID', $this->currentPerson->defaultOrgID],
                ['discountCODE', "<>", '']
            ])->get();

            foreach($orgDiscounts as $od) {
                $ed               = new EventDiscount;
                $ed->orgID        = $od->orgID;
                $ed->eventID      = $event->eventID;
                $ed->discountCODE = $od->discountCODE;
                $ed->percent      = $od->percent;
                $ed->creatorID    = $this->currentPerson->personID;
                $ed->updaterID    = $this->currentPerson->personID;
                $ed->save();
            }
        }

        return redirect('/event-tickets/' . $event->eventID);
    }

    public function edit ($id) {
        // responds to GET /events/id/edit and shows the add/edit form
        $this->currentPerson = Person::find(auth()->user()->id);
        $current_person      = $this->currentPerson = Person::find(auth()->user()->id);
        $event               = Event::find($id);
        $exLoc               = Location::find($event->locationID);
        $page_title          = 'Edit Event';
        return view('v1.auth_pages.events.add-edit_form', compact('current_person', 'page_title', 'event', 'exLoc'));
    }

    public function update (Request $request, $id) {
        // responds to PATCH /events/id
        $this->currentPerson = Person::find(auth()->user()->id);
        $event               = Event::find($id);
        $input_loc           = request()->input('locationID');
        // check to see if form loc == saved loc
        // if so, grab location item and save data.
        if($input_loc == $event->locationID) {
            $loc            = Location::find($event->locationID);
            $loc->locName   = request()->input('locName');
            $loc->addr1     = request()->input('addr1');
            $loc->addr2     = request()->input('addr2');
            $loc->city      = request()->input('city');
            $loc->state     = request()->input('state');
            $loc->zip       = request()->input('zip');
            $loc->updaterID = $this->currentPerson->personID;
            $loc->save();
            // if not and also not empty, grap location and save data
        } elseif($input_loc != $event->locationID && !empty($input_loc)) {
            $loc            = Location::find(request()->input('locationID'));
            $loc->locName   = request()->input('locName');
            $loc->addr1     = request()->input('addr1');
            $loc->addr2     = request()->input('addr2');
            $loc->city      = request()->input('city');
            $loc->state     = request()->input('state');
            $loc->zip       = request()->input('zip');
            $loc->updaterID = $this->currentPerson->personID;
            $loc->save();
            $event->locationID = $loc->locID;

            // otherwise, the ID is empty but there might be data or not
        } elseif(empty($input_loc)) {
            if(str_len(request()->input('locName') . request()->input('addr1')) > 3) {
                $loc            = new Location;
                $loc->locName   = request()->input('locName');
                $loc->addr1     = request()->input('addr1');
                $loc->addr2     = request()->input('addr2');
                $loc->city      = request()->input('city');
                $loc->state     = request()->input('state');
                $loc->zip       = request()->input('zip');
                $loc->creatorID = $this->currentPerson->personID;
                $loc->updaterID = $this->currentPerson->personID;
                $loc->save();
                $event->locationID = $loc->locID;
            }
        }
        $event->eventName        = request()->input('eventName');
        $event->eventDescription = request()->input('eventDescription');
        $event->catID            = request()->input('catID');
        $event->eventTypeID      = request()->input('eventTypeID');
        $event->eventInfo        = request()->input('eventInfo');
        $event->eventStartDate   = request()->input('eventStartDate');
        $event->eventEndDate     = request()->input('eventEndDate');
        $event->eventTimeZone    = request()->input('eventTimeZone');
        $event->contactOrg       = request()->input('contactOrg');
        $event->contactEmail     = request()->input('contactEmail');
        $event->contactDetails   = request()->input('contactDetails');
        $event->showLogo         = request()->input('showLogo');
        if(request()->input('hasFood')) {
            $event->hasFood = 1;
        } else {
            $event->hasFood = 0;
        }
        /*
         *  Add these later:
         *  image1
         *  image2
         *  shortURL
         *  refund Note
         *  event tags
         */
        $event->updaterID = $this->currentPerson->personID;
        $event->save();

        return redirect('/event-tickets/' . $event->eventID);
    }

    public function destroy ($id) {
        // responds to DELETE /events/id
        $event         = Event::find($id);
        $registrations = DB::table('event-registration')->where('eventID', $event->eventID)->count();

        if($registrations == 0) {
            $event->delete();
        } else {
            // Do something?
        }
        return redirect('/events');
    }

    public function activate ($id) {
        $event = Event::find($id);
        if($event->isActive == 1) {
            $event->isActive = 0;
        } else {
            $event->isActive = 1;
        }
        $event->updaterID = auth()->user()->id;
        $event->save();

        return json_encode(array('status' => 'success', 'message' => 'Activation successfully toggled.'));
    }

    public function ajax_update (Request $request, $id) {
        $event               = Event::find($id);
        $this->currentPerson = Person::find(auth()->user()->id);

        // shaving off number at the end to match fieldname
        $name  = request()->input('name');
        $value = request()->input('value');

        if($name == 'availabilityEndDate' or $name == 'earlyBirdDate' and $value !== null) {
            $date  = date("Y-m-d H:i:s", strtotime(trim($value)));
            $value = $date;
        }

        $event->{$name}   = $value;
        $event->updaterID = $this->currentPerson->personID;
        $event->save();
    }
}
