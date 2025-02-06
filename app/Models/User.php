<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\EntrustUserTraitOver;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable implements \Illuminate\Contracts\Auth\CanResetPassword
{
    use Billable;
    use EntrustUserTraitOver;
    use Notifiable;
    use ValidatesRequests;

    protected $table = 'users';

    protected $casts = [
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'last_login' => 'datetime',
    ];

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
     *
     * @return the email address/login for the user
     */
    public function routeNotificationForMail()
    {
        return $this->email;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne relationship with Person
     */
    public function person(): HasOne
    {
        return $this->hasOne(Person::class, 'personID', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany relationship with Roles
     */
    public function roles(): BelongsToMany
    {
        // we need to get default person org id so running another query to fetch same
        $person = Person::find(auth()->user()->id);

        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')->where('role_user.orgID', $person->defaultOrgID);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(\Kordy\Ticketit\Models\Ticket::class, 'user_id', 'id');
    }
}
