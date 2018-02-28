<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Track extends Model
{
    use SoftDeletes;

    protected $table = 'event-tracks';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $primaryKey = 'trackID';
    protected $dates = ['createDate', 'updateDate', 'deleted_at'];

    public function event()
    {
        return $this->belongsTo(Event::class, 'eventID');
    }

    //public function sessions() {
    //    return $this->hasMany(EventSession::class, 'sessionID');
    //}
}
