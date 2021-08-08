<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model
{
    protected $table = 'users_history';
    protected $primaryKey = 'revision';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';

    protected $dates = ['changeDate', 'createDate', 'last_login', 'updateDate'];

    public function person()
    {
        return $this->hasOne(Person::class, 'personID');
    }
}
