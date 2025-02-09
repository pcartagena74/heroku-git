<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'eventID', 'eventID');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(EventSession::class, 'sessionID', 'sessionID');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'regID', 'regID');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'personID', 'personID');
    }
}
