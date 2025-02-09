<?php

namespace App\Models;

use App\Other\ics_calendar;
use Carbon\Carbon;
use DateTimeInterface;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Event extends Model
{
    use LogsActivity;
    use SoftDeletes;

    // The table
    protected $table = 'org-event';

    protected $primaryKey = 'eventID';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
        'eventStartDate' => 'datetime',
        'eventEndDate' => 'datetime',
        'earlyBirdDate' => 'datetime',
        'eventID' => 'integer',
    ];

    protected $fillable = ['eventName', 'eventDescription', 'eventStartDate', 'eventEndDate', 'eventTimeZone', 'eventTypeID', 'slug', 'locationID'];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected static $logAttributes = ['eventName', 'locationID', 'isActive', 'slug', 'hasTracks',
        'eventStartDate', 'eventEndDate'];

    protected static $ignoreChangedAttributes = ['createDate', 'updateDate'];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function category()
    {
        return $this->hasOne(Category::class, 'catID', 'catID');
    }

    public function location()
    {
        return $this->hasOne(Location::class, 'locID', 'locationID');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'eventID', 'eventID');
    }

    public function event_type()
    {
        return $this->belongsTo(EventType::class, 'eventTypeID', 'etID');
    }

    public function bundles()
    {
        return $this->hasMany(Bundle::class, 'eventID', 'eventID');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'eventID', 'eventID');
    }

    public function regfinances()
    {
        return $this->hasMany(RegFinance::class, 'eventID', 'eventID');
    }

    public function org()
    {
        return $this->belongsTo(Org::class, 'orgID', 'orgID');
    }

    public function sessions()
    {
        return $this->hasMany(EventSession::class, 'eventID', 'eventID');
    }

    public function regsessions()
    {
        return $this->hasMany(RegSession::class, 'eventID', 'eventID');
    }

    public function main_session()
    {
        return $this->hasOne(EventSession::class, 'sessionID', 'mainSession');
    }

    public function surveys()
    {
        return $this->hasManyThrough(RSSurvey::class, EventSession::class, 'eventID', 'sessionID', 'eventID', 'sessionID');
    }

    public static function events_this_year()
    {
        return static::whereYear('eventStartDate', '=', date('Y'))
            ->whereDate('eventStartDate', '<', Carbon::now())
            ->select('eventID')
            ->get();
    }

    public function valid_earlyBird()
    {
        $today = Carbon::now();
        if ($this->earlyBirdDate !== null && $this->earlyBirdDate->gte($today)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function ok_to_display(): int
    {
        if ($this->isActive) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * checkin_time returns true when within 1 day of start or 2 days after the end of the event
     *
     * @return bool
     */
    public function checkin_time(): bool|int
    {
        $today = Carbon::now();
        if (($this->eventStartDate->diffInDays($today) <= 1
                && $this->eventStartDate->diffInDays($today) >= 0) ||
            ($today->gt($this->eventStartDate) && $today->lt($this->eventEndDate))
            || $today->diffInDays($this->eventEndDate) <= 2) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * checkin_period returns true when within 1 day of start or within $postEventEndDays after the end of the event
     *
     * @return bool
     */
    public function checkin_period(): bool|int
    {
        $today = Carbon::now();
        if ($this->orgID > 0) {
            $org = Org::find($this->orgID);
        } else {
            $e = self::find($this->eventID);
            $org = Org::find($e->orgID);
        }

        if (($this->eventStartDate->diffInDays($today) <= 1
                && $this->eventStartDate->diffInDays($today) >= 0) ||
            ($today->gt($this->eventStartDate) && $today->lt($this->eventEndDate)) ||
            $today->diffInDays($this->eventEndDate) <= $org->postEventEditDays) {
            return 1;
        } else {
            return 0;
        }
    }

    public function regCount()
    {
        return Registration::where('eventID', '=', $this->eventID)->count();
    }

    public function default_session()
    {
        // Kept to maintain legacy usage
        return EventSession::where('eventID', '=', $this->eventID)
            ->where(function ($q) {
                $q->orWhere('sessionName', '=', 'def_sess');
                $q->orWhere('trackID', '=', 0);
            })->first();
    }

    public function default_sessions()
    {
        return EventSession::where('eventID', '=', $this->eventID)
            ->where(function ($q) {
                $q->orWhere('sessionName', '=', 'def_sess');
                $q->orWhere('trackID', '=', 0);
            })->get();
    }

    public function registered_speakers()
    {
        return Person::whereHas('registrations', function ($q) {
            $q->where('eventID', '=', $this->eventID);
        })
            ->whereHas('roles', function ($q) {
                $q->where('id', '=', 2);
            })
            ->get();
    }

    public function main_reg_sessions()
    {
        return RegSession::where([
            ['sessionID', $this->mainSession],
            ['eventID', $this->eventID],
        ])->get();
    }

    public function week_sales()
    {
        $count = 0;

        foreach ($this->tickets as $t) {
            $count += $t->week_sales();
        }

        return $count;
    }

    public function registrants($sessionID)
    {
        // Get the possible registrants for this event's session
        // - people pre-registered (regsession) for this session's ID
        // - people with a relevant ticket for this EventSession, NOT pre-registered
        //   for a session at the same day/time as this one
        try {
            $es = EventSession::findOrFail($sessionID);
        } catch (\Exception $e) {
            return redirect()->back();
        }

        $ticketIDs = $es->ticket->bundle_parent_array();

        $out = Registration::whereIn('event-registration.ticketID', $ticketIDs)
            ->select('p.personID', 'p.firstName', 'p.prefName', 'p.lastName', 'op.OrgStat1', 'rs.hasAttended', 'event-registration.regID')
            //->with('ticket', 'event', 'person.orgperson', 'regsessions', 'person')
            ->where('event-registration.eventID', '=', $es->eventID)
            ->join('person as p', 'p.personID', '=', 'event-registration.personID')
            ->join('org-person as op', 'p.defaultOrgPersonID', '=', 'op.id')
            ->join('event-sessions as es', 'es.eventID', '=', 'event-registration.eventID')
            ->leftJoin('reg-session as rs', function ($q) use ($es) {
                $q->on('rs.regID', '=', 'event-registration.regID');
                $q->on('rs.personID', '=', 'p.personID');
                $q->on('rs.sessionID', '=', 'es.sessionID')->where('es.sessionID', '=', $es->sessionID);
            })
            ->whereIn('es.sessionID', [$es->sessionID, null])
            ->distinct()
            ->orderBy('p.lastName')
            ->get();

        return $out;
    }

    public function create_or_update_event_ics(): void
    {
        // Make the event_{id}.ics file if it doesn't exist
        $event_filename = 'event_'.$this->eventID.'.ics';
        $ical = new ics_calendar($this);
        $contents = $ical->get();
        \Storage::disk('events')->put($event_filename, $contents, 'public');
    }

    public function event_ics_url()
    {
        $ics_filename = "event_$this->eventID.ics";

        try {
            if (Storage::disk('events')->exists($ics_filename)) {
                $ics_file = Storage::disk('events')->url($ics_filename);
            }
        } catch (Exception $e) {
            $ics_file = '#';
        }

        return $ics_file;
    }

    public function event_url(): string
    {
        if ($this->slug) {
            return '/events/'.$this->slug;
        } else {
            return '/events/'.$this->eventID;
        }
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
