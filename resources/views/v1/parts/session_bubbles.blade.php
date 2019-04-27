<?php
/**
 * Comment: This is a DIV version of the session selection form
 * Created: 7/12/2017
 * Updated: 9/29/2018
 *
 * Passing in $event, $ticket, $rf, $reg
 * @param $event: the event for which sessions will be displayed
 * @param $ticket: the ticket (bundle or otherwise) for which sessions will be displayed
 *                 in the event a ticket is a bundle, sessions will be displayed for the appropriate component ticket(s)
 * @param $rf: the regFinance record associated with the registration
 * @param $reg: the individual registration instance for which the sessions will be displayed
 *
 * Update requires this form to be modularized in a way that allows or suppresses an individual selection form
 *
 * @param $suppress: if 1, the form pieces (open, close, submit button) will be suppressed
 *
 */

use App\Ticket;
use App\Track;
use App\EventSession;
use App\RegSession;

        // If an event has tracks, there could be sessions associated with 1 or more tickets
        // (depending on the number of days that have sessions)

        // Assumption will be that session_bubbles.blade is included when sessions are needed w/ $ticket->has_sessions()

if(!isset($suppress)) { $suppress = 0; }
if(!isset($registering)) { $registering = 0; }

//$needSessionPick = $reg->ticket->has_sessions();

if($event->hasTracks > 0) {
    $tracks = Track::where('eventID', $event->eventID)->get();

    if($event->hasTracks == 2) {
        if($event->isSymmetric){
            $time_column = 'col-sm-2';
            $track_column = 'col-sm-5';
        } else {
            $time_column = 'col-sm-1';
            $track_column = 'col-sm-5';
        }
    } else {
        if($event->isSymmetric){
            $time_column = 'col-sm-2';
            $track_column = 'col-sm-3';
        } else {
            $time_column = 'col-sm-1';
            $track_column = 'col-sm-3';
        }
    }
} else {
    $tracks = null;
}

// Bundle Tickets won't have sessions associated with them, but any individual ticket(s) that make it up can
// So if it's a bundle,
// 1. Get the child tickets of the bundle (parent)
// 2. Get all session records that were created for the event
// 3. Check if the ticketID associated with the sessions appears in #1 collection, and set $needSessionPick = 1



// Retrieve the session data associated with any previously-selected sessions
$regSessions = RegSession::where([
    ['regID', '=', $reg->regID],
    ['eventID', '=', $event->eventID]
])->get();

// Retrieve the member tickets of a bundle if appropriate
$tickets = $ticket->bundle_members();

?>
@if(1)
    @if(count($regSessions)== 0)
        @if($registering)
            <b>@lang('messages.instructions.no_reg_sess_init')</b><br/>
        @else
            <b>@lang('messages.instructions.no_reg_sess')</b><br/>
        @endif
    @else
        <b>@lang('messages.instructions.reg_sess')</b><br/>
    @endif

    @if(!$suppress)
    {!! Form::open(['url' => '/update_sessions/'.$reg->regID, 'method' => 'post',
                    'id' => 'complete_registration', 'data-toggle' => 'validator']) !!}
    @endif

    @if($event->hasTracks > 0)
        <div class="col-sm-12 well-sm" style="text-align: left; color: yellow; background-color: #2a3f54;">
            <b>@lang('messages.fields.track_select')</b>
        </div>

        <div class="col-sm-12">
            @foreach($tracks as $track)
                @if($tracks->first() == $track || !$event->isSymmetric)
                    <div class="{{ $time_column }}" style="text-align: left;">
                        <b>@lang('messages.fields.sess_times')</b>
                    </div>
                @endif
                <div class="{{ $track_column }}" style="text-align: left;">
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
            @if($tickets !== null)
                @if($tickets->contains('ticketID', $z->ticketID))
                    <div class="col-sm-12">&nbsp;<br/></div>
                    <div class="col-sm-12 well-sm" style="text-align:center; color: yellow; background-color: #2a3f54;">
                        <b>@lang('messages.headers.day') {{ $j }}: {{ $y->ticketLabel  }}</b>
                    </div>
                    <div class="col-sm-12">&nbsp;<br/></div>

                    @for($x=1;$x<=5;$x++)
<?php
                        // Check to see if there are any events for $x (this row)
                        $s = EventSession::where([
                            ['eventID', $event->eventID],
                            ['confDay', $j],
                            ['order', $x]
                        ])->first();

                        // As long as there are any sessions, the row will be displayed
                        // Added new row suppression based on whether the sessions are part of a purchased ticket
?>
                        @if($s !== null && $tickets->contains('ticketID', $s->ticketID))
                            <div class="col-sm-12">
                                @foreach($tracks as $track)
<?php
                                    $s = EventSession::where([
                                        ['trackID', $track->trackID],
                                        ['eventID', $event->eventID],
                                        ['confDay', $j],
                                        ['order', $x]
                                    ])->withTrashed()->first();

                                    if($s !== null && ($s->deleted_at === null || $s->isLinked == 1)) {
                                        $mySess = $s->sessionID;
                                    } else {
                                        $mySess = null;
                                    }
?>
                                    @if($s !== null && $s->deleted_at === null)
                                        @if($tracks->first() == $track || !$event->isSymmetric)

                                            <div class="{{ $time_column }}" style="text-align:left;">
                                                <nobr> {{ $s->start->format('g:i A') }} </nobr>
                                                &dash;
                                                <nobr> {{ $s->end->format('g:i A') }} </nobr>
                                            </div>
                                        @endif

                                        <div class="{{ $track_column }}" style="text-align:left;">
                                            @if($s !== null && $tickets->contains('ticketID', $s->ticketID))
                                            @if($s->maxAttendees > 0 && $s->regCount >= $s->maxAttendees)
                                                <b>{{ $s->sessionName }}</b><br />
                                            <span class="red">@lang('messages.instructions.max_reached')</span>
                                                <br/>
                                            @else
                                                <b>{{ $s->sessionName }}</b><br/>
                                            @endif
                                            {{--
                                                $regSession check for pre-selection by registrant
                                            --}}
                                            <center>
                                                @if($regSessions->contains('sessionID', $s->sessionID))
                                                    @if($s->maxAttendees > 0 && $s->regCount >= $s->maxAttendees || $s->maxAttendees == 0)
                                                        {!! Form::radio('sess-'. $j . '-'.$x . '-' . $reg->regID, $s->sessionID, true,
                                                            $attributes=array('disabled', 'required', 'id' => 'sess-'. $j . '-'.$x .'-'. $mySess)) !!}
                                                    @else
                                                        {!! Form::radio('sess-'. $j . '-'.$x . '-' . $reg->regID, $s->sessionID, true,
                                                            $attributes=array('required', 'id' => 'sess-'. $j . '-'.$x .'-'. $mySess)) !!}
                                                    @endif
                                    {{--
                                        Need to come up with a way to tie form components together when sessions span times
                                        1. Check for asymmetry because sessions in one track can only BE SET to overlap when asymmetric
                                        2. If asymmetric, for each session, check if there is a "shadow" session with these attributes
                                            a. deleted_at is NOT NULL
                                            b. session with the same start/end times
                                            c. session with contiguous order
                                        3. Deleted session needs javascript to keep user selection in sync with its live & shadow copies
                                    --}}
                                                @else
                                                    @if($s->maxAttendees > 0 && $s->regCount >= $s->maxAttendees)
                                                        {!! Form::radio('sess-'. $j . '-'.$x . '-' . $reg->regID, $s->sessionID, false,
                                                            $attributes=array('disabled', 'required', 'id' => 'sess-'. $j . '-'.$x .'-'. $mySess)) !!}
                                                    @else
                                                        {!! Form::radio('sess-'. $j . '-'.$x . '-' . $reg->regID, $s->sessionID, false,
                                                            $attributes=array('required', 'id' => 'sess-'. $j . '-'.$x .'-'. $mySess)) !!}
                                                    @endif
                                                @endif
                                            </center>
                                            {{--  Need to connect to 'sess-'.$j.'-'.$x-1 and 'sess-'.$j.'-'.$x
                                                  and have jquery set it to clicked and vice versa;
                                                  if selection moves from x or x-1, it unselects the other
                                                  if selection moves onto 1, it moves onto the other --}}
                                                @if($s->isLinked)
<?php

// If we're dealing with a linked session:
// 1. Find out how many & which sessions are linked
// 2. Setup the jquery code to cause hidden radio buttons to be selected based on this one's selection

                                                        $linked = EventSession::where([
                                                            ['trackID', $track->trackID],
                                                            ['eventID', $event->eventID],
                                                            ['confDay', $j],
                                                            ['sessionID', '!=', $s->sessionID],
                                                            ['isLinked', $s->sessionID]
                                                        ])->withTrashed()->get();
?>

                                                            @if($linked !== null)
<script>
    $(document).ready(function () {
        var rowname = '{{ 'sess-' . $j.'-'.$x.'-'.$reg->regID }}';
        var sessname = '{{ 'sess-' . $j.'-'.$x.'-'.$s->sessionID }}';
        $("input:radio[name='" + rowname + "']").on('change', function () {
            console.log(rowname + " changed.");
            if ($("input:radio[id='{{ 'sess-'. $j . '-'.$x.'-' . $s->sessionID }}']").prop("checked")) {
                console.log(sessname + " is checked.");
                @foreach($linked as $link)
                child = '{{ 'sess-' . $j.'-'.$link->order.'-'.$link->sessionID }}';
                console.log("  Checking " + child + ".");
                $("input:radio[id='" + child + "']").prop('checked', true);
                console.log("Checked? " + $("input:radio[id='" + child + "']").prop('checked'));
                @endforeach
            }
        });
    });
</script>
                                                            @endif
                                                        @endif
                                                    @endif
                                                </div>
                                            @else  {{-- Dealing with Deleted Session --}}
                                                @if($tracks->first() == $track || !$event->isSymmetric)
                                                    <div class="{{ $track_column }}" style="text-align:left;">
                                                @else
                                                    <div class="{{ $track_column }}" style="text-align:left;">
                                                @endif
<?php
                                                $parent = EventSession::find($s->isLinked);
?>
                                                @if($s->isLinked)
                                                {!! Form::radio('sess-'. $j . '-'.$x . '-' . $reg->regID, $mySess, true,
                                                $attributes=array('id' => 'sess-'. $j . '-'.$x .'-' . $s->sessionID, 'style' => 'visibility:hidden;')) !!}
                                                <script>
                                                    $(document).ready(function () {
                                                        {{-- If the parent changes, uncheck the hidden radio button --}}
                                                        $("input:radio[name='{{ 'sess-'. $j . '-'.$parent->order . '-' . $reg->regID }}']").on('change', function () {
                                                            console.log("{{ 'sess-'. $j . '-'.$parent->order .'-'.$reg->regID }} changed.");
                                                            if ($('#{{ 'sess-'. $j . '-'.$parent->order.'-' . $parent->sessionID }}').is(":checked")) {
                                                                $('#{{ 'sess-'. $j . '-'.($x) .'-'. $s->sessionID }}').prop('checked', 'checked');
                                                            } else {
                                                                $('#{{ 'sess-'. $j . '-'.($x) .'-'. $s->sessionID }}').removeAttr('checked');
                                                            }
                                                        });
                                                        {{-- If the hidden radio button changes, uncheck the parent --}}
                                                        $("input:radio[name='{{ 'sess-'. $j . '-'.($x).'-'. $reg->regID }}']").on('change', function () {
                                                            console.log("{{ 'sess-'. $j . '-'.($x) .'-'.$reg->regID }} changed.");
                                                            $('#{{ 'sess-'. $j . '-'.($parent->order) .'-' . $parent->sessionID }}').removeAttr('checked');
                                                        });
                                                    });
                                                </script>
                                                    </div>
                                                @endif
                                          @endif
                                     @endforeach
                                </div>
                                @endif
                            @endfor
                        @endif  {{-- if included ticket --}}
            @endif  {{-- if included ticket --}}
       @endfor  {{-- this closes confDays loop --}}

   @endif  {{-- closes hasTracks loop --}}

   <div class="col-sm-12"><p>&nbsp;</p></div>

    @if(!$suppress)
   <button type="submit" class="btn btn-primary btn-sm">@lang('messages.buttons.sess_reg')</button>
   {!! Form::close() !!}
    @endif
@else
    {{--
   <b>This event does not have sessions. </b><br/>
    --}}
@endif      {{-- closes $needSessionPick --}}
