<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OPHistory extends Model
{
    use SoftDeletes;
    protected $primaryKey = 'revision';

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
        'changeDate'
    ];

    // The table
    protected $table = 'org-person_history';

    public function myperson()
    {
        return $this->belongsTo(Person::class, 'orgID');
    }

    public function myorg()
    {
        return $this->belongsTo(Org::class, 'orgID');
    }

    public function people()
    {
        return $this->hasMany(Person::class, 'personID');
    }

    public function orgs()
    {
        return $this->hasMany(Org::class, 'orgID');
    }
}
