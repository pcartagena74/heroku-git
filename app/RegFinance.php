<?php

namespace App;

//use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegFinance extends Model
{
    use SoftDeletes;
    //use LogsActivity;
    // The table
    protected $table = 'reg-finance';
    protected $primaryKey = 'regID';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'cancelDate';
    protected $dates = ['createDate', 'cancelDate', 'deleted_at'];

    //protected static $logAttributes = ['confirmation', 'pmtRecd', 'status', 'cost'];
    //protected static $ignoreChangedAttributes = ['createDate', 'cancelDate'];

    public function event()
    {
        return $this->belongsTo(Event::class, 'eventID', 'eventID');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'personID', 'personID');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'rfID', 'regID');
    }

}
