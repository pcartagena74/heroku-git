<?php
/**
 * Comment: List the events to which the current user has signed up
 * Created: 7/11/2017
 *
 * $attendance: the list of events where registration completed
 * $progress: the list of events where registration was not completed
 *
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
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @include('v1.parts.start_content', ['header' => 'Your Paid Registered Events', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
    <div class="col-md-12 col-sm-12 col-xs-12">
        @if(count($paid)==0)
            <b>You have not registered for any upcoming events.</b>
        @endif

        @foreach($paid as $a)
            @if($a->event->eventStartDate->gt($today))
                @include('v1.parts.start_min_content', ['header' => $a->event->eventName,
                'subheader' => $a->event->eventStartDate->format('n/j/Y'), 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
                <div class="col-md-12 col-sm-12 col-xs-12">

                    @if($a->seats > 1)
                        @for($i=$a->regID-($a->seats-1);$i<=$a->regID;$i++)
<?php
                            $person = Person::find($reg->personID);
                            $ticket = Ticket::find($reg->ticketID);
                            $event = Event::find($reg->eventID);
                            $regSessions = RegSession::where([
                                ['regID', '=', $reg->regID],
                                ['eventID', '=', $event->eventID]
                            ])->get();
?>
                            @include('v1.parts.start_min_content', ['header' => $a->registration->membership .
                            ' Ticket Purchased: ' . $a->ticket->ticketLabel . " (" . $a->registration->regID . ")",
                            'subheader' => '<i class="fa fa-dollar"></i> ' . $a->registration->subtotal,
                            'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

                                @if(count($regSessions)==0)
                                    <b>You have not yet registered for sessions.  You can do so below. </b><br />
                                @endif
{{--
                                {!! Form::open(['url' => '/cancel_registration/'.$a->registration->regID . '/' . $a->regID,
                                                'method' => 'delete', 'id' => 'cancel_registration_'.$a->registration->regID, 'data-toggle' => 'validator']) !!}
--}}
    {!! Form::open(['method'  => 'delete', 'route' => [ 'cancel_registration', $a->registration->regID, $a->regID ], 'data-toggle' => 'validator' ]) !!}


                                <button type="submit" class="btn btn-danger btn-sm">
                                    @if($a->registration->subtotal > 0)
                                        Refund Registration
                                    @else
                                        Cancel Registration
                                    @endif
                                </button>
                                {!! Form::close() !!}
                                <br/>

                                @include('v1.parts.session_bubbles', ['event' => $a->event, 'ticket' => $a->ticket, 'rf' => $a,
                                         'reg' => $a->registration, 'regSession' => $regSessions])

                            @include('v1.parts.end_content')

                        @endfor

                    @else
                        @include('v1.parts.start_min_content', ['header' => $a->registration->membership .
                        ' Ticket Purchased: ' . $a->ticket->ticketLabel . " (" . $a->registration->regID . ")",
                        'subheader' => '<i class="fa fa-dollar"></i> ' . $a->registration->subtotal,
                        'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
<?php
                        $regSessions = RegSession::where([
                            ['regID', '=', $a->registration->regID],
                            ['eventID', '=', $a->event->eventID]
                        ])->get();
?>
                        @if(count($regSessions)==0)
                            <b>You have not yet registered for sessions.  You can do so below. </b><br />
                        @endif

                        {!! Form::open(['method'  => 'delete', 'route' => [ 'cancel_registration', $a->registration->regID, $a->regID ], 'data-toggle' => 'validator' ]) !!}
{{--
                        {!! Form::open(['url' => '/cancel_registration/'.$a->registration->regID, 'method' => 'delete',
                                        'id' => 'cancel_registration_'.$a->registration->regID, 'data-toggle' => 'validator']) !!}
--}}
                        <button type="submit" class="btn btn-danger btn-sm">
                            @if($a->registration->subtotal > 0)
                                Refund Registration
                            @else
                                Cancel Registration
                            @endif
                        </button>
                        {!! Form::close() !!}
                        <br/>

                        @include('v1.parts.session_bubbles', ['event' => $a->event, 'ticket' => $a->ticket, 'rf' => $a,
                                 'reg' => $a->registration, 'regSession' => $regSessions])


                        @include('v1.parts.end_content')
                    @endif

                </div>
                @include('v1.parts.end_content')
            @endif
        @endforeach

    </div>
    @include('v1.parts.end_content')

    @if(count($unpaid)!=0)
    @include('v1.parts.start_content', ['header' => 'Your Unpaid Registered Events', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
    <div class="col-md-12 col-sm-12 col-xs-12">
        @if(count($unpaid)==0)
            <b>You have not registered for any upcoming events.</b>
        @endif

        @foreach($unpaid as $a)
            @if($a->event->eventStartDate->gt($today))
                @include('v1.parts.start_min_content', ['header' => $a->event->eventName,
                'subheader' => $a->event->eventStartDate->format('n/j/Y'), 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
                <div class="col-md-12 col-sm-12 col-xs-12">

                    @if($a->seats > 1)
                        @for($i=$a->regID-($a->seats-1);$i<=$a->regID;$i++)
                            <?php
                            $person = Person::find($reg->personID);
                            $ticket = Ticket::find($reg->ticketID);
                            $event = Event::find($reg->eventID);
                            $regSessions = RegSession::where([
                                ['regID', '=', $reg->regID],
                                ['eventID', '=', $event->eventID]
                            ])->get();
                            ?>
                            @include('v1.parts.start_min_content', ['header' => $a->registration->membership .
                            ' Ticket Purchased: ' . $a->ticket->ticketLabel . " (" . $a->registration->regID . ")",
                            'subheader' => '<i class="fa fa-dollar"></i> ' . $a->registration->subtotal,
                            'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

                            @if(count($regSessions)==0)
                                <b>You have not yet registered for sessions.  You can do so below. </b><br />
                            @endif
                            {{--
                                                            {!! Form::open(['url' => '/cancel_registration/'.$a->registration->regID . '/' . $a->regID,
                                                                            'method' => 'delete', 'id' => 'cancel_registration_'.$a->registration->regID, 'data-toggle' => 'validator']) !!}
                            --}}
                            {!! Form::open(['method'  => 'delete', 'route' => [ 'cancel_registration', $a->registration->regID, $a->regID ], 'data-toggle' => 'validator' ]) !!}


                            <button type="submit" class="btn btn-danger btn-sm">
                                @if($a->registration->subtotal > 0)
                                    Refund Registration
                                @else
                                    Cancel Registration
                                @endif
                            </button>
                            {!! Form::close() !!}
                            <br/>

                            @include('v1.parts.session_bubbles', ['event' => $a->event, 'ticket' => $a->ticket, 'rf' => $a,
                                     'reg' => $a->registration, 'regSession' => $regSessions])

                            @include('v1.parts.end_content')

                        @endfor

                    @else
                        @include('v1.parts.start_min_content', ['header' => $a->registration->membership .
                        ' Ticket Purchased: ' . $a->ticket->ticketLabel . " (" . $a->registration->regID . ")",
                        'subheader' => '<i class="fa fa-dollar"></i> ' . $a->registration->subtotal,
                        'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
                        <?php
                        $regSessions = RegSession::where([
                            ['regID', '=', $a->registration->regID],
                            ['eventID', '=', $a->event->eventID]
                        ])->get();
                        ?>
                        @if(count($regSessions)==0)
                            <b>You have not yet registered for sessions.  You can do so below. </b><br />
                        @endif

                        {!! Form::open(['method'  => 'delete', 'route' => [ 'cancel_registration', $a->registration->regID, $a->regID ], 'data-toggle' => 'validator' ]) !!}
                        {{--
                                                {!! Form::open(['url' => '/cancel_registration/'.$a->registration->regID, 'method' => 'delete',
                                                                'id' => 'cancel_registration_'.$a->registration->regID, 'data-toggle' => 'validator']) !!}
                        --}}
                        <button type="submit" class="btn btn-danger btn-sm">
                            @if($a->registration->subtotal > 0)
                                Refund Registration
                            @else
                                Cancel Registration
                            @endif
                        </button>
                        {!! Form::close() !!}
                        <br/>

                        @include('v1.parts.session_bubbles', ['event' => $a->event, 'ticket' => $a->ticket, 'rf' => $a,
                                 'reg' => $a->registration, 'regSession' => $regSessions])


                        @include('v1.parts.end_content')
                    @endif

                </div>
                @include('v1.parts.end_content')
            @endif
        @endforeach

    </div>
    @include('v1.parts.end_content')
    @endif

    @if(count($pending)!=0)
    @include('v1.parts.start_content', ['header' => 'Your Abandoned Registrations', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
    <div class="col-md-12 col-sm-12 col-xs-12">

        @foreach($pending as $a)
            @if($a->event->eventStartDate->gt($today))
                @include('v1.parts.start_min_content', ['header' => $a->event->eventName,
                'subheader' => $a->event->eventStartDate->format('n/j/Y'), 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
                <div class="col-md-12 col-sm-12 col-xs-12">

                    @include('v1.parts.start_min_content', ['header' => $a->registration->membership .
                    ' Ticket Purchased: ' . $a->ticket->ticketLabel . " (" . $a->registration->regID . ")",
                    'subheader' => '<i class="fa fa-dollar"></i> ' . $a->registration->subtotal,
                    'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

                    {!! Form::open(['method'  => 'delete', 'route' => [ 'cancel_registration', $a->registration->regID, $a->regID ], 'data-toggle' => 'validator' ]) !!}

                    <button type="submit" class="btn btn-danger btn-sm">
                            Cancel Registration
                    </button>
                    {!! Form::close() !!}

                    {!! Form::open(['method'  => 'get', 'route' => [ 'register_step3', $a->regID], 'data-toggle' => 'validator' ]) !!}

                    <button type="submit" class="btn btn-success btn-sm">
                        Continue Registration
                    </button>
                    {!! Form::close() !!}
                            <br/>

                            @include('v1.parts.end_content')




                </div>
                @include('v1.parts.end_content')
            @endif
        @endforeach

    </div>
    @include('v1.parts.end_content')
    @endif

@endsection

@section('scripts')
    <script>
        $('.collapsed').css('height', 'auto');
        $('.collapsed').find('.x_content').css('display', 'none');
        {{--
                $('.collapsed').find('i').toggleClass('fa-chevron-up fa-chevron-down');
        --}}
    </script>
@endsection