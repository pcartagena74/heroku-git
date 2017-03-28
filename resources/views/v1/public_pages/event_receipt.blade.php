<?php
/**
 * Comment: Event Receipt
 * Created: 3/26/2017
 */
    $today = Carbon\Carbon::now();

    // $rf, $loc, $event, $org
        $ticketLabel = $rf->registration->ticket->ticketLabel;
?>

@extends('v1.layouts.no-auth')

@section('content')
    @include('v1.parts.start_content', ['header' => "Registration Receipt", 'subheader' => '', 'o' => '2', 'w1' => '8', 'w2' => '8', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div class="myrow col-md-12 col-sm-12">
        <div class="col-md-2 col-sm-2" style="text-align:center;">
            <h1 class="fa fa-5x fa-calendar"></h1>
        </div>
        <div class="col-md-7 col-sm-7">
            <h2><b>{{ $event->eventName }}</b></h2>
            <div style="margin-left: 10px;">
                {{ $event->eventStartDate->format('n/j/Y g:i A') }}
                - {{ $event->eventEndDate->format('n/j/Y g:i A') }}
                <br>
                {{ $loc->locName }}<br>
                {{ $loc->addr1 }} <i class="fa fa-circle fa-tiny-circle"></i> {{ $loc->city }}
                , {{ $loc->state }} {{ $loc->zip }}
            </div>
            <br/>
        </div>
        <div class="col-md-3 col-sm-3">
        </div>
    </div>
    <div class="myrow col-md-12 col-sm-12">
        <div class="col-md-2 col-sm-2" style="text-align:center;">
            <h1 class="fa fa-5x fa-dollar"></h1>
        </div>
        <div class="col-md-7 col-sm-7">
            @if($rf->seats > 1)
                <table class="table table-condensed jambo_table table-striped">
                    <thead>
                    <tr>
                        <th style="text-align: left;">Attendee</th>
                        <th style="text-align: left;">Ticket</th>
                        <th style="text-align: left;">Original Cost</th>
                        <th style="text-align: left;">Discounts</th>
                        <th style="text-align: left;">Total</th>
                    </tr>
                    </thead>
                    <tbody>

                    @for($i=$rf->regID-1;$i<=$rf->regID;$i++)
                        <?php
                        $reg = \App\Registration::find($i);
                        $person = \App\Person::find($reg->personID);
                        $ticket = \App\Ticket::find($reg->ticketID);
                        ?>
                        <tr>
                            <td style="text-align: left;">{!! $person->firstName . " " . $person->lastName !!}</td>
                            <td style="text-align: left;">{!! $ticket->ticketLabel !!}</td>
                            <td style="text-align: left;"><nobr><i class="fa fa-dollar"></i>
                                @if($reg->membership == 'Member')
                                    {{ number_format($ticket->memberBasePrice, 2, ".", ",") }}
                                @else
                                    {{ number_format($ticket->nonmbrBasePrice, 2, ".", ",") }}
                                @endif
                            </nobr></td>
                            @if(!($ticket->earlyBirdEndDate === null) && $ticket->earlyBirdEndDate->diffInSeconds($today)>0)
                                @if($rf->discountCode)
                                    <td style="text-align: left;">Early Bird, {{ $rf->discountCode }}</td>
                                @else
                                    <td style="text-align: left;">Early Bird</td>
                                @endif
                            @else
                                @if($rf->discountCode)
                                    <td style="text-align: left;">{{ $rf->discountCode }}</td>
                                @else
                                    <td style="text-align: left;"> --</td>
                                @endif
                            @endif
                            <td style="text-align: left;"><nobr><i class="fa fa-dollar"></i>
                                {{ number_format($reg->subtotal, 2, ".", ",") }}
                            </nobr></td>
                        </tr>

                    @endfor
                    <tr>
                        <td colspan="4" style="text-align: left;"></td>
                        <td style="text-align: left;"><nobr><i class="fa fa-dollar"></i>
                            {{ number_format($rf->cost, 2, ".", ",") }}
                            </nobr></td>
                    </tr>
                    </tbody>
                </table>
            @else
                <ul>
                    <li><b>Ticket:</b> {{ $ticketLabel }}</li>
                    <li><b>Total Cost:</b> {{ $rf->cost }}</li>
                </ul>
            @endif
            <table class="table borderless">
                <tr>
                    <td style="text-align: center;"><img src="{{ $message->embed('/images/outlook.jpg') }}" height="55" /></td>
                    <td style="text-align: center;"><img src="{{ $message->embed('/images/google.jpg') }}" height="45" /></td>
                    <td style="text-align: center;"><img src="{{ $message->embed('/images/yahoo.jpg') }}" height="45" /></td>
                    <td style="text-align: center;"><img src="{{ $message->embed('/images/ical.jpg') }}" height="45" /></td>
                </tr>
            </table>
        </div>
    </div>

    {{-- add links to ical, etc. --}}
    @include('v1.parts.end_content')
@endsection
