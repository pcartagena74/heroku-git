<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegSession extends Model
{
    // use LogsActivity;
    // The table
    protected $table = 'reg-session';

    protected $primaryKey = 'id';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
    ];

    //protected static $logAttributes = ['confirmation', 'pmtRecd', 'status', 'cost'];
    protected static $ignoreChangedAttributes = ['createDate'];

    public function event()
    {
        return $this->belongsTo(Event::class, 'eventID', 'eventID');
    }

    public function session()
    {
        return $this->belongsTo(EventSession::class, 'sessionID', 'sessionID');
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class, 'regID', 'regID');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'personID', 'personID');
    }
}
