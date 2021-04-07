<?php

namespace App;

class Activity extends Model
{
    // The table
    protected $table = 'person-activity';
    protected $primaryKey = 'activityID';
    protected $dates = ['createDate', 'updateDate'];

    public function person()
    {
        return $this->belongsTo(Person::class, 'personID');
    }

    public function org()
    {
        return $this->belongsTo(Org::class, 'orgID');
    }
}
