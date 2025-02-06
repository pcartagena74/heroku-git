<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
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

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'eventID', 'eventID');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'personID', 'personID');
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class, 'ticketID', 'ticketID');
    }

    public function regfinance(): BelongsTo
    {
        return $this->belongsTo(RegFinance::class, 'rfID', 'regID');
    }

    public function regsessions(): HasMany
    {
        return $this->hasMany(RegSession::class, 'regID', 'regID');
    }

    public function checkin($sessionID = null)
    {
        if ($sessionID === null) {
            $sessionID = $this->event->default_session()->sessionID;
        }

        $es = EventSession::find($sessionID);
        $rs = RegSession::where([
            ['eventID', '=', $this->eventID],
            ['regID', '=', $this->regID],
            ['sessionID', '=', $sessionID],
            ['personID', '=', $this->personID],
        ])->first();

        try {
            $rs->hasAttended = 1;
            $rs->save();
        } catch (\Exception $e) {
            $rs = new RegSession;
            $event = $this->event()->first();
            $rs->regID = $this->regID;
            $rs->eventID = $event->eventID;
            $rs->confDay = $es->confDay;
            $rs->sessionID = $sessionID;
            $rs->personID = $this->personID;
            $rs->hasAttended = 1;
            $rs->save();
        }
    }

    public function is_session_attended($sessionID)
    {
        return RegSession::where([
            ['regID', $this->regID],
            ['sessionID', $sessionID],
            ['hasAttended', 1],
        ])->first();
    }
}
