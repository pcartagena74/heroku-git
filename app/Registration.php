<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\RegSession;

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
        return $this->hasOne(RegFinance::class, 'regID', 'rfID');
    }

    public function regsessions()
    {
        return $this->hasOne(RegSession::class, 'regID', 'regID');
    }

    public function checkin(){
        $rs = new RegSession;
        $e = $this->event()->first();
        $rs->regID = $this->regID;
        $rs->eventID = $e->eventID;
        $rs->sessionID = $e->default_session()->sessionID;
        $rs->personID = $this->personID;
        $rs->hasAttended = 1;
        $rs->save();
    }

}
