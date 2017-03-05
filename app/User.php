<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Validation\Concerns\ValidatesAttributes;

class User extends Authenticatable implements \Illuminate\Contracts\Auth\CanResetPassword
{
    use Notifiable;
    use ValidatesRequests;

    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'login', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function person() {
        return $this->hasOne(Person::class, 'personID');
    }
}
