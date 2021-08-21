<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

//use Spatie\Activitylog\Traits\LogsActivity;

class OrgPerson extends Model
{
    // use LogsActivity;
    // use SoftDeletes;

    // The table
    protected $table = 'org-person';

    protected $primaryKey = 'id';

    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';

    public $incrementing = true;
    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
        'RelDate1' => 'datetime',
        'RelDate2' => 'datetime',
        'RelDate3' => 'datetime',
        'RelDate4' => 'datetime',
        'RelDate5' => 'datetime',
        'RelDate6' => 'datetime',
        'RelDate7' => 'datetime',
        'RelDate8' => 'datetime',
        'RelDate9' => 'datetime',
        'RelDate10' => 'datetime',
    ];

    protected static $logAttributes = ['OrgStat1', 'OrgStat2', 'RelDate1', 'RelDate2', 'RelDate3', 'RelDate4'];
    protected static $ignoreChangedAttributes = ['createDate'];

    public function myperson()
    {
        return $this->belongsTo(Person::class, 'personID', 'personID');
    }

    public function myorg()
    {
        return $this->belongsTo(Org::class, 'orgID', 'orgID');
    }
}
