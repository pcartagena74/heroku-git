@php
/**
 * Comment:
 * Created: 10/8/20
 */

$topBits = '';  // remove this if this was set in the controller
$header = '';

@endphp
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @if((Entrust::can('event-management'))
        || Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))

        @include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
        <!-- stuff -->
        @include('v1.parts.end_content')
    @endif

@endsection

@section('scripts')
    @include('v1.parts.menu-fix', ['path' => '/dashboard'])
@endsection

@section('footer')
@endsection
