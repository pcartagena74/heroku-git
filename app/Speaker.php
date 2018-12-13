<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Speaker extends Model
{
    protected $table = 'speaker';
    protected $primaryKey = 'id';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['createDate', 'updateDate'];

    public function eventsessions()
    {
        return $this->belongsToMany(EventSession::class, 'eventsession_speaker', 'speaker_id', 'eventsession_id');
    }
}
