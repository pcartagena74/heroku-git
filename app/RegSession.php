<?php

namespace App;

// use Spatie\Activitylog\Traits\LogsActivity;

class RegSession extends Model
{
    // use LogsActivity;
    // The table
    protected $table = 'reg-session';
    protected $primaryKey = 'id';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['createDate', 'updateDate'];

    //protected static $logAttributes = ['confirmation', 'pmtRecd', 'status', 'cost'];
    protected static $ignoreChangedAttributes = ['createDate'];

    public function event() {
        return $this->belongsTo(Event::class, 'eventID', 'eventID');
    }

    public function session() {
        return $this->belongsTo(EventSession::class, 'sessionID', 'sessionID');
    }

    public function registration() {
        return $this->hasOne(Registration::class, 'regID', 'regID');
    }
}
