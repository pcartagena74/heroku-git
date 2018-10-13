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
    @if(count($regSessions)==0)
        <b>@lang('messages.instructions.no_reg_sess')</b><br/>
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

                                            <div class="{{ $time_column }}" style="text-align:left;">
                                                <nobr> {{ $s->start->format('g:i A') }} </nobr>
                                                &dash;
                                                <nobr> {{ $s->end->format('g:i A') }} </nobr>
                                            </div>
                                        @else

                                        @endif
                                        <div class="{{ $track_column }}" style="text-align:left;">
                                            @if($s->maxAttendees > 0 && $s->regCount >= $s->maxAttendees)
                                                <b>{{ $s->sessionName }}</b><br />
                                            <span class="red">@lang('messages.instructions.max_reached')</span>
                                                <br/>
                                            @else
                                                <b>{{ $s->sessionName }}</b><br/>
                                            @endif
                                            <center>
                                                @if($regSessions->contains('sessionID', $s->sessionID))
                                                    @if($s->maxAttendees > 0 && $s->regCount >= $s->maxAttendees)
                                                        {!! Form::radio('sess-'. $j . '-'.$x . '-' . $reg->regID, $s->sessionID, true,
                                                            $attributes=array('disabled', 'required', 'id' => 'sess-'. $j . '-'.$x .'-'. $mySess)) !!}
                                                    @else
                                                        {!! Form::radio('sess-'. $j . '-'.$x . '-' . $reg->regID, $s->sessionID, true,
                                                            $attributes=array('required', 'id' => 'sess-'. $j . '-'.$x .'-'. $mySess)) !!}
                                                    @endif
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
                                        </div>
                                    @else
                                        @if($tracks->first() == $track || !$event->isSymmetric)
                                            <div class="{{ $track_column }}" style="text-align:left;">
                                        @else
                                            <div class="{{ $track_column }}" style="text-align:left;">
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
                                        {!! Form::radio('sess-'. $j . '-'.$x . '-' . $reg->regID, '', false,
                                        $attributes=array('required', 'id' => 'sess-'. $j . '-'.$x .'-x', 'style' => 'visibility:hidden;')) !!}
                                        <script>
                                            $(document).ready(function () {
                                                $("input:radio[name='{{ 'sess-'. $j . '-'.$x . '-' . $reg->regID }}']").on('change', function () {
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
