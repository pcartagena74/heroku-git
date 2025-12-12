<?php

// 2024-05-18: `org-event`.isPrivate = 1 will be used to account for wait list features being active

namespace App\Http\Controllers;

ini_set('max_execution_time', 0);

use App\Models\Event;
use App\Models\EventDiscount;
use App\Models\EventSession;
use App\Models\Location;
use App\Models\Org;
use App\Models\OrgDiscount;
use App\Models\Person;
use App\Models\ReferLink;
use App\Models\Ticket;
use App\Models\Track;
use App\Other\ics_cal_full;
use App\Other\ics_calendar;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\View\View;
use Spatie\Referer\Referer;

class EventController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth', ['except' => ['show', 'listing', 'ticket_listing', 'ics_listing', 'get_tix']]);
    }

    // Helper function containing code to put the event counting bits into a blade template
    protected function event_bits()
    {
        $topBits = [];
        $today = Carbon::now();

        $upcoming = trans('messages.fields.up') . ' ';
        $rtw = trans('messages.headers.regs_this_week');

        $upcoming_events = Cache::get('upcoming-events', function () use ($today) {
            return Event::where([
                ['eventStartDate', '>=', $today],
                ['orgID', $this->currentPerson->defaultOrgID],
            ])->get();
        });

        /*
        $ch_mtg = Cache::get('future_cm', function () use ($today) {
            return Event::where([
                ['eventStartDate', '>=', $today],
                ['orgID', $this->currentPerson->defaultOrgID],
                ['eventTypeID', 1],
            ])->get();
        });

        foreach ($ch_mtg as $cm) {
            $cm_count += $cm->week_sales();
        }
        */

        $cm_count = 0;
        foreach ($ch_mtg = $upcoming_events->where('eventTypeID', 1) as $ue) {
            $cm_count += $ue->week_sales();
        }
        $cm_label = trans_choice('messages.event_types.Chapter Meeting', 2);

        /*
        $roundtables = Cache::get('future_roundtables', function () use ($today) {
            return Event::where([
                ['eventStartDate', '>=', $today],
                ['orgID', $this->currentPerson->defaultOrgID],
                ['eventTypeID', 2],
            ])->get();
        });

        $rt_count = 0;
        foreach ($roundtables as $rt) {
            $rt_count += $rt->week_sales();
        }
        */

        $rt_count = 0;
        foreach ($roundtables = $upcoming_events->where('eventTypeID', 2) as $ue) {
            $rt_count += $ue->week_sales();
        }

        $rt_label = trans_choice('messages.event_types.Roundtable', 2);

        /*
        $socials = Cache::get('future_socials', function () use ($today) {
            return Event::where([
                ['eventStartDate', '>=', $today],
                ['orgID', $this->currentPerson->defaultOrgID],
                ['eventTypeID', 4],
            ])->get();
        });

        $so_count = 0;
        foreach ($socials as $so) {
            $so_count += $so->week_sales();
        }
        */

        $so_count = 0;
        foreach ($socials = $upcoming_events->where('eventTypeID', 4) as $ue) {
            $so_count += $ue->week_sales();
        }
        $so_label = trans_choice('messages.event_types.Social Gathering', 2);

        /*
        $pddays = Cache::get('future_pddays', function () use ($today) {
            return Event::where([
                ['eventStartDate', '>=', $today],
                ['orgID', $this->currentPerson->defaultOrgID],
                ['eventTypeID', 3],
            ])->get();
        });

        $pd_count = 0;
        foreach ($pddays as $pd) {
            $pd_count += $pd->week_sales();
        }
        */

        $pd_count = 0;
        foreach ($pddays = $upcoming_events->where('eventTypeID', 3) as $ue) {
            $pd_count += $ue->week_sales();
        }
        $pd_label = trans_choice('messages.event_types.PD Day', 2);

        /*
        $jobs = Cache::get('future_job_fairs', function () use ($today) {
            return Event::where([
                ['eventStartDate', '>=', $today],
                ['orgID', $this->currentPerson->defaultOrgID],
                ['eventTypeID', 9],
            ])->get();
        });

        $jf_count = 0;
        foreach ($jobs as $jf) {
            $jf_count += $jf->week_sales();
        }
        */

        $jf_count = 0;
        foreach ($jobs = $upcoming_events->where('eventTypeID', 9) as $ue) {
            $jf_count += $ue->week_sales();
        }
        $jf_label = trans_choice('messages.event_types.Job Fair', 2);

        /*
        $all = Cache::get('all_future_events', function () use ($today) {
            return Event::where([
                ['eventStartDate', '>=', $today],
                ['orgID', $this->currentPerson->defaultOrgID],
            ])->get();
        });

        $ae_count = 0;
        foreach ($all as $ae) {
            $ae_count += $ae->week_sales();
        }
        */
        $ae_count = 0;
        foreach ($all = $upcoming_events as $ue) {
            $ae_count += $ue->week_sales();
        }

        // sets $which to "Upcoming"
        $which = trans_choice('messages.var_words.time_period', 0);
        $ae_label = trans('messages.codes.etID99', ['which' => $which]);

        $topBits[] = [3, $upcoming . $cm_label, count($ch_mtg), $cm_count, $rtw, $cm_count > 0 ? 1 : -1, 2];
        $topBits[] = [3, $upcoming . $rt_label, count($roundtables), $rt_count, $rtw, $rt_count > 0 ? 1 : -1, 2];
        $topBits[] = [3, $upcoming . $so_label, count($socials), $so_count, $rtw, $so_count > 0 ? 1 : -1, 2];
        $topBits[] = [3, $upcoming . $pd_label, count($pddays), $pd_count, $rtw, $pd_count > 0 ? 1 : -1, 2];
        $topBits[] = [3, $upcoming . $jf_label, count($jobs), $jf_count, $rtw, $jf_count > 0 ? 1 : -1, 2];
        $topBits[] = [3, $ae_label, count($all), $ae_count, $rtw, $ae_count > 0 ? 1 : -1, 2];

        return $topBits;
    }

    public function index($past = null): View
    {
        // responds to GET /manage_events
        $this->topBits = $this->event_bits();
        $this->past = $past;

        $today = Carbon::now();

        if ($this->past === null) {
            // Function called was "Manage Events" so there should be future events and limited past events"
            // Get a list of current events, showing events that have not yet ended.
            $this->current_events = Event::with('registrations', 'event_type', 'location')
                ->select('eventID', 'eventName', 'eventStartDate', 'eventEndDate', 'org-event.isActive',
                    'hasTracks', 'etName', 'slug', 'eventTypeID', 'locationID')
                ->where([
                    ['org-event.orgID', $this->currentPerson->defaultOrgID],
                ])
                ->where(function ($q) use ($today) {
                    $q->orWhere('eventEndDate', '>=', $today);
                    $q->orWhereBetween('eventStartDate', [$today->addDays(-1), $today->addDays(1)]);
                })
                ->join('org-event_types as oet', 'oet.etID', '=', 'eventTypeID')
                ->withCount('registrations')
                ->orderBy('eventStartDate', 'ASC')
                ->get();

            $this->past_events = Event::select('eventID', 'eventName', 'eventStartDate', 'eventEndDate', 'org-event.isActive',
                'hasTracks', 'etName', 'slug', 'eventTypeID', 'locationID')
                ->where([
                    ['org-event.orgID', $this->currentOrg->orgID],
                    ['eventEndDate', '<', $today],
                ])
                ->whereIn(DB::raw('year(eventEndDate)'), [$today->year, $today->year - 1])
                ->join('org-event_types as oet', 'oet.etID', '=', 'eventTypeID')
                ->with('registrations', 'event_type', 'location')
                ->withCount('registrations')
                ->orderBy('eventStartDate', 'DESC')
                ->get();
        } else {
            $this->current_events = null;
            $this->past_events = Event::select('eventID', 'eventName', 'eventStartDate', 'eventEndDate', 'org-event.isActive',
                'hasTracks', 'etName', 'slug', 'eventTypeID', 'locationID')
                ->where([
                    ['org-event.orgID', $this->currentOrg->orgID],
                    ['eventEndDate', '<', $today],
                ])
                ->join('org-event_types as oet', 'oet.etID', '=', 'eventTypeID')
                ->with('registrations', 'event_type', 'location')
                ->withCount('registrations')
                ->orderBy('eventStartDate', 'DESC')
                ->get();
        }

        return view('v1.auth_pages.events.list', $this->data);
    }

    public function event_copy($param)
    {
        $today = Carbon::now();
        try {
            $event = Event::when(
                filter_var($param, FILTER_VALIDATE_INT) !== false,
                function ($query) use ($param) {
                    return $query->where('eventID', $param);
                },
                function ($query) use ($param) {
                    return $query->where('slug', $param);
                }
            )->firstOrFail();
        } catch (\Exception $exception) {
            $message = trans('messages.warning.inactive_event_url');

            return view('v1.public_pages.error_display', compact('message'));
        }

        $e = $event->replicate();
        $e->slug = 'temp_' . rand();
        $e->isActive = 0;
        // 8/24/25: Adding 2 days to give user time to update copied event
        $e->eventStartDate = $today->addDays(2);
        $e->eventEndDate = $today->addDays(2);
        // this is here until we decide to copy EVERYTHING associated with a PD Day event
        $e->hasTracks = 0;
        $e->save();
        $e->slug = $e->eventID;
        $e->save();

        $event = $e;

        $exLoc = Location::find($event->locationID);
        $page_title = trans('messages.fields.edit_copy');

        // CHANGE: get the ticket(s) associated with the original event, replicate, and change eventStartDate & earlyBirdEndDate
        $label = $this->currentOrg;
        $tkt = new Ticket;
        $tkt->ticketLabel = $label->defaultTicketLabel;
        $tkt->availabilityEndDate = $event->eventStartDate;
        $tkt->eventID = $event->eventID;
        $tkt->earlyBirdPercent = $label->earlyBirdPercent;
        $tkt->earlyBirdEndDate = Carbon::now();
        $tkt->save();

        // CHANGE: get the session(s) associated with the original event, replicate, and change the ticketID, start, end, creator/updater
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
        try {
            $event->save();
        } catch (\Exception $exception) {
        }

        // A copied event should always get the discount codes.
        $orgDiscounts = OrgDiscount::where([['orgID', $this->currentOrg->orgID],
            ['discountCODE', '<>', ''],])->get();

        // CHANGE: decide if the original event's EventDiscounts should be copied instead.
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
        return redirect('/event/' . $event->eventID . '/edit');
    }

    public function show($param, $override = null)
    {
        // responds to GET /events/{param}
        // $param is either an ID or slug

        $this->override = $override;
        try {
            $event = Event::when(
                filter_var($param, FILTER_VALIDATE_INT) !== false,
                function ($query) use ($param) {
                    return $query->where('eventID', $param);
                },
                function ($query) use ($param) {
                    return $query->where('slug', $param);
                }
            )->firstOrFail();
        } catch (\Exception $exception) {
            $message = trans('messages.warning.inactive_event_url');

            return view('v1.public_pages.error_display', compact('message'));
        }
        if ($override) {
            if ($override == 'unlock') {
                $message = trans('messages.warning.inactive_unlocked_event');
            } else {
                $message = trans('messages.warning.inactive_event');
            }
            request()->session()->flash('alert-warning', $message);
        }

        $referrer = app(Referer::class)->get();

        if ($referrer) {
            $r = new ReferLink;
            $r->objectType = 'eventID_reg';
            $r->objectID = $event->eventID;
            $r->referrerText = $referrer;
            $r->save();
        }

        $today = Carbon::now();

        if (auth()->guest()) {
            $this->current_person = 0;
        } else {
            $this->current_person = $this->currentPerson;
        }
        $currentOrg = $this->currentOrg;

        $this->event_loc = Location::where('locID', $event->locationID)->first();
        // $orgLogoPath = Org::where('orgID', $event->orgID)->select('orgPath', 'orgLogo')->first();
        $this->orgLogoPath = $this->currentOrg->select('orgPath', 'orgLogo')->first();
        $this->bundles =
            Ticket::where([
                ['isaBundle', 1],
                ['isSuppressed', 0],
                ['eventID', $event->eventID],
                ['availabilityEndDate', '>=', $today],
            ])->get()->sortByDesc('availabilityEndDate');

        $this->tickets =
            Ticket::where([
                ['isaBundle', 0],
                ['isSuppressed', 0],
                ['eventID', $event->eventID],
                ['availabilityEndDate', '>=', $today],
            ])->get()->sortByDesc('availabilityEndDate');

        $this->tracks = Track::where('eventID', $event->eventID)->get();
        // $member = '';added for working_regform_show
        // $nonmbr = '';

        // 'v1.public_pages.display_event_w_sessions2',
        return view('v1.public_pages.event_show', $this->data);
    }

    public function create(): View
    {
        // responds to /events/create and shows add/edit form
        $current_person = $this->currentPerson;
        $this->org = $this->currentOrg;
        $this->page_title = trans('messages.headers.event_new');

        return view('v1.auth_pages.events.add-edit_form', $this->data);
    }

    public function store(Request $request)
    {
        // responds to POST to /events and creates, adds, stores the event
        $today = Carbon::now();
        $event = new Event;
        $label = $this->currentOrg;
        $slug = request()->input('slug');
        $slug_not_unique = Event::where('slug', $slug)->withTrashed()->first();
        if ($slug_not_unique !== null) {
            request()->session()->flash('alert-danger', trans('messages.flashes.custom_slug'));
            return back()->withInput();
        }
        $loc_virtual = request()->input('virtual');
        if (empty($loc_virtual)) {
            $loc_virtual = 0;
        }

        $loc = location_triage($request, $event, $this->currentPerson);

        $event->locationID = $loc->locID;

        $event->orgID = $this->currentOrg->orgID;
        $event->eventName = request()->input('eventName');
        $eventDescription = request()->input('eventDescription');
        if ($eventDescription !== null) {
            if (str_contains($eventDescription, 'data:image')) {
                $eventDescription = extract_images($eventDescription, $event->orgID);
            }
        }
        $event->eventDescription = $eventDescription;
        $eventInfo = request()->input('eventInfo');
        if ($eventInfo !== null) {
            if (str_contains($eventInfo, 'data:image')) {
                $eventInfo = extract_images($eventInfo, $event->orgID);
            }
        }
        $event->eventInfo = $eventInfo;
        $event->catID = request()->input('catID');
        $event->eventTypeID = request()->input('eventTypeID');
        $event->eventStartDate = request()->input('eventStartDate');
        $event->eventEndDate = request()->input('eventEndDate');
        $event->eventTimeZone = request()->input('eventTimeZone');
        $event->contactOrg = request()->input('contactOrg');
        $event->contactEmail = request()->input('contactEmail');
        $event->contactDetails = request()->input('contactDetails');
        $event->showLogo = request()->input('showLogo');
        $event->hasFood = request()->input('hasFood');
        $event->slug = request()->input('slug');
        $postRegInfo = request()->input('postRegInfo');
        if ($postRegInfo !== null) {
            if (str_contains($postRegInfo, 'data:image')) {
                $postRegInfo = extract_images($postRegInfo, $event->orgID);
            }
        }
        $event->postRegInfo = $postRegInfo;
        // Intentionally set to 0 so that track selection works without issue
        $event->confDays = 0;

        if (request()->input('hasFood')) {
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

        if (request()->input('hasTracksCheck') == 1) {
            $numTracks = request()->input('hasTracks');
            $event->hasTracks = $numTracks;
        } else {
            $event->hasTracks = 0;
        }
        $event->earlyDiscount = $label->earlyBirdPercent;
        $event->earlyBirdDate = Carbon::now();
        $event->creatorID = $this->currentPerson->personID;
        $event->updaterID = $this->currentPerson->personID;

        try {
            $event->save();
        } catch (\Exception $exception) {
        }

        if (request()->input('hasTracksCheck') == 1) {
            $count = DB::table('event-tracks')->where('eventID', $event->eventID)->count();
            for ($i = 1 + $count; $i <= request()->input('hasTracks'); $i++) {
                $track = new Track;
                $track->trackName = 'Track' . $i;
                $track->eventID = $event->eventID;
                $track->save();
            }
        }
        // Create a stub for the default ticket for the event
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
        $mainSession->leadAmt = request()->input('leadAmt');
        $mainSession->stratAmt = request()->input('stratAmt');
        $mainSession->techAmt = request()->input('techAmt');
        $mainSession->creatorID = $this->currentPerson->personID;
        $mainSession->updaterID = $this->currentPerson->personID;
        $mainSession->save();

        $event->mainSession = $mainSession->sessionID;
        $event->updaterID = $this->currentPerson->personID;
        try {
            $event->save();
        } catch (\Exception $exception) {
        }

        if ($event->eventStartDate > $today) {
            $orgDiscounts = OrgDiscount::where([['orgID', $this->currentPerson->defaultOrgID],
                ['discountCODE', '<>', ''],])->get();

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
        }

        // Update (or make) the event_{id}.ics file if it doesn't exist
        $event->create_or_update_event_ics();

        return redirect('/event-tickets/' . $event->eventID);
    }

    public function edit(Event $event): View
    {
        // responds to GET /events/id/edit and shows the add/edit form
        //$event               = Event::find($id);
        $this->current_person = $this->currentPerson;
        $this->org = $this->currentOrg;
        $this->exLoc = Location::find($event->locationID);
        $this->page_title = trans('messages.headers.event_edit');
        if ($event->mainSession === null) {
            $es = new EventSession;
            $es->eventID = $event->eventID;
            $es->save();
            $event->mainSession = $es->sessionID;
            $event->save();
        }
        $this->event = $event;

        return view('v1.auth_pages.events.add-edit_form', $this->data);
    }

    public function checkSlugUniqueness(Request $request, $id)
    {
        $slug = request()->input('slug');
        if ($id == 0) {
            if (Event::whereSlug($slug)->withTrashed()->exists()) {
                $message = $slug . ' is <b style="color:red;">NOT</b> available';
                //            } elseif (Event::whereSlug($slug)->exists()) {
                //                $message = $slug . ' is available';
            } else {
                $message = $slug . ' is available';
            }
        } else {
            if (Event::whereSlug($slug)->withTrashed()->where('eventID', '!=', $id)->exists()) {
                $message = $slug . ' is <b style="color:red;">NOT</b> available';
                //            } elseif (Event::whereSlug($slug)->exists()) {
                //                $message = $slug . ' is available';
            } else {
                $message = $slug . ' is available';
            }
        }

        return json_encode(['status' => 'success', 'message' => $message]);
    }

    public function update(Request $request, Event $event)
    {
        // responds to PATCH /event/id
        $slug = request()->input('slug');
        $skip = request()->input('sub_changes');
        $slug_not_unique = Event::where([
            ['slug', '=', $slug],
            ['eventID', '!=', $event->eventID],
        ])->withTrashed()->first();
        if ($slug_not_unique !== null) {
            request()->session()->flash('alert-danger', trans('messages.errors.slug_error'));
            return back()->withInput();
        }

        $original = $event->getOriginal();

        $loc = location_triage($request, $event, $this->currentPerson);

        $event->locationID = $loc->locID;
        $event->eventName = request()->input('eventName');
        $eventDescription = request()->input('eventDescription');
        if ($eventDescription !== null) {
            if (str_contains($eventDescription, 'data:image')) {
                $eventDescription = extract_images($eventDescription, $event->orgID);
            }
        }
        $event->eventDescription = $eventDescription;
        $eventInfo = request()->input('eventInfo');
        if ($eventInfo !== null) {
            if (str_contains($eventInfo, 'data:image')) {
                $eventInfo = extract_images($eventInfo, $event->orgID);
            }
        }
        $event->eventInfo = $eventInfo;
        //$event->eventDescription = request()->input('eventDescription');
        //$event->eventInfo = request()->input('eventInfo');
        $event->catID = request()->input('catID');
        $event->eventTypeID = request()->input('eventTypeID');
        $event->eventStartDate = request()->input('eventStartDate');
        $event->eventEndDate = request()->input('eventEndDate');
        $event->eventTimeZone = request()->input('eventTimeZone');
        $event->contactOrg = request()->input('contactOrg');
        $event->contactEmail = request()->input('contactEmail');
        $event->contactDetails = request()->input('contactDetails');
        $event->showLogo = request()->input('showLogo');
        if (request()->input('hasFood')) {
            $event->hasFood = 1;
        } else {
            $event->hasFood = 0;
        }
        $event->slug = request()->input('slug');
        $postRegInfo = request()->input('postRegInfo');
        if ($postRegInfo !== null) {
            if (str_contains($postRegInfo, 'data:image')) {
                $postRegInfo = extract_images($postRegInfo, $event->orgID);
            }
        }
        $event->postRegInfo = $postRegInfo;
        /*
         *  Add these later:
         *  image1
         *  image2
         *  refund Note
         *  event tags
         */
        if (request()->input('hasTracksCheck') == 1) {
            $numTracks = request()->input('hasTracks');
            $event->hasTracks = $numTracks;
            $count = DB::table('event-tracks')->where('eventID', $event->eventID)->count();
            for ($i = 1 + $count; $i <= request()->input('hasTracks'); $i++) {
                $track = new Track;
                $track->trackName = 'Track' . $i;
                $track->eventID = $event->eventID;
                $track->save();
            }
        } else {
            $event->hasTracks = 0;
        }
        $event->updaterID = $this->currentPerson->personID;
        $today = Carbon::now();

        // Edit all tickets for the event ONLY IF the end date changed.
        if ($original['eventEndDate'] != $event->eventEndDate) {
            $tkts = Ticket::where('eventID', $event->eventID)->get();
            foreach ($tkts as $tkt) {
                $tkt->availabilityEndDate = $event->eventStartDate;
                $tkt->save();
            }
        }

        $event_discounts = EventDiscount::where('eventID', $event->eventID)->get();
        if ($event->eventStartDate > $today && !$event_discounts) {
            $orgDiscounts = OrgDiscount::where([
                ['orgID', $this->currentOrg->orgID],
                ['discountCODE', '<>', ''],])
                ->orWhere('discountCODE', '!=', 0)
                ->whereNotNull('discountCODE')->get();

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
        }

        if ($event->mainSession === null) {
            $mainSession = new EventSession;
            $mainSession->trackID = 0;
            $mainSession->eventID = $event->eventID;
            $mainSession->sessionName = 'def_sess';
            $mainSession->confDay = 0;
            $mainSession->start = $event->eventStartDate;
            $mainSession->end = $event->eventEndDate;
            $mainSession->order = 0;
            $mainSession->leadAmt = request()->input('leadAmt');
            $mainSession->stratAmt = request()->input('stratAmt');
            $mainSession->techAmt = request()->input('techAmt');
            $mainSession->creatorID = $this->currentPerson->personID;
            $mainSession->updaterID = $this->currentPerson->personID;
            $mainSession->save();

            $event->mainSession = $mainSession->sessionID;
            $event->updaterID = $this->currentPerson->personID;
        } else {
            $mainSession = EventSession::find($event->mainSession);
            $mainSession->trackID = 0;
            $mainSession->eventID = $event->eventID;
            $mainSession->sessionName = 'def_sess';
            $mainSession->confDay = 0;
            $mainSession->start = $event->eventStartDate;
            $mainSession->end = $event->eventEndDate;
            $mainSession->order = 0;
            $mainSession->leadAmt = request()->input('leadAmt');
            $mainSession->stratAmt = request()->input('stratAmt');
            $mainSession->techAmt = request()->input('techAmt');
            $mainSession->creatorID = $this->currentPerson->personID;
            $mainSession->updaterID = $this->currentPerson->personID;
            $mainSession->save();
        }
        try {
            $event->save();
        } catch (\Exception $exception) {
        }

        // Make and/or overwrite the event_{id}.ics file
        $event->create_or_update_event_ics();

        // Think about whether ticket modification should be done here.
        // Maybe catch the auto-created tickets when events are copied

        if ($skip === null) {
            return redirect(config('APP_URL') . '/event-tickets/' . $event->eventID);
        } else {
            return redirect(config('APP_URL') . '/manage_events');
        }
    }

    public function destroy(Event $event): RedirectResponse
    {
        // responds to DELETE /events/id
        $event->delete();
        return redirect('/manage_events');
    }

    public function activate(Event $event)
    {
        // $event = Event::find($id);
        if ($event->isActive == 1) {
            $event->isActive = 0;
        } else {
            if ($event->hasTracks) {
                // determine what checks should be performed
            }
            $event->isActive = 1;
        }
        $event->updaterID = $this->user->id;
        try {
            $event->save();
        } catch (\Exception $exception) {
        }
        return json_encode(['status' => 'success', 'message' => 'Activation successfully toggled.']);
    }

    /**
     * This function allows the quick update of the Early Bird End Date and Percent Discount
     * associated with eventID $id from /event-tickets/{id}
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Laravel\Lumen\Http\Redirector
     */
    public function ajax_update(Request $request, Event $event): RedirectResponse
    {
        $name = request()->input('name');
        $value = request()->input('value');

        if ($name == 'earlyBirdDate' and $value !== null) {
            $date = date('Y-m-d H:i:s', strtotime(trim($value)));
            $value = $date;
        }

        $event->{$name} = $value;
        $event->updaterID = $this->currentPerson->personID;
        try {
            $event->save();
        } catch (\Exception $exception) {
        }

        // now, either the date or percent changed, so update all event tickets
        // MUST figure out why this isn't working...
        $tickets = Ticket::where('eventID', $event->eventID)->get();
        foreach ($tickets as $ticket) {
            if ($name == 'earlyBirdDate' and $value !== null) {
                $ticket->earlyBirdEndDate = $value;
            } elseif ($name == 'earlyDiscount') {
                $ticket->earlyBirdPercent = $value;
            }
            $ticket->updaterID = $this->currentPerson->personID;

            try {
                $ticket->save();
            } catch (\Exception $exception) {
            }
        }
        return redirect('/event-tickets/' . $event->eventID);
    }

    public function showGroup($event = null, $override = null): View
    {
        $this->title = trans('messages.headers.group_reg');
        $today = Carbon::now();

        if (!isset($event)) {
            if (Auth::user()->hasRole('Developer')) {
                $e = Event::where([
                    ['orgID', '=', $this->currentOrg->orgID],
                    ['eventStartDate', '>=', $today->subDays(10)],
                ])
                    ->select(DB::raw("eventID,
                                      concat(date_format(eventStartDate, '%m/%d/%Y'),
                                        ': ',
                                        eventName,
                                        ' (id:', eventID, ')') as eventName"))
                    ->get();
            } else {
                $e = Event::where([
                    ['orgID', '=', $this->currentOrg->orgID],
                    ['eventStartDate', '>=', $today->subDays(10)],
                ])
                    ->select(DB::raw("eventID, concat(date_format(eventStartDate, '%m/%d/%Y'), ': ', eventName) as eventName"))
                    ->get();
            }

            $a = $e->pluck('eventName', 'eventID');
            $b = [null => trans('messages.admin.upload.select')];
            $c = $a->toArray();
            $this->events = $b + $c;
            $this->event = $event;

            return view('v1.auth_pages.events.registration.group-registration',
                $this->data);
        } else {
            // Cannot pass object as reference so need to set here
            $this->event = Event::find($event);
            $override ? $this->check = 1 : $this->check = 0;
            $this->title = $this->title . ": $event->eventName";
            $t = Ticket::where([
                ['eventID', $event->eventID],
                ['isSuppressed', 0]
            ])->get();
            if (count($t) > 1) {
                $a = $t->pluck('ticketLabel', 'ticketID');
                $b = [null => trans('messages.headers.sel_tkt')];
                $c = $a->toArray();
                $this->tickets = $b + $c;
            } else {
                $this->tickets = $t->first()->ticketID;
            }

            $a = EventDiscount::where('eventID', '=', $event->eventID)->get();
            $b = [0 => 'Select'];
            $c = $a->pluck('discountCODE', 'discountCODE');
            $d = $c->toArray();

            $this->discounts = $b + $d;

            return view('v1.auth_pages.events.registration.group-registration',
                $this->data);
        }
    }

    public function listing($orgID, $etID, $override = null)
    {
        try {
            if ($this->currentOrg->orgID == $orgID) {
                $this->org = $this->currentOrg;
            } else {
                $this->org = Org::find($orgID);
            }
        } catch (\Exception $exception) {
            $message = trans('messages.instructions.no_org');
            return view('v1.public_pages.error_display', 'message');
        }

        // Check to see if $etID is sent as a comma-separated list of etIDs
        if (str_contains($etID, ',')) {
            // change value of $etID to be the list of things if it's a list
            $etID_array = explode(',', $etID);
            $tag = DB::table('org-event_types')->whereIn('etID', $etID_array)->pluck('etName')->toArray();
            $tag = array_map('et_translate', $tag);
            $tag = implode(' or ', (array)$tag);

            $events = Event::where([
                ['orgID', $orgID],
                ['isActive', 1],
            ])
                ->whereIn('eventTypeID', $etID_array)
                ->whereDate('eventStartDate', '>=', Carbon::today()->toDateString())
                ->with('location')
                ->orderBy('eventStartDate')
                ->get();
        } else {
            $tag = DB::table('org-event_types')->where('etID', $etID)->select('etName')->first();
            if (Lang::has('messages.event_types' . $tag->etName)) {
                $tag->etName = trans_choice('messages.event_types.' . $tag->etName, 1);
            } else {
                // $tag = $tag->etName;
            }

            if ($etID == 1) {
                $events = Event::where([
                    ['orgID', $orgID],
                    ['isActive', 1],
                ])
                    ->whereIn('eventTypeID', [3, $etID])
                    ->whereDate('eventStartDate', '>=', Carbon::today()->toDateString())
                    ->with('location')
                    ->orderBy('eventStartDate')
                    ->get();
            } elseif ($etID == 99) {
                $events = Event::where([
                    ['orgID', $orgID],
                    ['isActive', 1],
                ])
                    ->whereDate('eventStartDate', '>=', Carbon::today()->toDateString())
                    ->with('location')
                    ->orderBy('eventStartDate')
                    ->get();
            } else {
                $events = Event::where([
                    ['orgID', $orgID],
                    ['eventTypeID', $etID],
                    ['isActive', 1],
                ])
                    ->whereDate('eventStartDate', '>=', Carbon::today()->toDateString())
                    ->with('location')
                    ->orderBy('eventStartDate')
                    ->get();
            }
        }

        $this->cnt = count($events);
        $this->events = $events;
        $this->tag = $tag;
        $this->etID = $etID;

        if ($override) {
            $view = view('v1.public_pages.eventlist', $this->data)->render();
            $view = trim(preg_replace('/\r\n/', ' ', $view));
            return json_encode(['status' => 'success', 'message' => $view]);
        } else {
            return view('v1.public_pages.eventlist', $this->data);
        }
    }

    public function ticket_listing($param, $override = null)
    {
        try {
            $this->event = Event::when(
                filter_var($param, FILTER_VALIDATE_INT) !== false,
                function ($query) use ($param) {
                    return $query->where('eventID', $param);
                },
                function ($query) use ($param) {
                    return $query->where('slug', $param);
                }
            )->firstOrFail();
        } catch (\Exception $exception) {
            $message = "$param is not a valid event URL or identifier.";
            return view('v1.public_pages.error_display', compact('message'));
        }

        if (auth()->guest()) {
            $current_person = 0;
        } else {
            $this->current_person = $this->currentPerson;
        }
        $currentOrg = $this->currentOrg;

        //$referrer = Referer::get();
        $referrer = app(Referer::class)->get();

        if ($referrer) {
            $r = new ReferLink;
            $r->objectType = 'eventID';
            $r->objectID = $this->event->eventID;
            $r->referrerText = $referrer;
            $r->save();
        }

        $this->event_loc = Location::where('locID', $this->event->locationID)->first();
        $this->orgLogoPath = Org::where('orgID', $this->event->orgID)->select('orgPath', 'orgLogo')->first();
        $this->bundles =
            Ticket::where([
                ['isaBundle', 1],
                ['eventID', $this->event->eventID],
            ])->get()->sortByDesc('availableEndDate');

        $this->tickets =
            Ticket::where([
                ['isaBundle', 0],
                ['eventID', $this->event->eventID],
            ])->get()->sortByDesc('availableEndDate');

        return view('v1.public_pages.display_tickets_only', $this->data);
    }

    public function ics_listing($orgID, $etID = null, $override = null): Response
    {
        $events = Event::where([
            ['orgID', '=', $orgID],
            ['eventStartDate', '>=', Carbon::now()],
            ['isActive', '=', 1],
        ])->get();
        $ical = new ics_cal_full($events);
        $output = $ical->open();
        $output .= $ical->get();
        $output .= $ical->close();

        return response($output)
            ->header('Content-type', 'text/calendar')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('Pragma', 'no-cache');
    }

    /*
     * get_tix: AJAX - returns the tickets for an event
     */
    public function get_tix(Event $event, ?Ticket $ticket = null)
    {
        $tix = Ticket::where([
            ['eventID', '=', $event->eventID],
            ['isSuppressed', '=', 0],
        ])
            ->where(fn($q) => $q->where('maxAttendees', '=', 0)->orWhereRaw('maxAttendees - regCount > 0'))
            ->get();

        return json_encode(['status' => 'success', 'tix' => $tix, 'def_tick' => $ticket]);
    }
}
