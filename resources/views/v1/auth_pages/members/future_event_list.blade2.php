<?php
/**
 * Comment: List the events to which the current user has signed up
 * Created: 7/11/2017
 *
 * $attendance: the list of events where registration completed
 * $progress: the list of events where registration was not completed
 */
use App\Models\Registration;

$tcount = 0;
$today = \Carbon\Carbon::now();

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @if(count($paid) + count($unpaid) + count($pending) + count($bought) == 0)
        <b>@lang('messages.instructions.no_fut_events')</b>
    @else
        @if(0 && count($pending)>0)
            @include('v1.parts.rf_bit', ['header' => trans('messages.headers.fut_inc'), 'rf_array' => $pending])
        @endif

        @if(count($bought)>0)
            @include('v1.parts.reg_bit', ['header' => trans('messages.headers.fut_behalf'), 'reg_array' => $bought])
        @endif

        @if(count($wait)>0)
            @include('v1.parts.rf_bit', ['header' => trans('messages.headers.fut_wait'), 'rf_array' => $wait])
        @endif

        @if(count($unpaid)>0)
            @include('v1.parts.rf_bit', ['header' => trans('messages.headers.fut_unpaid'), 'rf_array' => $unpaid])
        @endif

        @if(count($paid)>0)
            @include('v1.parts.rf_bit', ['header' => trans('messages.headers.fut_paid'), 'rf_array' => $paid])
        @endif
    @endif

@endsection

@section('scripts')
    <script>
        $('.collapsed').css('height', 'auto');
        $('.collapsed').find('.x_content').css('display', 'none');
    </script>
@endsection