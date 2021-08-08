<?php

namespace App\Listeners;

use App\Models\User;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
//use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
