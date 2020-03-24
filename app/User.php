<?php

namespace App;

use App\Traits\EntrustUserTraitOver;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable implements \Illuminate\Contracts\Auth\CanResetPassword
{
    use Billable;
    use Notifiable;
    use ValidatesRequests;
    use EntrustUserTraitOver;

    protected $table = 'users';
    protected $dates = ['createDate', 'updateDate', 'trial_ends_at', 'subscription_ends_at', 'last_login'];

    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'email', 'name', 'login', 'password', 'ticketit_agent',
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

    public function person()
    {
        return $this->hasOne(Person::class, 'personID');
    }

    public function roles()
    {
        // we need to get default person org id so running another query to fetch same
        $person = Person::find(auth()->user()->id);
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')->where('role_user.orgId', $person->defaultOrgID);
    }
}
