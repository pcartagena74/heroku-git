<?php
/**
 * Comment: after registering for a session, show the session form
 * Created: 7/6/2017
 *
 * Consider the following:
 *  -
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

$speakers = $session->show_speakers();

?>
@extends('v1.layouts.no-auth_simple')

@section('content')
    @include('v1.parts.start_content', ['header' => trans('messages.surveys.title'), 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @if($event->showLogo && $logo)
        <img src="{{ $logo }}" height="50">
    @endif
    <h2>@lang('messages.fields.event'): {{ $event->eventName }}</h2>
    @if($session->sessionName != "def_sess")
        <b>@lang('messages.fields.session'):</b> {{ $session->sessionName }} <br />
    @endif
    @if($speakers)
        <b>@lang('messages.fields.speakers'):</b> {{ $speakers }}
    @endif

    <p>&nbsp;</p>
    {!! trans('messages.instructions.survey_instructions') !!}
    <p>&nbsp;</p>
    {!! Form::open((['url' => env('APP_URL').'/rs_survey', 'method' => 'post', 'id' => 'session_survey', 'data-toggle' => 'validator'])) !!}

    {!! Form::hidden('eventID', $event->eventID) !!}
    {!! Form::hidden('orgID', $org->orgID) !!}
    {!! Form::hidden('regID', $rs->regID) !!}
    {!! Form::hidden('personID', $rs->personID) !!}
    {!! Form::hidden('sessionID', $rs->sessionID) !!}

    <h2>{!! trans('messages.surveys.q1') !!}<SUP style="color:red;">*</SUP> </h2><br/>
    @include('v1.parts.survey_buttons', ['button_name' => 'engageResponse'])

    <h2>{!! trans('messages.surveys.q2') !!}<SUP style="color:red;">*</SUP> </h2><br/>
    @include('v1.parts.survey_buttons', ['button_name' => 'takeResponse'])

    <h2>{!! trans('messages.surveys.q3') !!}<SUP style="color:red;">*</SUP> </h2><br/>
    @include('v1.parts.survey_buttons', ['button_name' => 'contentResponse'])

    <h2>{!! trans('messages.surveys.q4') !!}<SUP style="color:red;">*</SUP> </h2><br/>
    @include('v1.parts.survey_buttons', ['button_name' => 'styleResponse'])

    <h2><b>{!! trans('messages.surveys.q5') !!}</b></h2><br/>
    <div class="form-group col-xs-12">
        <div style="text-align:center;" class="form-group col-xs-12">
            {!! Form::textarea('favoriteResponse', '', $attributes = array('class'=>'form-control', 'rows' => '5')) !!}
        </div>
    </div>

    <h2><b>{!! trans('messages.surveys.q6') !!}</b></h2><br/>
    <div class="form-group col-xs-12">
        <div style="text-align:center;" class="form-group col-xs-12">
            {!! Form::textarea('suggestResponse', '', $attributes = array('class'=>'form-control', 'rows' => '5')) !!}
        </div>
    </div>

    <h2><b>{!! trans('messages.surveys.q7') !!}</b></h2><br/>
    <div class="form-group col-xs-12">
        <div style="text-align:center;" class="form-group col-xs-2">
                {!! Form::checkbox('wantsContact', 'wantsContact', false, $attributes = array('class' => 'form-control')) !!}
        </div>
        <div style="text-align:center;" class="form-group col-xs-10">
                {!! Form::textarea('contactResponse', '', $attributes = array('class'=>'form-control', 'rows' => '5')) !!}
        </div>
    </div>

    <div class="form-group col-xs-12">
        {!! Form::submit(trans('messages.surveys.submit'), array('class' => 'btn btn-primary')) !!}
    </div>

    {!! Form::close() !!}
    @include('v1.parts.end_content')

@endsection
