<?php
/**
 * Comment: a No-Auth way to mark session attendance
 * Created: 7/5/2017
 *
 * Consider the following:
 *  - what if the sessionID is somehow incorrect... show options?
 *
 */

use GrahamCampbell\Flysystem\Facades\Flysystem;

try {
    if ($org->orgLogo !== null) {
        $s3m = Flysystem::connection('s3_media');
        $logo = $s3m->getAdapter()->getClient()->getObjectURL(env('AWS_BUCKET3'), $org->orgPath . "/" . $org->orgLogo);
    }
} catch (\League\Flysystem\Exception $exception) {
    $logo = '';
}
?>
@extends('v1.layouts.no-auth_simple')

@section('content')
    @include('v1.parts.start_content', ['header' => "Record Your Session Attendance", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @if($event->showLogo && $logo)
        <img src="{{ $logo }}" height="50">
    @endif
    <h2>Event: {{ $event->eventName }}</h2>
    <b>Session: {{ $session->sessionName }}</b>

    <p>&nbsp;</p>
    {!! Form::open((['url' => env('APP_URL').'/rs/' . $session->sessionID . '/edit', 'method' => 'post', 'id' => 'session_registration', 'data-toggle' => 'validator'])) !!}

    {!! Form::hidden('eventID', $event->eventID) !!}
    {!! Form::hidden('orgID', $org->orgID) !!}

    <div class="form-group has-feedback col-md-12 col-xs-12">
    {!! Form::label('regID', 'Please enter your registration id number', array('class' => 'control-label')) !!}
    {!! Form::text('regID', '', $attributes = array('class'=>'form-control has-feedback-left', 'required')) !!}
        <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span>
    </div>
    <div class="form-group col-md-12 col-xs-12">
    {!! Form::submit('Submit Session Attendance', array('class' => 'btn btn-primary')) !!}
    </div>

    {!! Form::close() !!}
    @include('v1.parts.end_content')


@endsection