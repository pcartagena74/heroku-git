<?php
/**
 * Comment: This is a DIV version of the session selection form
 *          This is included by rf-bit.blade and reg-bit.blade to display the session selection form to update sessions.
 *          This is also included by register2.blade during the registration process.
 *          All pages that include this MUST check whether $event->hasTracks() or purchased $ticket->has_sessions()
 * Created: 7/12/2017
 * Updated: 9/29/2018
 * Overhauled: 6/4/19 to accomodate session overlap
 *
 * Passing in $event, $ticket, $rf, $reg
 * @param $event :  the event for which sessions will be displayed
 * @param $ticket : the ticket (bundle or otherwise) for which sessions will be displayed
 *                 in the event a ticket is a bundle, sessions will be displayed for the appropriate component ticket(s)
 * @param $rf :     the regFinance record associated with the registration
 * @param $reg :    the individual registration instance for which the sessions will be displayed
 *
 * Update requires this form to be modularized in a way that allows or suppresses an individual selection form
 *
 * @param $suppress : if 1, the form pieces (open, close, submit button) will be suppressed because selections can no
 *                   longer be edited.
 */

use App\Ticket;
use App\Track;
use App\EventSession;
use App\RegSession;
use App\Org;

if (!isset($suppress)) {
    $suppress = 0;
}
if (!isset($registering)) {
    $registering = 0;
}

if ($event->hasTracks > 0) {
    $tracks = Track::where('eventID', $event->eventID)->get();

    if ($event->hasTracks == 2) {
        if ($event->isSymmetric) {
            $time_column = 'col-sm-2';
            $track_column = 'col-sm-5';
        } else {
            $time_column = 'col-sm-1';
            $track_column = 'col-sm-5';
        }
    } else {
        if ($event->isSymmetric) {
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

if(!isset($org)){
    $org = Org::find($event->orgID);
}

// Bundle Tickets won't have sessions associated with them, but any individual ticket(s) that make it up can
// So if it's a bundle,
// 1. Get the child tickets of the bundle (parent)
// 2. Get all session records that were created for this $reg record for this $event

// Retrieve the session data associated with any previously-selected sessions for this registration record
$regSessions = RegSession::where([
    ['regID', '=', $reg->regID],
    ['eventID', '=', $event->eventID]
])->get();

// Retrieve the member tickets of a bundle if appropriate
$purchased_tickets = $ticket->bundle_members();
if (count($purchased_tickets) == 0) {
    $purchased_tickets = Ticket::where('ticketID', $ticket->ticketID)->get();
}

// These are the ticketIDs that have been purchased, it's an array of 1 item (if stand-alone) or more (if bundled)
$valid_ticketIDs = $ticket->ticketIDs();

?>
@if(1)  {{-- didn't clean up; previously checked if this included stuff should display --}}
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
        // the calculus to determine if a conference day should be displayed is based on purchased tickets associated with any
        // event sessions on that day; Check for valid ticketIDs because there could be "special" sessions requiring purchase
        $y = null;

        $tickets_associated_with_confDay_sessions = EventSession::where([
            ['confDay', '=', $j],
            ['eventID', '=', $event->eventID]
        ])->distinct()->pluck('ticketID')->toArray();

        foreach ($tickets_associated_with_confDay_sessions as $tkt) {
            if (in_array($tkt, $valid_ticketIDs)) {
                $y = Ticket::find($tkt);
                break;
            }
        }

        ?>
        @if($purchased_tickets !== null && $y !== null)
            @if($purchased_tickets->contains('ticketID', $y->ticketID))
                <div class="col-sm-12">&nbsp;<br/></div>
                <div class="col-sm-12 well-sm" style="text-align:center; color: yellow; background-color: #2a3f54;">
                    <b>@lang('messages.headers.day') {{ $j }}: {{ $y->ticketLabel  }}</b>
                </div>
                <div class="col-sm-12">&nbsp;<br/></div>

                @for($x=1;$x<=5;$x++)
                    <?php
                    // Check to see if there are any non-deleted sessions for row $x (this row)
                    $s = EventSession::where([
                        ['eventID', $event->eventID],
                        ['confDay', $j],
                        ['order', $x]
                    ])->whereIn('ticketID', $valid_ticketIDs)->first();

                    // As long as there are any sessions, the row will be displayed
                    // Added new row suppression based on whether the sessions are part of a purchased ticket
                    ?>
                    @if($s !== null && $purchased_tickets->contains('ticketID', $s->ticketID))
                        <div class="col-sm-12">
                            @foreach($tracks as $track)
                                <?php
                                $s = EventSession::where([
                                    ['trackID', $track->trackID],
                                    ['eventID', $event->eventID],
                                    ['confDay', $j],
                                    ['order', $x]
                                ])->withTrashed()->first();

                                // set $mySess if the session is either an active or "shadow" session
                                if ($s !== null && ($s->deleted_at === null || $s->isLinked)) {
                                    $mySess = $s->sessionID;
                                } else {
                                    $mySess = null;
                                }
                                ?>
                                @if($s !== null && $s->deleted_at === null)
                                    {{--
                                        If this is the first track OR there are "uneven" tracks
                                        AND it's for a purchased ticket, show the session times
                                    --}}
                                    @if(($tracks->first() == $track || !$event->isSymmetric)
                                        && in_array($s->ticketID, $valid_ticketIDs))
                                        <div class="{{ $time_column }}" style="text-align:left;">
                                            <nobr> {{ $s->start->format('g:i A') }} </nobr>
                                            &dash;
                                            <nobr> {{ $s->end->format('g:i A') }} </nobr>
                                        </div>
                                    @endif

                                    <div class="{{ $track_column }}" style="text-align:left;">
                                        @if($s !== null && $purchased_tickets->contains('ticketID', $s->ticketID)
                                            && in_array($s->ticketID, $valid_ticketIDs))
                                            @if($s->maxAttendees > 0 && $s->regCount >= $s->maxAttendees)
                                                <b>{{ $s->sessionName }}</b>
                                                @if(env('APP_ENV') == 'local')
                                                    <small class="pull-right">({{ $s->sessionID }})</small><br/>
                                                @endif
                                                <span class="red">{!!  $org->fullTXT ?? trans('messages.instructions.max_reached') !!}</span>
                                                <br/>
                                            @else
                                                <b>{{ $s->sessionName }}</b>
                                                @if(env('APP_ENV') == 'local')
                                                    <small class="pull-right">({{ $s->sessionID }})</small><br/>
                                                @endif
                                            @endif
                                            {{--
                                                $regSession check for pre-selection by registrant
                                            --}}
                                            <center>
                                                @if(in_array($s->ticketID, $valid_ticketIDs))
                                                    @if($regSessions->contains('sessionID', $s->sessionID))
                                                        {{--  if sessions have been selected, the radio buttons should be selected  --}}
                                                        @if($s->maxAttendees > 0 && $s->regCount >= $s->maxAttendees || $s->maxAttendees == 0)
                                                            {{--  if sessions are full, this radio button should not be selectable  --}}
                                                            {!! Form::radio('sess-'. $j . '-'.$x . '-' . $reg->regID, $s->sessionID, true,
                                                                $attributes=array('disabled', 'required', 'id' => 'sess-'. $j . '-'.$x .'-'. $reg->regID . '-' . $mySess)) !!}
                                                        @else
                                                            {!! Form::radio('sess-'. $j . '-'.$x . '-' . $reg->regID, $s->sessionID, true,
                                                                $attributes=array('required', 'id' => 'sess-'. $j . '-'.$x .'-'. $reg->regID . '-' . $mySess)) !!}
                                                        @endif
                                                    @else
                                                        {{--  sessions have NOT been selected, so radio buttons should NOT be selected  --}}
                                                        @if($s->maxAttendees > 0 && $s->regCount >= $s->maxAttendees)
                                                            {!! Form::radio('sess-'. $j . '-'.$x . '-' . $reg->regID, $s->sessionID, false,
                                                                $attributes=array('disabled', 'required', 'id' => 'sess-'. $j . '-'.$x .'-'. $reg->regID . '-' . $mySess)) !!}
                                                        @else
                                                            {!! Form::radio('sess-'. $j . '-'.$x . '-' . $reg->regID, $s->sessionID, false,
                                                                $attributes=array('required', 'id' => 'sess-'. $j . '-'.$x .'-'. $reg->regID . '-' . $mySess)) !!}
                                                        @endif
                                                    @endif
                                                @endif
                                            </center>
                                            {{--
                                                To tie the form components together when a session spans/overlaps times:

                                                1. For each session, check if there is a "shadow" session with these attributes
                                                    a. deleted_at IS NOT NULL
                                                    b. session with the same start/end times
                                                    c. session with contiguous order
                                                2. Deleted session needs javascript to keep user selection in sync with its live & shadow copies
                                            --}}
                                            @if($s->isLinked)
                                                <?php
                                                // If we're dealing with a linked session:
                                                // 1. Find out how many & which sessions are linked (where isLinked == $s->sessionID)
                                                //    and exclude current $s->sessionID

                                                $linked = EventSession::where([
                                                    ['trackID', $track->trackID],
                                                    ['eventID', $event->eventID],
                                                    ['confDay', $j],
                                                    ['sessionID', '!=', $s->sessionID],
                                                    ['isLinked', $s->sessionID]
                                                ])->withTrashed()->get();

                                                // 2. Setup the jquery code to make hidden radio buttons be selected
                                                //    based on this one's selection, if there are indeed linked sessions
                                                $rowname = 'sess-' . $j . '-' . $x . '-' . $reg->regID;
                                                $sessname = 'sess-' . $j . '-' . $x . '-' . $reg->regID . '-' . $s->sessionID;
                                                ?>
                                                @if($linked !== null)
                                                    <script>
                                                        {{--
                                                        JQuery should:
                                                        1. Detect change on the row name for the real (parent) session
                                                        2. Determine if the parent is checked and then
                                                           a. if so, the linked child(ren) should also be checked
                                                           b. if not, the linked child(ren) should also be unchecked

                                                        First, add a line for each linked "child" so that each hidden radio button can be checked
                                                        when the parent radio button is checked.
                                                        --}}
                                                        $(document).ready(function () {
                                                            $("input:radio[name='{{ $rowname }}']").on('change', function () {
                                                                if ($("input:radio[id='{{ $sessname }}']").prop("checked") == true) {
                                                                    @foreach($linked as $link)
                                                                    <?php
                                                                    $childrow = 'sess-' . $j . '-' . $link->order . '-' . $reg->regID;
                                                                    $childsess = 'sess-' . $j . '-' . $link->order . '-' . $reg->regID . '-' . $link->sessionID;
                                                                    // childsess = '{{ $childsess }}';
                                                                    // console.log("Checking child: {{ $childsess }}.");
                                                                    // console.log("  Checked? " + $("input:radio[id='{{ $childsess }}']").prop('checked'));
                                                                    // console.log("{{ $rowname }} changed and {{ $sessname }} was checked. Setting child to true.");
                                                                    ?>
                                                                    $("input:radio[id='{{ $childsess }}']").prop('checked', true);
                                                                    @endforeach
                                                                } else {
                                                                    @foreach($linked as $link)
                                                                    <?php
                                                                    $childrow = 'sess-' . $j . '-' . $link->order . '-' . $reg->regID;
                                                                    $childsess = 'sess-' . $j . '-' . $link->order . '-' . $reg->regID . '-' . $link->sessionID;
                                                                    // childsess = '{{ 'sess-'.$j.'-'.$link->order.'-'.$reg->regID.'-'.$link->sessionID }}';
                                                                    // console.log("Checking child: " + childsess + ".");
                                                                    // console.log("  Checked? " + $("input:radio[id='" + childsess + "']").prop('checked'));
                                                                    // console.log("{{ $rowname }} changed but {{ $sessname }} was NOT checked. Setting child to false");
                                                                    ?>
                                                                    $("input:radio[id='{{ $childsess }}']").prop('checked', false);
                                                                    @endforeach
                                                                }
                                                            });
                                                        });
                                                    </script>
                                                    {{--
                                                    Now, loop through the children (shadow sessions) to add:
                                                     1. a hidden radio button
                                                     2. an on('change') event for each row with a shadow session
                                                        associated with current $s->sessionID (parent).

                                                    If the hidden child changes, it's because it's been deselected.  So the parent should be deselected.
                                                    This may not be needed here because we can add jquery code in a just-in-time way for each hidden-radio child
                                                    --}}
                                                    @foreach($linked as $link)
                                                        @if($regSessions->contains('sessionID', $s->sessionID))
                                                            {!! Form::radio('sess-'.$j.'-'.$link->order.'-'.$reg->regID, $link->sessionID, true,
                                                            $attributes=array('id' => 'sess-'.$j.'-'.$link->order.'-'.$reg->regID . '-' . $link->sessionID, 'style' => 'visibility:hidden;')) !!}
                                                        @else
                                                            {!! Form::radio('sess-'.$j.'-'.$link->order.'-'.$reg->regID, $link->sessionID, false,
                                                            $attributes=array('id' => 'sess-'.$j.'-'.$link->order .'-'.$reg->regID.'-'.$link->sessionID, 'style' => 'visibility:hidden;')) !!}
                                                        @endif
                                                    @endforeach

                                                    <script>
                                                        @foreach($linked as $link)
                                                        <?php
                                                        $childrow = 'sess-' . $j . '-' . $link->order . '-' . $reg->regID;
                                                        $childsess = 'sess-' . $j . '-' . $link->order . '-' . $reg->regID . '-' . $link->sessionID;
                                                        ?>
                                                        $(document).ready(function () {
                                                            $("input[name='{{ $childrow }}']:radio").on('change', function () {
                                                                if ($("input:radio[id='{{ $childsess }}']").prop("checked") == false) {
                                                                    $("input:radio[id='{{ $sessname }}']").prop('checked', false);
                                                                }
                                                            });
                                                        });
                                                        @endforeach
                                                    </script>
                                                @endif
                                            @endif
                                        @endif
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
    <div class="col-sm-12">
        <button type="submit"
                class="btn btn-primary btn-sm">@lang('messages.buttons.sess_reg')</button>
    </div>
    {!! Form::close() !!}
@endif
@else
    {{--
   <b>This event does not have sessions. </b><br/>
    --}}
@endif      {{-- closes $needSessionPick --}}
