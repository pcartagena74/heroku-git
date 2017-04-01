<?php

namespace App;

use Spatie\Activitylog\Traits\LogsActivity;

class RegFinance extends Model
{
    use LogsActivity;
    // The table
    protected $table = 'reg-finance';
    protected $primaryKey = 'regID';
    const UPDATED_AT = 'cancelDate';
    protected $dates = ['createDate', 'cancelDate'];

    protected static $logAttributes = ['confirmation', 'pmtRecd', 'status', 'cost'];
    protected static $ignoreChangedAttributes = ['createDate'];

    public function event() {
        return $this->belongsTo(Event::class, 'eventID');
    }

    public function person() {
        return $this->belongsTo(Person::class, 'personID');
    }

    public function ticket() {
        return $this->belongsTo(Ticket::class, 'ticketID');
    }

    public function registration() {
        return $this->hasOne(Registration::class, 'regID', 'regID');
    }
}
