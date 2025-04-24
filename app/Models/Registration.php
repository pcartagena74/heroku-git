<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    protected $guarded = [];

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

    public function regsession(): HasOne
    {
        return $this->hasOne(RegSession::class, 'regID', 'regID');
    }

    public function checkin($sessionID = null): void
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
        ])->firstOrNew([
                'regID' => $this->regID,
                'eventID' => $this->eventID,
                'confDay' => $es->confDay,
                'sessionID' => $sessionID,
                'personID' => $this->personID,
            ]
        );

        try {
            $rs->hasAttended = 1;
            $rs->save();
        } catch (\Exception $e) {
            // if the above doesn't create and set attendance, something big is wrong.
        }
    }

    public function is_session_attended($sessionID): RegSession
    {
        return RegSession::where([
            ['regID', $this->regID],
            ['sessionID', $sessionID],
            ['hasAttended', 1],
        ])->first();
    }
}
