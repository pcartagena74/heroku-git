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

    @if(count($bought)>0)
        @include('v1.parts.reg_bit', ['header' => 'Events Purchased On Your Behalf', 'reg_array' => $bought])
    @endif

    @if(count($paid) + count($unpaid) + count($pending) == 0)
        <b>You have not registered for any upcoming events.</b>
    @else
        @if(count($paid)>0)
            @include('v1.parts.rf_bit', ['header' => 'Your Paid Registered Events', 'rf_array' => $paid])
        @endif
        @if(count($unpaid)>0)
            @include('v1.parts.rf_bit', ['header' => 'Your Unpaid Registered Events', 'rf_array' => $unpaid])
        @endif
        @if(count($pending)>0)
            @include('v1.parts.rf_bit', ['header' => 'Your Incomplete Registered Events', 'rf_array' => $pending])
        @endif
    @endif


@endsection

@section('scripts')
    <script>
        $('.collapsed').css('height', 'auto');
        $('.collapsed').find('.x_content').css('display', 'none');
    </script>
@endsection