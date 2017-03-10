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
        'email', 'login', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Provides the route for email/notifications to the user
     * @return the email address/login for the user
     */
    public function routeNotificationForMail()
    {
        return $this->email;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne relationship with Person
     *
     */

    public function person() {
        return $this->hasOne(Person::class, 'personID');
    }
}
