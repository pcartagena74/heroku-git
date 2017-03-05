<?php

namespace App;


class RegFinance extends Model
{
    // The table
    protected $table = 'reg-finance';
    protected $primaryKey = 'regID';
    protected $dates = ['createDate', 'cancelDate'];

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
