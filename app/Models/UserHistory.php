<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserHistory extends Model
{
    protected $table = 'users_history';

    protected $primaryKey = 'revision';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'updateDate';

    protected function casts(): array
    {
        return [
            'changeDate' => 'datetime',
            'createDate' => 'datetime',
            'last_login' => 'datetime',
            'updateDate' => 'datetime',
        ];
    }

    public function person(): HasOne
    {
        return $this->hasOne(Person::class, 'personID');
    }
}
