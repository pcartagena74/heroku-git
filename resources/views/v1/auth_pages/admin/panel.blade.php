<?php
/**
 * Comment:
 * Created: 2/9/2017
 */

$topBits = '';  // remove this if this was set in the controller
$header = '';

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('event-management'))
        || Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))

        <h2>@lang('messages.nav.ad_panel')</h2>
        @include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
        <!-- stuff -->
        @include('v1.parts.end_content')
    @endif

@endsection

@section('scripts')
@endsection

@section('footer')
@endsection
