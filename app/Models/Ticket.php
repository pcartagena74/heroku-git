<?php

namespace App\Models;

use App\Models\Bundle;
use App\Models\EventSession;
use Carbon\Carbon;
//use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Ticket extends Model
{
    use SoftDeletes;
    //use LogsActivity;

    protected static $logAttributes = ['earlyBirdEndDate', 'memberBasePrice', 'nonmbrBasePrice', 'maxAttendees',
        'isaBundle', 'ticketLabel', ];
    protected static $ignoreChangedAttributes = ['createDate'];
    // The table
    protected $table = 'event-tickets';
    protected $primaryKey = 'ticketID';
    const CREATED_AT = 'createDate';
    const UPDATED_AT = 'updateDate';
    protected $casts = [
        'availabilityEndDate' => 'datetime',
        'earlyBirdEndDate' => 'datetime',
        'createDate' => 'datetime',
        'updateDate' => 'datetime',
    ];

    protected $appends = ['eb_mbr_price', 'eb_non_price'];

    public function getEbMbrPriceAttribute()
    {
        if ($this->valid_earlyBird()) {
            return $this->memberBasePrice - ($this->memberBasePrice * $this->earlyBirdPercent / 100);
        } else {
            return $this->memberBasePrice;
        }
    }

    public function getEbNonPriceAttribute()
    {
        if ($this->valid_earlyBird()) {
            return $this->nonmbrBasePrice - ($this->nonmbrBasePrice * $this->earlyBirdPercent / 100);
        } else {
            return $this->nonmbrBasePrice;
        }
    }

    public function bundle()
    {
        return $this->belongsTo(Bundle::class, 'ticketID', 'ticketID', self::class);
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

    /**
     * week_sales() returns count of sales for this ticket's sales based on this ticket's (or its members') sales
     */
    public function week_sales()
    {
        $tickets = [$this->ticketID];
        if (! $this->isaBundle) {
            $bundles = Bundle::where('ticketID', $this->ticketID)->select('bundleID')->get();
            foreach ($bundles as $b) {
                array_push($tickets, $b->bundleID);
            }
        }

        return Registration::whereDate('updateDate', '>=', Carbon::now()->subWeek(1))
            ->whereIn('ticketID', $tickets)
            ->whereNull('deleted_at')
            ->where('regStatus', '=', 'Processed')
            ->count();
    }

    /**
     * waitlisting() returns true/false based on ticket's maxAttendee limit and tickets sold
     */
    public function waitlisting()
    {
        // For bundles, if any component ticket is waitlisting, the entire bundle is waitlisting.
        if ($this->isaBundle) {
            $members = Bundle::with('ticket')->where('bundleID', $this->ticketID)->get();
            foreach ($members as $m) {
                if ($m->ticket->maxAttendees > 0 && $m->ticket->regCount > $m->ticket->maxAttendees) {
                    return 1;
                }
            }

            return 0;
        } else {
            if ($this->maxAttendees > 0 && $this->regCount >= $this->maxAttendees) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    /**
     * valid_earlyBird() returns true/false based on current date and ticket's earlyBirdEndDate
     */
    public function valid_earlyBird()
    {
        $today = \Carbon\Carbon::now();
        if ($this->earlyBirdEndDate !== null && $this->earlyBirdEndDate->gte($today) && $this->earlyBirdPercent > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * bundle_members() returns the members of a bundle ticket
     */
    public function bundle_members()
    {
        return self::join('bundle-ticket as bt', 'bt.ticketID', 'event-tickets.ticketID')
            ->where([
                ['bt.bundleID', '=', $this->ticketID],
                ['event-tickets.eventID', '=', $this->eventID],
            ])
            ->get();
    }

    /**
     * update_count Increment / Decrement Function for Ticket Counts
     *              count is updated for bundle member tickets OR the ticket itself
     * @param:  $amt is the amount passed to update the count - allows for +1 and -1
     * @param:  $waitlist is a boolean indicating whether this should adjust the waitlist count or not
     */
    public function update_count($amt, $waitlist = 0)
    {
        $bundle_members = $this->bundle_members();
        if (count($bundle_members) > 0) {
            foreach ($bundle_members as $m) {
                // When attempting to increment the count:
                // 1. Check if waitlisting and ad there
                // 2. Otherwise, add as normal
                if ($waitlist) {
                    $m->waitCount += $amt;
                } else {
                    $m->regCount += $amt;
                }
                $m->save();
            }
        } else {
            if ($waitlist) {
                $this->waitCount += $amt;
            } else {
                $this->regCount += $amt;
            }
            $this->save();
        }
    }

    /**
     * has_sessions()   Determines if a ticket has been connected with EventSessions
     */
    public function has_sessions()
    {
        $bundles = $this->bundle_members();
        foreach ($bundles as $m) {
            $es = EventSession::where([
                ['eventID', '=', $this->eventID],
                ['ticketID', '=', $m->ticketID],
            ])->get();
            if (count($es) > 1) {
                return 1;
            }
        }
        $es = EventSession::where([
            ['eventID', '=', $this->eventID],
            ['ticketID', '=', $this->ticketID],
        ])->get();

        return count($es) > 1;
    }

    public function ok_to_display()
    {
        $today = Carbon::now();

        return $this->availabilityEndDate->gte($today) && $this->isSuppressed == 0;
    }

    /**
     * bundle_ticket_array() returns an array of ticketIDs associated with the chosen ticket's bundle members
     *             or an array of just the single ticketID
     */
    public function bundle_ticket_array()
    {
        $bundle_members = $this->bundle_members();
        if (count($bundle_members) > 0) {
            return $bundle_members->pluck('ticketID')->toArray();
        } else {
            return [$this->ticketID];
        }
    }

    /**
     * bundle_parents() returns an array of ticketIDs that could result the purchase of $this ticket
     */
    public function bundle_parents()
    {
        return self::join('bundle-ticket as bt', 'bt.ticketID', 'event-tickets.ticketID')
            ->where([
                ['bt.ticketID', '=', $this->ticketID],
                ['event-tickets.eventID', '=', $this->eventID],
            ])
            ->pluck('bundleID')->toArray();
    }

    /**
     * bundle_parent_array() returns an array of ticketIDs associated with bundles that include $this ticket
     *             or an array of just the single ticketID
     */
    public function bundle_parent_array()
    {
        $bundle_parents = $this->bundle_parents();
        array_push($bundle_parents, $this->ticketID);

        return $bundle_parents;
    }
}
