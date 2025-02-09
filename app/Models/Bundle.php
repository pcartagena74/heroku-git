<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Bundle extends Model
{
    use LogsActivity;

    // The table
    protected $table = 'bundle-ticket';

    protected $primaryKey = '';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
    ];

    protected static $logOnlyDirty = true;

    protected static $submitEmptyLogs = false;

    protected static $logAttributes = ['ticketID', 'bundleID'];

    protected static $ignoreChangedAttributes = ['createDate'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'eventID', 'ticketID');
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class, 'ticketID', 'ticketID');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
