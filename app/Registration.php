<?php

namespace App;

use Spatie\Activitylog\Traits\LogsActivity;

class Registration extends Model
{
    use LogsActivity;
    protected static $logAttributes = ['regStatus'];
    protected static $ignoreChangedAttributes = ['createDate'];

    // The table
    protected $table = 'event-registration';
    protected $primaryKey = 'regID';
    protected $dates = ['createDate', 'updateDate'];

    public function event() {
        return $this->belongsTo(Event::class, 'eventID');
    }

    public function person() {
        return $this->belongsTo(Person::class, 'personID');
    }

    public function ticket() {
        return $this->belongsTo(Ticket::class, 'ticketID');
    }

    public function regfinance() {
        return $this->hasOne(RegFinance::class, 'regID', 'regID');
    }
}
