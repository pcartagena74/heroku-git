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
    const UPDATED_AT = 'cancelDate';
    protected $dates = ['createDate', 'cancelDate', 'deleted_at'];

    //protected static $logAttributes = ['confirmation', 'pmtRecd', 'status', 'cost'];
    //protected static $ignoreChangedAttributes = ['createDate', 'cancelDate'];

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
        return $this->hasMany(Registration::class, 'token', 'token');
    }
}
