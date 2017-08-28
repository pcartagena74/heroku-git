<?php
/**
 * Comment: This is a reusable blade file that shows
 *          registrations purchased by someone else
 *          (reg-finance->personID <> $this->currentPerson->personID)
 *
 * @param:
 *      $header: for display
 *      $reg_array: the collection of registration records
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
//dd($rf_array);
?>

@include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
<div class="col-md-12 col-sm-12 col-xs-12">


    @foreach($reg_array as $reg)
        @if($reg->event->eventEndDate->gte($today))
            @include('v1.parts.start_min_content', ['header' => $reg->event->eventName,
            'subheader' => $reg->event->eventStartDate->format('n/j/Y'), 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
            <div class="col-md-12 col-sm-12 col-xs-12">

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

                        {!! Form::open(['method'  => 'delete', 'route' => [ 'cancel_registration', $reg->regID, $reg->regfinance->regID ], 'data-toggle' => 'validator' ]) !!}

                        <button type="submit" class="btn btn-danger btn-sm">
                            @if($reg->subtotal > 0)
                                Refund Registration
                            @else
                                Cancel Registration
                            @endif
                        </button>
                        {!! Form::close() !!}
                        <br/>

                        @include('v1.parts.session_bubbles', ['event' => $reg->event, 'ticket' => $reg->ticket, 'rf' => $reg->regfinance,
                        'reg' => $reg, 'regSession' => $regSessions])

                        @include('v1.parts.end_content')

            </div>
            @include('v1.parts.end_content')
        @endif
    @endforeach

</div>
@include('v1.parts.end_content')
