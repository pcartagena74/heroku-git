<?php
/**
 * Comment: This is a DIV version of the session selection form
 * Created: 7/13/2017
 */

use App\Ticket;
use App\Models\Track;
use App\Models\EventSession;

if($event->isSymmetric) {
    $columns = ($event->hasTracks * 2) + 1;
    $width   = number_format(85 / $event->hasTracks, 0, '', '');
    $mw      = number_format(90 / $event->hasTracks, 0, '', '');
} else {
    $columns = $event->hasTracks * 3;
    $width   = number_format(80 / $event->hasTracks, 0, '', '');
    $mw      = number_format(85 / $event->hasTracks, 0, '', '');
}

if($event->hasTracks > 0) {
    $tracks = Track::where('eventID', $event->eventID)->get();
} else {
    $tracks = null;
}

if($ticket->isaBundle) {
    $tickets = Ticket::join('bundle-ticket as bt', 'bt.ticketID', 'event-tickets.ticketID')
                     ->where([
                         ['bt.bundleID', '=', $ticket->ticketID],
                         ['event-tickets.eventID', '=', $event->eventID]
                     ])
                     ->get();
    $s       = EventSession::where('eventID', '=', $event->eventID)
                           ->select(DB::raw('distinct ticketID'))
                           ->get();
    foreach($s as $t) {
        if($tickets->contains('ticketID', $t->ticketID)) {
            $needSessionPick = 1;
            break;
        }
    }
} else {
    $tickets = Ticket::where('ticketID', '=', $rf->ticketID)->get();
    $s       = EventSession::where([
        ['eventID', '=', $event->eventID],
        ['ticketID', '=', '$ticket->ticketID']
    ])->first();

    if($s !== null) {
        $needSessionPick = 1;
    }
}

?>

@if($event->hasTracks > 0)
    <div class="col-sm-12 form-group" style="text-align: left; color: yellow; background-color: #2a3f54;">
        Track Selection
    </div>
    <div class="col-sm-12">
        <div class="col-sm-2" style="text-align: left;">
            Session Times
        </div>
        <div class="col-sm-4" style="text-align: left;">
            Selected Sessions
        </div>
    </div>

    @for($j=1;$j<=$event->confDays;$j++)
        <?php
        $z = EventSession::where([
            ['confDay', '=', $j],
            ['eventID', '=', $event->eventID]
        ])->first();
        $y = Ticket::find($z->ticketID);

        ?>
        @if($tickets->contains('ticketID', $z->ticketID))
            <div class="col-sm-12 form-group" style="text-align:center; color: yellow; background-color: #2a3f54;">
                Day {{ $j }}: {{ $y->ticketLabel  }}
            </div>

            {{--
                1. Need to show the session options for each day
                2. Need to enable selection and ajax saving
                3. Need to check for the asymmetric session exclusivity
            --}}

            @foreach($rs as $z)
                @if($rs->first() == $z)
                    <?php
                    $s = EventSession::find($z->sessionID);
                    $y = Ticket::find($s->ticketID);
                    ?>

                @endif
                <?php
                $s = EventSession::with('track')->where('sessionID', $z->sessionID)->first();
                ?>
                <div class="col-sm-12" style="text-align: left;">
                    <div class="col-sm-2" style="text-align: left;">
                        <nobr> {{ $s->start->format('g:i A') }} </nobr>
                        &dash;
                        <nobr> {{ $s->end->format('g:i A') }} </nobr>
                    </div>
                    <div class="col-sm-3" style="text-align: left;">
                        <b>{{ $s->track->trackName }}</b><br/>
                        {{ $s->sessionName }} <br/>
                    </div>
                </div>
            @endforeach
        @endif
    @endfor
@endif


