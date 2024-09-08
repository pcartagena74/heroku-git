<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model
{
    protected $table = 'users_history';

    protected $primaryKey = 'revision';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected $casts = [
        'changeDate' => 'datetime',
        'createDate' => 'datetime',
        'last_login' => 'datetime',
        'updateDate' => 'datetime',
    ];

    public function person()
    {
        return $this->hasOne(Person::class, 'personID');
    }
}
