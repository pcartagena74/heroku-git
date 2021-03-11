<?php

namespace App;

//use Spatie\Activitylog\Traits\LogsActivity;

class RSSurvey extends Model
{
    //use LogsActivity;
    // The table
    protected $table = 'rs-survey';
    protected $primaryKey = 'id';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['createDate', 'updateDate'];

    //protected static $logAttributes = ['confirmation', 'pmtRecd', 'status', 'cost'];
    //protected static $ignoreChangedAttributes = ['createDate'];

    public function person()
    {
        return $this->belongsTo(Person::class, 'personID', 'personID');
    }

    public function session()
    {
        return $this->belongsTo(EventSession::class, 'sessionID', 'sessionID');
    }

    public function registration()
    {
        return $this->belongsTo(Registration::class, 'regID', 'regID');
    }

}
