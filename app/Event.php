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

    public function tickets() {
        return $this->hasMany(Ticket::class, 'ticketID');
    }

    public function bundles() {
        return $this->hasMany(Bundle::class, 'ticketID');
    }

    public function registrations() {
        return $this->hasMany(Registration::class, 'ticketID');
    }

    public function regfinances() {
        return $this->hasMany(RegFinance::class, 'ticketID');
    }

    public function org() {
        return $this->belongsTo(Org::class, 'orgID');
    }
}
