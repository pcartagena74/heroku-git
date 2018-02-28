<?php

namespace App;

use Spatie\Activitylog\Traits\LogsActivity;

class Bundle extends Model
{
    use LogsActivity;

    // The table
    protected $table = 'bundle-ticket';
    protected $primaryKey = '';
    protected $dates = ['createDate', 'updateDate'];

    protected static $logAttributes = ['ticketID', 'bundleID'];
    protected static $ignoreChangedAttributes = ['createDate'];

    public function event()
    {
        return $this->belongsTo(Event::class, 'eventID', 'ticketID');
    }

    public function ticket()
    {
        return $this->hasOne(Ticket::class, 'ticketID', 'ticketID');
    }
}
