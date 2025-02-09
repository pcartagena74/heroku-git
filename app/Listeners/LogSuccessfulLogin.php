<?php

namespace App\Listeners;

use App\Models\Person;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;

//use Spatie\Activitylog\Traits\LogsActivity;

class LogSuccessfulLogin
{
    public $user;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $event->user->last_login = date('Y-m-d H:i:s');
        $login = $event->user->email;
        // activity('lastLogin')->performedOn($this->user)->log("Login of $login @ " . Carbon::now());
        $p = Person::find($event->user->id);
        $p->lastLoginDate = Carbon::now();
        $p->save();
    }
}
