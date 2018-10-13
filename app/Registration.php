<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;

//use Spatie\Activitylog\Traits\LogsActivity;

class Registration extends Model
{
    //use LogsActivity;
    //protected static $logAttributes = ['regStatus'];
    //protected static $ignoreChangedAttributes = ['createDate'];

    use SoftDeletes;
    // The table
    protected $table = 'event-registration';
    protected $primaryKey = 'regID';
    protected $dates = ['createDate', 'updateDate', 'deleted_at'];

    public function event()
    {
        return $this->belongsTo(Event::class, 'eventID', 'eventID');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'personID', 'personID');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticketID', 'ticketID');
    }

    public function regfinance()
    {
        return $this->hasOne(RegFinance::class, 'regID', 'regID');
    }

    public function regsession()
    {
        return $this->hasMany(RegSession::class, 'regID', 'regID');
    }
}
