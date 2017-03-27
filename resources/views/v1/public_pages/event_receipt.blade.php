<?php
/**
 * Comment: Event Receipt
 * Created: 3/26/2017
 */
?>

@extends('v1.layouts.no-auth')

@section('content')
    @include('v1.parts.start_content', ['header' => "Registration Confirmation", 'subheader' => '', 'o' => '2', 'w1' => '8', 'w2' => '8', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <div class="whole">

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
    </div>
    @include('v1.parts.end_content')
@endsection
