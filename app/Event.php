<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
//use Spatie\Activitylog\Traits\LogsActivity;
use App\EventSession;
use App\Person;
use Carbon\Carbon;

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
        return EventSession::where('eventID', '=', $this->eventID)
            ->where(function ($q) {
                $q->orWhere('sessionName', '=', 'def_sess');
                $q->orWhere('trackID', '=', 0);
            })->first();
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
}
