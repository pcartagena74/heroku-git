<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
//use Spatie\Activitylog\Traits\LogsActivity;
use App\EventSession;
use App\Person;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Event extends Model
{
    //use LogsActivity;
    use SoftDeletes;

    // The table
    protected $table = 'org-event';
    protected $primaryKey = 'eventID';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['createDate', 'updateDate', 'eventStartDate', 'eventEndDate', 'deleted_at', 'earlyBirdDate'];

    protected $casts = [
        'eventID' => 'integer',
    ];

    protected static $logAttributes = ['eventName', 'eventDescription', 'locationID', 'isActive', 'hasFood', 'slug', 'hasTracks'];
    protected static $ignoreChangedAttributes = ['createDate'];

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

    public function ok_to_display()
    {
        /*
        $today = Carbon::now();
        $max = Ticket::select('availabilityEndDate')
            ->where('eventID', $this->eventID)
            ->orderBy('availabilityEndDate', 'desc')
            ->first();
        //if($this->isActive && $max->availabilityEndDate->gte($today)){
        */
        if ($this->isActive) {
            return 1;
        } else {
            return 0;
        }
    }

    public function checkin_time()
    {
        $today = Carbon::now();
        //dd($this->eventStartDate->diffInDays($today));
        if (($this->eventStartDate->diffInDays($today) <= 2
                && $this->eventStartDate->diffInDays($today) >= 0)
            || $today->gte($this->eventEndDate)) {
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
            ['eventID', $this->eventID]
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

            // DB::listen(function ($sql){var_dump($sql->sql, $sql->bindings);});
            // DB::enableQueryLog();

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
            // dd(DB::getQueryLog());

        return $out;
    }
}
