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
    protected $dates = [
        'createDate',
        'updateDate',
        'deleted_at',
        'RelDate1',
        'RelDate2',
        'RelDate3',
        'RelDate4',
        'RelDate5',
        'RelDate6',
        'RelDate7',
        'RelDate8',
        'RelDate9',
        'RelDate10',
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
