<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    // The table
    protected $table = 'org-event_types';
    protected $primaryKey = 'etID';
    protected $dates = ['createDate', 'updateDate'];
}
