<?php
/**
 * Comment: This is a DIV version of the session selection form
 * Created: 7/12/2017
 *
 * Passing in $event, $ticket, $rf, $reg
 *
 */

use App\Ticket;
use App\Track;
use App\EventSession;
use App\RegSession;
/*
if($event->isSymmetric) {
    $columns = ($event->hasTracks * 2) + 1;
    $width   = number_format(85 / $event->hasTracks, 0, '', '');
    $mw      = number_format(90 / $event->hasTracks, 0, '', '');
} else {
    $columns = $event->hasTracks * 3;
    $width   = number_format(80 / $event->hasTracks, 0, '', '');
    $mw      = number_format(85 / $event->hasTracks, 0, '', '');
}

                        <ol>
                            <li>Show Ticket detail: membership?, discountCode?</li>
                            <li>Button to cancel/refund by ticket</li>
                            <ol>
                                <li>decrement appropriate registration counts (ticket, session, etc.)</li>
                                <li>perform refund IF money was spent</li>
                            </ol>
                            <li>Show sessions and allow changes</li>
                            <ul>
                                <li>re-make/send receipt</li>
                            </ul>
                        </ol>
*/

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
    $tickets = Ticket::where('ticketID', '=', $reg->ticketID)->get();
    $s       = EventSession::where([
        ['eventID', '=', $event->eventID],
        ['ticketID', '=', '$ticket->ticketID']
    ])->first();

    if($s !== null) {
        $needSessionPick = 1;
    }
}

/*
$regSessions = RegSession::where([
    ['regID', '=', $reg->regID],
    ['eventID', '=', $event->eventID]
])->get();
*/

if($reg->regID == 8674){
   // dd($regSessions);
}

?>

{!! Form::open(['url' => '/update_sessions/'.$reg->regID, 'method' => 'post',
                'id' => 'complete_registration', 'data-toggle' => 'validator']) !!}

@if($event->hasTracks > 0)
    <div class="col-sm-12 well-sm" style="text-align: left; color: yellow; background-color: #2a3f54;">
        Track Selection
    </div>

    <div class="col-sm-12">
        @foreach($tracks as $track)
            @if($tracks->first() == $track || !$event->isSymmetric)
                <div class="col-sm-1" style="text-align: left;">
                    <b>Session Times</b>
                </div>
            @endif
            <div class="col-sm-3" style="text-align: left;">
                <b>{{ $track->trackName }}</b>
            </div>
        @endforeach
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
            <div class="col-sm-12">&nbsp;<br /></div>
            <div class="col-sm-12 well-sm" style="text-align:center; color: yellow; background-color: #2a3f54;">
                Day {{ $j }}: {{ $y->ticketLabel  }}
            </div>
            <div class="col-sm-12">&nbsp;<br /></div>

            @for($x=1;$x<=5;$x++)
<?php
                // Check to see if there are any events for $x (this row)
                $s = EventSession::where([
                    ['eventID', $event->eventID],
                    ['confDay', $j],
                    ['order', $x]
                ])->first();

                // As long as there are any sessions, the row will be displayed
?>
                @if($s !== null)
                    <div class="col-sm-12">
                    @foreach($tracks as $track)
<?php
                        $s = EventSession::where([
                            ['trackID', $track->trackID],
                            ['eventID', $event->eventID],
                            ['confDay', $j],
                            ['order', $x]
                        ])->first();

                        if($s !== null) {
                            $mySess = $s->sessionID;
                        }
?>
                        @if($s !== null)
                                @if($tracks->first() == $track || !$event->isSymmetric)

                                    <div class="col-sm-1" style="text-align:left;">
                                        <nobr> {{ $s->start->format('g:i A') }} </nobr>
                                        &dash;
                                        <nobr> {{ $s->end->format('g:i A') }} </nobr>
                                    </div>
                                @else

                                @endif
                                <div class="col-sm-3" style="text-align:left;">
                                    <b>{{ $s->sessionName }}</b><br/>
                                    <center>
                                    @if($regSessions->contains('sessionID', $s->sessionID))

                                    {!! Form::radio('sess-'. $j . '-'.$x, $s->sessionID, true,
                                        $attributes=array('required', 'id' => 'sess-'. $j . '-'.$x .'-'. $mySess)) !!}
                                    @else
                                        {!! Form::radio('sess-'. $j . '-'.$x, $s->sessionID, false,
                                            $attributes=array('required', 'id' => 'sess-'. $j . '-'.$x .'-'. $mySess)) !!}
                                    @endif
                                    </center>
                                    {{--  Need to connect to 'sess-'.$j.'-'.$x-1 and 'sess-'.$j.'-'.$x
                                          and have jquery set it to clicked and vice versa;
                                          if selection moves from x or x-1, it unselects the other
                                          if selection moves onto 1, it moves onto the other --}}
                                </div>
                        @else
                                @if($tracks->first() == $track || !$event->isSymmetric)
                                    <div class="col-sm-4" style="text-align:left;">
                                @else
                                    <div class="col-sm-3" style="text-align:left;">
                                @endif
<?php
                                                $t = EventSession::where([
                                                    ['trackID', $track->trackID],
                                                    ['eventID', $event->eventID],
                                                    ['confDay', $j],
                                                    ['order', $x - 1]
                                                ])->first();

                                                if($t !== null) {
                                                    $myTess = $t->sessionID;
                                                }
?>
                                                {!! Form::radio('sess-'. $j . '-'.$x, '', false,
                                                    $attributes=array('required', 'id' => 'sess-'. $j . '-'.$x .'-x', 'style' => 'visibility:hidden;')) !!}
                                                <script>
                                                    $(document).ready(function () {
                                                        $("input:radio[name='{{ 'sess-'. $j . '-'.$x }}']").on('change', function () {
                                                            console.log("{{ 'sess-'. $j . '-'.$x .'-x' }}  changed.");
                                                            if ($('#{{ 'sess-'. $j . '-'.$x.'-x' }}').is(":checked")) {
                                                                $('#{{ 'sess-'. $j . '-'.($x-1) .'-'. $myTess }}').prop('checked', 'checked');
                                                            } else {
                                                                $('#{{ 'sess-'. $j . '-'.($x-1) .'-'. $myTess }}').removeAttr('checked');
                                                            }
                                                        });
                                                        $("input:radio[name='{{ 'sess-'. $j . '-'.($x-1) }}']").on('change', function () {
                                                            console.log("{{ 'sess-'.$j.'-'.($x-1) . '-' . $myTess }}  changed.");
                                                            if ($('#{{ 'sess-'. $j . '-'.($x-1).'-' . $myTess }}').is(":checked")) {
                                                                $('#{{ 'sess-'. $j . '-'.($x) .'-x' }}').prop('checked', 'checked');
                                                            } else {
                                                                $('#{{ 'sess-'. $j . '-'.($x) .'-x' }}').removeAttr('checked');
                                                            }
                                                        });
                                                    });
                                                </script>
                                            </div>
                        @endif
                    @endforeach
                    </div>
                @endif
            @endfor
        @endif  {{-- if included ticket --}}
    @endfor  {{-- this closes confDays loop --}}

@endif  {{-- closes hasTracks loop --}}
    <div class="col-sm-12"><p>&nbsp;</p></div>

    <button type="submit" class="btn btn-primary btn-sm">Submit Session Registration Changes</button>
{!! Form::close() !!}

