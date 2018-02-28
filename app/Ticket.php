<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\DB;
use App\Bundle;

class Ticket extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected static $logAttributes = ['earlyBirdEndDate', 'memberBasePrice', 'nonmbrBasePrice', 'maxAttendees',
        'isaBundle', 'ticketLabel'];
    protected static $ignoreChangedAttributes = ['createDate'];
    // The table
    protected $table = 'event-tickets';
    protected $primaryKey = 'ticketID';
    protected $dates = ['availabilityEndDate', 'earlyBirdEndDate', 'createDate', 'updateDate', 'deleted_at'];

    public function bundle()
    {
        return $this->belongsTo(Bundle::class, 'ticketID');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'eventID');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'ticketID');
    }

    public function regfinances()
    {
        return $this->hasMany(RegFinance::class, 'ticketID');
    }

    public function waitlisting()
    {
        if ($this->isaBundle) {
            $members = Bundle::with('ticket')->where('bundleID', $this->ticketID)->get();
            foreach ($members as $m) {
                if ($m->ticket->maxAttendees > 0 && $m->ticket->regCount > $m->ticket->maxAttendees) {
                    return 1;
                }
            }
            return 0;
        } else {
            if ($this->maxAttendees > 0 && $this->regCount > $this->maxAttendees) {
                return 1;
            } else {
                return 0;
            }
        }
    }
}
