<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Speaker extends Model
{
    protected $table = 'speaker';

    protected $primaryKey = 'id';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
    ];

    public function eventsessions()
    {
        return $this->belongsToMany(EventSession::class, 'eventsession_speaker', 'speaker_id', 'eventsession_id');
    }

    public function person()
    {
        return $this->hasOne(Person::class, 'personID', 'id');
    }
}
