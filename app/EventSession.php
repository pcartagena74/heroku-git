<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventSession extends Model
{
    use SoftDeletes;

    protected $table = 'event-sessions';
    protected $primaryKey = 'sessionID';
    protected $dates = ['createDate', 'updateDate', 'deleted_at'];

    public function track() {
        return $this->belongsTo(Track::class, 'trackID');
    }

    public function event() {
        return $this->belongsTo(Event::class, 'eventID');
    }
}
