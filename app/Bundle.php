<?php

namespace App;


class Bundle extends Model
{
    // The table
    protected $table = 'bundle-ticket';
    protected $primaryKey = '';
    protected $dates = ['createDate', 'updateDate'];

    public function tickets() {
        return $this->hasMany(Ticket::class, 'ticketID');
    }

    public function event() {
        return $this->belongsTo(Event::class, 'eventID', 'ticketID');
    }

    public function ticket() {
        return $this->belongsTo(Ticket::class, 'ticketID');
    }
}
