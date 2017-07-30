<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonSocialite extends Model
{
    use SoftDeletes;

    // The table
    protected $table = 'person-socialite';
    protected $primaryKey = 'id';
    protected $dates = ['createDate', 'updateDate', 'deleted_at'];

    public function person () {
        return $this->belongsTo(Person::class, 'personID', 'id');
    }

}
