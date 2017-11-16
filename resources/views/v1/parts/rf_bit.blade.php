<?php
/**
 * Comment: This is a reusable blade file that shows
 *          registrations based on passed reg-finance collection
 *
 * @param:
 *      $header: for display
 *      $rf_array: the collection of reg-finance records
 *
 * Created: 8/25/2017
 */

use App\RegSession;
use App\Registration;
use App\Person;
use App\Ticket;
use App\EventSession;
use App\Event;

$tcount = 0;
$today = \Carbon\Carbon::now();

?>

@include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
<div class="col-md-12 col-sm-12 col-xs-12">

    @foreach($rf_array as $a)
        @if($a->event->eventEndDate->gte($today))
            @include('v1.parts.start_min_content', ['header' => $a->event->eventName,
            'subheader' => $a->event->eventStartDate->format('n/j/Y'), 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
            <div class="col-md-12 col-sm-12 col-xs-12">

                @if($a->seats > 1)

                    @foreach($a->registration as $reg)
<?php
                        $person = Person::find($reg->personID);
                        $ticket = Ticket::find($reg->ticketID);
                        $event = Event::find($reg->eventID);
                        $regSessions = RegSession::where([
                            ['regID', '=', $reg->regID],
                            ['eventID', '=', $event->eventID]
                        ])->get();
?>
                        @include('v1.parts.start_min_content', ['header' => $reg->membership .
                        " Ticket (" .  $person->showFullName() . "): " . $reg->ticket->ticketLabel . " (" . $reg->regID . ")",
                        'subheader' => '<i class="fa fa-dollar"></i> ' . $reg->subtotal,
                        'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                        {!! Form::open(['method'  => 'delete', 'route' => [ 'cancel_registration', $reg->regID, $a->regID ], 'data-toggle' => 'validator' ]) !!}

                        <button type="submit" class="btn btn-danger btn-sm">
                            @if($reg->subtotal > 0)
                                Refund Registration
                            @else
                                Cancel Registration
                            @endif
                        </button>
                        {!! Form::close() !!}
                        <br/>

                        @include('v1.parts.session_bubbles', ['event' => $a->event, 'ticket' => $reg->ticket, 'rf' => $a,
                        'reg' => $reg, 'regSession' => $regSessions])

                        @include('v1.parts.end_content')

                    @endforeach

                @else
<?php
                    $reg = Registration::with('ticket', 'person')->where('regID', $a->regID)->first();
?>
                    @include('v1.parts.start_min_content', ['header' => $reg->membership .
                    ' (' . $reg->person->showFullName() . ') Ticket: ' . $reg->ticket->ticketLabel . " (" . $reg->regID . ")",
                    'subheader' => '<i class="fa fa-dollar"></i> ' . $reg->subtotal,
                    'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
<?php
                    $regSessions = RegSession::where([
                        ['regID', '=', $reg->regID],
                        ['eventID', '=', $a->event->eventID]
                    ])->get();
?>

                    {!! Form::open(['method'  => 'delete',
                    'route' => [ 'cancel_registration', $reg->regID, $a->regID ],
                    'data-toggle' => 'validator' ]) !!}

                    <button type="submit" class="btn btn-danger btn-sm">
                        @if($reg->subtotal > 0)
                            Refund Registration
                        @else
                            Cancel Registration
                        @endif
                    </button>
                    {!! Form::close() !!}
                    <br/>

                    @include('v1.parts.session_bubbles', ['event' => $a->event, 'ticket' => $reg->ticket, 'rf' => $a,
                    'reg' => $reg, 'regSession' => $regSessions])

                    @include('v1.parts.end_content')
                @endif

            </div>
            @include('v1.parts.end_content')
        @endif
    @endforeach

</div>
@include('v1.parts.end_content')
