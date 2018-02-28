<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventSession extends Model
{
    use SoftDeletes;

    protected $table = 'event-sessions';
    protected $primaryKey = 'sessionID';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['start', 'end', 'createDate', 'updateDate', 'deleted_at'];

    public function track()
    {
        return $this->belongsTo(Track::class, 'trackID');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'eventID');
    }
}
