@php
    /**
     * Comment:
     * Created: 10/8/20
     */

    $topBits = '';  // remove this if this was set in the controller
    $header = trans('messages.admin.api.api_props');

@endphp

@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
    <div id="root">


        @if(Entrust::can('event-management') || Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))

            <h2>@lang('messages.nav.ad_panel')</h2>
            @include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
        <!-- stuff -->

            @include('v1.parts.end_content')
        @endif

    </div>
@endsection

@section('scripts')
    <script src="panel-vue.js"></script>
@endsection

@section('footer')
@endsection
