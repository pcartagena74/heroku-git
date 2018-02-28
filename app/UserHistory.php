<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model
{
    protected $dates = ['changeDate', 'createDate', 'last_login', 'updateDate'];

    protected $table = 'users_history';
    protected $primaryKey = 'revision';

    public function person()
    {
        return $this->hasOne(Person::class, 'personID');
    }
}
