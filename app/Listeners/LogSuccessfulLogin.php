<?php

namespace App\Listeners;

use App\Person;
use Illuminate\Auth\Events\Login;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
//use Spatie\Activitylog\Traits\LogsActivity;
use Carbon\Carbon;
use App\User;

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
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $event->user->last_login = date('Y-m-d H:i:s');
        $login = $event->user->email;
        // activity('lastLogin')->performedOn($this->user)->log("Login of $login @ " . Carbon::now());
        $p = Person::find($event->user->id);
        $p->lastLoginDate = Carbon::now();
        $p->save();
    }
}
