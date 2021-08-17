<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventType extends Model
{
    use SoftDeletes;
    // The table
    protected $table = 'org-event_types';
    protected $primaryKey = 'etID';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['createDate', 'updateDate'];

    /*
     * Commented out because this doesn't make sense
    public function event()
    {
        return $this->belongsTo(Event::class, 'eventTypeID', 'eventID');
    }
    */
}
