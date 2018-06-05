<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Event extends Model
{
    use LogsActivity;
    use SoftDeletes;

    // The table
    protected $table = 'org-event';
    protected $primaryKey = 'eventID';
    protected $dates = ['createDate', 'updateDate', 'eventStartDate', 'eventEndDate', 'deleted_at', 'earlyBirdDate'];

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
            ->whereDate('eventStartDate', '<', \Carbon\Carbon::now())
            ->select('eventID')
            ->get();
    }

    public function valid_earlyBird() {
        $today = \Carbon\Carbon::now();
        if($this->earlyBirdDate !== null && $this->earlyBirdDate->gte($today)){
            return 1;
        } else {
            return 0;
        }
    }
}
