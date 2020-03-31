@php

/**
 * Comment: Create New Organization and associate it with a user.
 *
 * Created: 18/02/2020
 */

$topBits = '';  // remove this if this was set in the controller
$header = implode(" ", [trans('messages.nav.o_create')]);

@endphp
@extends('v1.layouts.auth', ['topBits' => $topBits])
@section('header')
{{-- <link href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" rel="stylesheet"/> --}}
{{--
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet"/>
--}}
@endsection
<link href="{{ asset('vendor/file-manager/css/file-manager.css') }}" rel="stylesheet"/>
@section('content')
<div class="form-group col-xs-12">
    <div style="height: 700px;">
        <div id="fm">
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('vendor/file-manager/js/file-manager.js') }}">
</script>

@endsection
@section('footer')
@endsection
