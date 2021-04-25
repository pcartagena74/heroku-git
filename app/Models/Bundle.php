<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Bundle extends Model
{
    use LogsActivity;

    // The table
    protected $table = 'bundle-ticket';
    protected $primaryKey = '';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['createDate', 'updateDate'];

    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    protected static $logAttributes = ['ticketID', 'bundleID'];
    protected static $ignoreChangedAttributes = ['createDate'];

    public function event()
    {
        return $this->belongsTo(Event::class, 'eventID', 'ticketID');
    }

    public function ticket()
    {
        return $this->hasOne(Ticket::class, 'ticketID', 'ticketID');
    }
}
