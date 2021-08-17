<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonSocialite extends Model
{
    use SoftDeletes;

    // The table
    protected $table = 'person-socialite';
    protected $primaryKey = 'id';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $dates = ['createDate', 'updateDate', 'deleted_at'];

    public function person()
    {
        return $this->belongsTo(Person::class, 'personID', 'id');
    }
}
