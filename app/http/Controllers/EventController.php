<?php
namespace App\Http\Controllers;
ini_set('max_execution_time', 0);

use App\OrgPerson;
use App\RegFinance;
use App\Registration;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GrahamCampbell\Flysystem\Facades\Flysystem;
use League\Flysystem\AdapterInterface;
use App\Event;
use App\EventDiscount;
use App\Location;
use App\Org;
use App\OrgDiscount;
use App\Person;
use App\Ticket;
use App\Track;
use App\ReferLink;
use App\Other\ics_calendar;
use Spatie\Referer\Referer;
use App\EventSession;


class EventController extends Controller
{
    public function __construct () {
        $this->middleware('auth', ['except' => ['show']]);
    }

    public function index () {
        // responds to /events

        $topBits        = '';
        $current_person = $this->currentPerson = Person::find(auth()->user()->id);

        $current_sql = "SELECT e.eventID, e.eventName, date_format(e.eventStartDate, '%Y/%m/%d %l:%i %p') AS eventStartDateF,
                               date_format(e.eventEndDate, '%Y/%m/%d %l:%i %p') AS eventEndDateF, e.isActive, e.eventStartDate, e.eventEndDate,
                               count(er.ticketID) AS 'cnt', et.etName, e.slug, e.hasTracks
                        FROM `org-event` e
                        LEFT JOIN `event-registration` er ON er.eventID=e.eventID AND er.regStatus != 'In Progress'
                        LEFT JOIN `org-event_types` et ON et.etID = e.eventTypeID AND et.orgID = e.orgID
                        WHERE e.orgID = ?
                            AND eventStartDate >= NOW() AND e.deleted_at is null
                        GROUP BY e.eventID, e.eventName, e.eventStartDate, e.eventEndDate, e.isActive, e.eventStartDate
                        ORDER BY e.eventStartDate ASC";

        /*
        $c = DB::table('org-event as oe')
               ->leftJoin('event-registration as er', 'er.eventID', '=', 'oe.eventID')
               ->leftJoin('org-event_types as et', 'et.etID', '=', 'oe.eventTypeID')
               ->where('et.orgID', '=', 'oe.orgID')
               ->where([
                   ['oe.orgID', '=', $this->currentPerson->defaultOrgID],
                   ['oe.eventStartDate', '>=', 'NOW()']
               ])
            ->groupBy('oe.eventID', 'oe.eventName', 'oe.eventStartDate', 'oe.eventEndDate', 'oe.isActive', 'oe.slug', 'oe.hasTracks')
            ->orderBy('oe.eventStartDate')
               ->select(DB::raw('oe.eventID, oe.eventName, oe.eventStartDate, oe.eventEndDate, oe.isActive,
                                 count(er.ticketID) as cnt, et.etName, oe.slug, oe.hasTracks'))
               ->get();

        */

        $past_sql = "SELECT e.eventID, e.eventName, date_format(e.eventStartDate, '%Y/%m/%d %l:%i %p') AS eventStartDateF,
                            date_format(e.eventEndDate, '%Y/%m/%d %l:%i %p') AS eventEndDateF, e.isActive, e.eventStartDate,
                            count(er.ticketID) AS 'cnt', et.etName, e.slug, e.hasTracks
                     FROM `org-event` e
                     LEFT JOIN `event-registration` er ON er.eventID=e.eventID
                     LEFT JOIN `org-event_types` et ON et.etID = e.eventTypeID AND et.orgID = e.orgID
                     WHERE e.orgID = ?
                        AND eventStartDate < NOW() AND e.deleted_at is null
                     GROUP BY e.eventID, e.eventName, e.eventStartDate, e.eventEndDate, e.isActive, e.eventStartDate
                     ORDER BY e.eventStartDate DESC";

        $current_events = DB::select($current_sql, [$this->currentPerson->defaultOrgID]);

        $past_events = DB::select($past_sql, [$this->currentPerson->defaultOrgID]);

        return view('v1.auth_pages.events.list', compact('current_events', 'past_events', 'topBits', 'current_person'));
    }

    public function event_copy ($param) {
        $today = Carbon::now();
        $event = Event::where('eventID', '=', $param)
                      ->orWhere('slug', '=', $param)
                      ->firstOrFail();

        $e           = $event->replicate();
        $e->slug     = 'temporary_slug_placeholder';
        $e->isActive = 0;
        $e->eventStartDate = $today;
        $e->eventEndDate = $today;
        // this is here until we decide to copy EVERYTHING associated with a PD Day event
        $e->hasTracks = 0;
        $e->save();
        $e->slug = $e->eventID;
        $e->save();

        $event = $e;

        $this->currentPerson = Person::find(auth()->user()->id);
        $current_person      = $this->currentPerson = Person::find(auth()->user()->id);
        $exLoc               = Location::find($event->locationID);
        $page_title          = 'Edit Copied Event';

        /*
         * Commented out because the store function creates the ticket stub
         # and mainSession for initial event
        // Create a stub for the default ticket for the event
        $label                    = Org::find($this->currentPerson->defaultOrgID);
        $tkt                      = new Ticket;
        $tkt->ticketLabel         = $label->defaultTicketLabel;
        $tkt->availabilityEndDate = $event->eventStartDate;
        $tkt->eventID             = $event->eventID;
        $tkt->earlyBirdPercent    = $label->earlyBirdPercent;
        $tkt->earlyBirdEndDate    = Carbon::now();
        $tkt->save();

        // Create a mainSession for the default ticket for the event
        $mainSession = new EventSession;
        $mainSession->trackID = 0;
        $mainSession->eventID = $event->eventID;
        $mainSession->ticketID = $tkt->ticketID;
        $mainSession->sessionName = 'Default Session';
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
        */

        if($event->eventStartDate > $today) {
            $orgDiscounts = OrgDiscount::where([['orgID', $this->currentPerson->defaultOrgID],
                ['discountCODE', "<>", '']])->get();

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
        return view('v1.auth_pages.events.add-edit_form', compact('current_person', 'page_title', 'event', 'exLoc'));
    }

    public function show ($param) {
        // responds to GET /events/{param}
        // $param is either an ID or slug
        $event = Event::where('eventID', '=', $param)
                      ->orWhere('slug', '=', $param)
                      ->firstOrFail();

        if(auth()->guest()) {
            $current_person = 0;
        } else {
            $this->currentPerson = Person::find(auth()->user()->id);
            $current_person      = $this->currentPerson;
        }

        //$referer = Referer::get();
        $referer = app(Referer::class)->get();

        if($referer) {
            $r              = new ReferLink;
            $r->objectType  = 'eventID';
            $r->objectID    = $event->eventID;
            $r->refererText = $referer;
            $r->save();
        }

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

        if($event->hasTracks > 0) {
            $tracks = Track::where('eventID', $event->eventID)->get();
            return view('v1.public_pages.display_event_w_sessions',
                compact('event', 'current_person', 'bundles', 'tickets', 'event_loc', 'org_stuff', 'tracks'));
        } else {
            return view('v1.public_pages.display_event',
                compact('event', 'current_person', 'bundles', 'tickets', 'event_loc', 'org_stuff'));
        }
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
        $today               = Carbon::now();
        $this->currentPerson = Person::find(auth()->user()->id);
        $event               = new Event;
        $label               = Org::find($this->currentPerson->defaultOrgID);

        if(request()->input('locationID') != '') {
            $location = Location::find(request()->input('locationID'));
            $locName  = request()->input('locName');
            $addr1    = request()->input('addr1');
            if($location->locName == $locName && $location->addr1 == $addr1) {
                $event->locationID = $location->locID;
            } else {
                $loc            = new Location;
                $loc->orgID     = $this->currentPerson->defaultOrgID;
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
            $loc->orgID     = $this->currentPerson->defaultOrgID;
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
        $event->slug             = request()->input('slug');

        if(request()->input('hasFood')) {
            $event->hasFood = 1;
        } else {
            $event->hasFood = 0;
        }
        /*
         *  Add these later:
         *  image1
         *  image2
         *  refund Note
         *  event tags
         */

        if(request()->input('hasTracksCheck') == 1) {
            $numTracks        = request()->input('hasTracks');
            $event->hasTracks = $numTracks;
            $count            = DB::table('event-tracks')->where('eventID', $event->eventID)->count();
            for($i = 1 + $count; $i <= request()->input('hasTracks'); $i++) {
                $track            = new Track;
                $track->trackName = "Track" . $i;
                $track->eventID   = $event->eventID;
                $track->save();
            }
        } else {
            $event->hasTracks = 0;
        }
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

        // Create a mainSession for the default ticket for the event
        $mainSession = new EventSession;
        $mainSession->trackID = 0;
        $mainSession->eventID = $event->eventID;
        $mainSession->ticketID = $tkt->ticketID;
        $mainSession->sessionName = 'Default Session';
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

        if($event->eventStartDate > $today) {
            $orgDiscounts = OrgDiscount::where([['orgID', $this->currentPerson->defaultOrgID],
                ['discountCODE', "<>", '']])->get();

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

        // Make the event_{id}.ics file if it doesn't exist
        $event_filename = 'event_' . $event->eventID . '.ics';
        $ical           = new ics_calendar($event);
        $contents       = $ical->get();
        Flysystem::connection('awss3')->put($event_filename, $contents, ['visibility' => AdapterInterface::VISIBILITY_PUBLIC]);

        return redirect('/event-tickets/' . $event->eventID);
    }

    public function edit (Event $event) {
        // responds to GET /events/id/edit and shows the add/edit form
        //$event               = Event::find($id);
        $this->currentPerson = Person::find(auth()->user()->id);
        $current_person      = $this->currentPerson = Person::find(auth()->user()->id);
        $exLoc               = Location::find($event->locationID);
        $page_title          = 'Edit Event';
        return view('v1.auth_pages.events.add-edit_form', compact('current_person', 'page_title', 'event', 'exLoc'));
    }

    public function checkSlugUniqueness (Request $request, $id) {
        $slug = request()->input('slug');
        if($id == 0) {
            if(Event::whereSlug($slug)->exists()) {
                $message = $slug . ' is <b style="color:red;">NOT</b> available';
            } elseif(Event::whereSlug($slug)->exists()) {
                $message = $slug . ' is available';
            } else {
                $message = $slug . ' is available';
            }
        } else {
            if(Event::whereSlug($slug)->where('eventID', '!=', $id)->exists()) {
                $message = $slug . ' is <b style="color:red;">NOT</b> available';
            } elseif(Event::whereSlug($slug)->exists()) {
                $message = $slug . ' is available';
            } else {
                $message = $slug . ' is available';
            }
        }
        return json_encode(array('status' => 'success', 'message' => $message));
    }

    public function update (Request $request, Event $event) {
        // responds to PATCH /events/id
        // $event               = Event::find($id);
        $this->currentPerson = Person::find(auth()->user()->id);
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
            // if not and also not empty, grab location and save data
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
        $event->slug = request()->input('slug');
        /*
         *  Add these later:
         *  image1
         *  image2
         *  refund Note
         *  event tags
         */
        if(request()->input('hasTracksCheck') == 1) {
            $numTracks        = request()->input('hasTracks');
            $event->hasTracks = $numTracks;
            $count            = DB::table('event-tracks')->where('eventID', $event->eventID)->count();
            for($i = 1 + $count; $i <= request()->input('hasTracks'); $i++) {
                $track            = new Track;
                $track->trackName = 'Track' . $i;
                $track->eventID   = $event->eventID;
                $track->save();
            }
        } else {
            $event->hasTracks = 0;
        }
        $event->updaterID = $this->currentPerson->personID;

        if($event->mainSession === null){
            $mainSession = new EventSession;
            $mainSession->trackID = 0;
            $mainSession->eventID = $event->eventID;
            $mainSession->sessionName = 'Default Session';
            $mainSession->confDay = 0;
            $mainSession->start = $event->eventStartDate;
            $mainSession->end = $event->eventEndDate;
            $mainSession->order = 0;
            $mainSession->creatorID = $this->currentPerson->personID;
            $mainSession->updaterID = $this->currentPerson->personID;
            $mainSession->save();

            $event->mainSession = $mainSession->sessionID;
            $event->updaterID = $this->currentPerson->personID;
        }
        $event->save();

        // Make and overwrite the event_{id}.ics file
        $event_filename = 'event_' . $event->eventID . '.ics';
        $ical           = new ics_calendar($event);
        $contents       = $ical->get();
        Flysystem::connection('awss3')->put($event_filename, $contents);

        // Think about whether ticket modification should be done here.
        // Maybe catch the auto-created tickets when events are copied

        return redirect('/event-tickets/' . $event->eventID);
    }

    public function destroy (Event $event) {
        // responds to DELETE /events/id

        $event->delete();
        return redirect('/events');
    }

    public function activate (Event $event) {
        // $event = Event::find($id);
        if($event->isActive == 1) {
            $event->isActive = 0;
        } else {
            $event->isActive = 1;
        }
        $event->updaterID = auth()->user()->id;
        $event->save();

        return json_encode(array('status' => 'success', 'message' => 'Activation successfully toggled.'));
    }

    public function ajax_update (Request $request, Event $event) {
        // this function is just for the quick update of the Early Bird End Date
        // and Percent Discount associated with the eventID $id from /event-tickets/{id}
        //$event               = Event::find($id);
        $this->currentPerson = Person::find(auth()->user()->id);

        $name  = request()->input('name');
        $value = request()->input('value');

        if($name == 'earlyBirdDate' and $value !== null) {
            $date  = date("Y-m-d H:i:s", strtotime(trim($value)));
            $value = $date;
        }

        $event->{$name}   = $value;
        $event->updaterID = $this->currentPerson->personID;
        $event->save();

        // now, either the date or percent changed, so update all event tickets
        // MUST figure out why this isn't working...
        $tickets = Ticket::where('eventID', $event->eventID)->get();
        foreach($tickets as $ticket) {
            if($name == 'earlyBirdDate' and $value !== null) {
                $ticket->earlyBirdEndDate = $value;
            } elseif($name == 'earlyDiscount') {
                $ticket->earlyBirdPercent = $value;
            }
            $ticket->updaterID = $this->currentPerson->personID;
            $ticket->save();
        }
        return redirect('/event-tickets/' . $event->eventID);
    }

    public function showGroup($event = null){
        $title = "Group Registration";
        $today = Carbon::now();
        $this->currentPerson = Person::find(auth()->user()->id);

        if(!isset($event)){
            $e = Event::where([
                ['orgID', '=', $this->currentPerson->defaultOrgID],
                ['eventStartDate', '>=', $today->subDays(10)]
            ])
                      ->select('eventID', 'eventName')
                      ->limit(2)
                      ->get();

            $a = $e->pluck('eventName', 'eventID');
            $b = array(0 => 'Select Event');
            $c = $a->toArray();
            $events = $b + $c;
            return view('v1.auth_pages.events.group-registration', compact('title', 'event', 'events'));
        } else {
            $event = Event::find($event);
            $title = $title . ": $event->eventName";
            $t = Ticket::where('eventID', '=', $event->eventID)->get();
            $a = $t->pluck('ticketLabel', 'ticketID');
            $b = array(0 => 'Select Ticket');
            $c = $a->toArray();
            $tickets = $b + $c;

            $a = EventDiscount::where('eventID', '=', $event->eventID)->get();
            $b = array(0 => 'Select');
            $c = $a->pluck('discountCODE', 'discountCODE');
            $d = $c->toArray();

            $discounts = $b + $d;

            return view('v1.auth_pages.events.group-registration', compact('title', 'event', 'tickets', 'discounts'));
        }

        //return view('v1.auth_pages.events.group-registration', compact('event'));
    }

    public function group_reg1(Request $request){
        $this->currentPerson = Person::find(auth()->user()->id);
        $seats = 0; $total_cost = 0; $total_orig = 0; $total_handle = 0;
        $today = Carbon::now();

        //dd(request()->all());
        for($i=1; $i<=15; $i++){
            $personID = request()->input('person-'.$i);
            $firstName = request()->input('firstName-'.$i);
            $lastName = request()->input('lastName-'.$i);
            $email = request()->input('email-'.$i);
            $pmiid = request()->input('pmiid-'.$i);
            $ticketID = request()->input('ticketID-'.$i);
            $code = request()->input('code-'.$i);

            if($personID === null && $firstName !== null){
                // create requisite records: person, orgperson
                $p = new Person;
                $p->firstName = $firstName;
                $p->lastName = $lastName;
                $p->defaultOrgID = $this->currentPerson->defaultOrgID;
                $p->login = $email;
                $p->creatorID = $this->currentPerson->personID;
                $p->save();

                $u = new User;
                $u->id = $p->personID;
                $u->login = $email;
                $u->email = $email;
                $u->save();

                $op = new OrgPerson;
                $op->personID = $p->personID;
                $op->orgID = $p->defaultOrgID;
                $op->OrgStat1 = $pmiid;
                $op->save();

            } else {
                // get the person record from $personID
                $p = Person::find($personID);
            }

            // Create a registration record for each attendee
            $eventID = request()->input('eventID');

            $handle = 0;
            if($p){
                // Setup variables for valid attendee
                if($code === null){ $code = 'N/A';}
                $t = Ticket::find($ticketID);
                // tr: ticket remember
                $tr = $t->ticketID;
                // cr: code remember
                $cr = $code;
                $seats++;

                $reg = new Registration;
                $reg->eventID = $eventID;
                $reg->ticketID = $ticketID;
                $reg->personID = $p->personID;
                if($p->allergenInfo){
                    $reg->allergenInfo = $p->allergenInfo;
                }
                $reg->registeredBy = $this->currentPerson->showFullName();
                $reg->discountCode = $code;
                if($pmiid){
                    $reg->membership = "Member";
                } else {
                    $reg->membership = "Non-Member";
                }
                $reg->token = request()->input('_token');
                $reg->creatorID = $this->currentPerson->personID;
                if($p->affiliation){
                    $reg->affiliation = $p->affiliation;
                }
                $reg->canNetwork = 1;
                if($pmiid){
                    $reg->isAuthPDU = 1;
                } else {
                    $reg->isAuthPDU = 0;
                }
                // origcost
                // subtotal
                if($t->earlyBirdEndDate !== null && $today->lte($t->earlyBirdEndDate)){
                    // Use earlybird discount pricing as base
                    if($reg->membership == 'Member'){
                        $reg->origcost = $t->memberBasePrice;
                        $reg->subtotal = $t->memberBasePrice - ($t->memberBasePrice * $t->earlyBirdPercent);
                    } else {
                        $reg->origcost = $t->nonmbrBasePrice;
                        $reg->subtotal = $t->nonmbrBasePrice - ($t->nonmbrBasePrice * $t->earlyBirdPercent);
                    }
                } else {
                    // Use non-discount pricing
                    if($reg->membership == 'Member'){
                        $reg->origcost = $t->memberBasePrice;
                        $reg->subtotal = $t->memberBasePrice;
                    } else {
                        $reg->origcost = $t->nonmbrBasePrice;
                        $reg->subtotal = $t->nonmbrBasePrice;
                    }
                }
                if($code){
                    $dCode = EventDiscount::where([
                        ['eventID', $eventID],
                        ['discountCODE', $code]
                    ])->first();
                    if($dCode->percent > 0){
                        $reg->subtotal = $reg->subtotal - ($reg->subtotal * $dCode->percent/100);
                    } else {
                        $reg->subtotal = $reg->subtotal - $dCode->flatAmt;
                    }
                }
                $reg->regStatus = 'In Progress';
                $reg->save();
                $total_orig = $total_orig + $reg->origcost;
                $total_cost = $total_cost + $reg->subtotal;
                $handle = $reg->subtotal * 0.029;
                if($handle > 5){$handle = 5;}
                $total_handle = $total_handle + $handle;
            }
        }
        // Create a regfinance record for all of the attendees
        // Show a group receipt
        $rf = new RegFinance;
        $rf->regID = $reg->regID;
        $rf->eventID = $eventID;
        $rf->ticketID = $tr;
        $rf->discountCode = $cr;
        $rf->seats = $seats;
        $rf->personID = $this->currentPerson->personID;
        $rf->cost = $total_cost;
        $rf->status = 'In Progress';
        $rf->handleFee = $total_handle;
        $rf->token = request()->input('_token');
        $rf->save();
        return redirect('/groupreg/'.$rf->regID);
    }

}
