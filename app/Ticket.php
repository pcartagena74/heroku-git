<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Ticket extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected static $logAttributes = ['earlyBirdEndDate', 'memberBasePrice', 'nonmbrBasePrice', 'maxAttendees',
        'isaBundle', 'ticketLabel'];
    protected static $ignoreChangedAttributes = ['createDate'];
    // The table
    protected $table = 'event-tickets';
    protected $primaryKey = 'ticketID';
    protected $dates = ['availabilityEndDate', 'earlyBirdEndDate', 'createDate', 'updateDate', 'deleted_at'];

    public function bundle() {
        return $this->belongsTo(Bundle::class, 'ticketID');
    }

    public function event() {
        return $this->belongsTo(Event::class, 'eventID');
    }

    public function registrations() {
        return $this->hasMany(Registration::class, 'ticketID');
    }

    public function regfinances() {
        return $this->hasMany(RegFinance::class, 'ticketID');
    }
}
