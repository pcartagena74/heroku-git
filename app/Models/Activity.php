<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    // The table
    protected $table = 'person-activity';

    protected $primaryKey = 'activityID';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'personID');
    }

    public function org()
    {
        return $this->belongsTo(Org::class, 'orgID');
    }
}
