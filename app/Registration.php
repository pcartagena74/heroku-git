<?php

namespace App;


class Registration extends Model
{
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
