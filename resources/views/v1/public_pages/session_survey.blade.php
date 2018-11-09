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

?>
@extends('v1.layouts.no-auth_simple')

@section('content')
    @include('v1.parts.start_content', ['header' => "Please Record Your Session Feedback", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @if($event->showLogo && $logo)
        <img src="{{ $logo }}" height="50">
    @endif
    <h2>Event: {{ $event->eventName }}</h2>
    <b>Session: {{ $session->sessionName }}</b>

    <p>&nbsp;</p>
    Please provide your objective feedback so that we may continuously improve the value we provide to our membership.
    <br>
    Please rate the speaker/presentation in the following categories.
    <p>&nbsp;</p>
    {!! Form::open((['url' => env('APP_URL').'/rs_survey', 'method' => 'post', 'id' => 'session_survey', 'data-toggle' => 'validator'])) !!}

    {!! Form::hidden('eventID', $event->eventID) !!}
    {!! Form::hidden('orgID', $org->orgID) !!}
    {!! Form::hidden('regID', $rs->regID) !!}
    {!! Form::hidden('personID', $rs->personID) !!}
    {!! Form::hidden('sessionID', $rs->sessionID) !!}

    <h2><b>SPEAKER ENGAGEMENT:</b> speaker encouraged audience comments and participation.<SUP style="color:red;">*</SUP> </h2><br/>
    <div class="form-group col-xs-12">
        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('engageResponse', '4', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('engageResponse', '3', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('engageResponse', '2', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('engageResponse', '1', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('engageResponse', '0', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>
    </div>
    <div style="text-align:center;" class="form-group col-md-12 col-xs-12">
        <div class="form-group col-xs-2">
            {!! Form::label('engageResponse', 'Very Good', array('class' => 'control-label')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::label('engageResponse', 'Good', array('class' => 'control-label')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::label('engageResponse', 'Needs Improvement', array('class' => 'control-label')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::label('engageResponse', 'Speaker Did Not Engage', array('class' => 'control-label')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::label('engageResponse', 'No Comment', array('class' => 'control-label')) !!}
        </div>
    </div>


    <h2><b>TAKE-AWAYS:</b> speaker provided knowledge/best practices that can be used on your next project.<SUP style="color:red;">*</SUP> </h2><br/>
    <div class="form-group col-md-12 col-xs-12">
        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('takeResponse', '4', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('takeResponse', '3', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('takeResponse', '2', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('takeResponse', '1', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('takeResponse', '0', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>
    </div>
    <div style="text-align:center;" class="form-group col-md-12 col-xs-12">
        <div class="form-group col-xs-2">
            {!! Form::label('takeResponse', 'Very Good', array('class' => 'control-label')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::label('takeResponse', 'Good', array('class' => 'control-label')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::label('takeResponse', 'Needs Improvement', array('class' => 'control-label')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::label('takeResponse', 'Speaker Did Not Engage', array('class' => 'control-label')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::label('takeResponse', 'No Comment', array('class' => 'control-label')) !!}
        </div>
    </div>


    <h2><b>CONTENT/DEPTH OF PRESENTATION:</b> content at level that met expectations.<SUP style="color:red;">*</SUP> </h2><br/>
    <div class="form-group col-md-12 col-xs-12">
        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('contentResponse', '4', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('contentResponse', '3', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('contentResponse', '2', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('contentResponse', '1', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('contentResponse', '0', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>
    </div>
    <div style="text-align:center;" class="form-group col-md-12 col-xs-12">
        <div class="form-group col-xs-2">
            {!! Form::label('contentResponse', 'Very Good', array('class' => 'control-label')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::label('contentResponse', 'Good', array('class' => 'control-label')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::label('contentResponse', 'Needs Improvement', array('class' => 'control-label')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::label('contentResponse', 'Speaker Did Not Engage', array('class' => 'control-label')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::label('contentResponse', 'No Comment', array('class' => 'control-label')) !!}
        </div>
    </div>

    <h2><b>SPEAKER STYLE:</b> presentation and speaker delivery encouraged learning.<SUP style="color:red;">*</SUP> </h2><br/>
    <div class="form-group col-md-12 col-xs-12">
        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('styleResponse', '4', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('styleResponse', '3', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('styleResponse', '2', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('styleResponse', '1', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::radio('styleResponse', '0', false, $attributes = array('class'=>'form-control', 'required')) !!}
        </div>
    </div>
    <div style="text-align:center;" class="form-group col-md-12 col-xs-12">
        <div class="form-group col-xs-2">
            {!! Form::label('styleResponse', 'Very Good', array('class' => 'control-label')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::label('styleResponse', 'Good', array('class' => 'control-label')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::label('styleResponse', 'Needs Improvement', array('class' => 'control-label')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::label('styleResponse', 'Speaker Did Not Engage', array('class' => 'control-label')) !!}
        </div>

        <div style="text-align:center;" class="form-group col-xs-2">
            {!! Form::label('styleResponse', 'No Comment', array('class' => 'control-label')) !!}
        </div>
    </div>

    <h2><b>What was your favorite part of the presentation?</b></h2><br/>
    <div class="form-group col-xs-12">
        <div style="text-align:center;" class="form-group col-xs-5">
            {!! Form::textarea('favoriteResponse', '', $attributes = array('class'=>'form-control', 'rows' => '5')) !!}
        </div>
    </div>


    <h2><b>What suggestions do you have to improve the presentation?</b></h2><br/>
    <div class="form-group col-xs-12">
        <div style="text-align:center;" class="form-group col-md-5 col-xs-5">
            {!! Form::textarea('suggestResponse', '', $attributes = array('class'=>'form-control', 'rows' => '5')) !!}
        </div>
    </div>


    <h2><b>OPTIONAL: Check this box and include your contact info if you would like to speak to a volunteer
            organizer.</b></h2><br/>
    <div class="form-group col-xs-12">
        <div style="text-align:center;" class="form-group col-xs-1">
                {!! Form::checkbox('wantsContact', 'wantsContact', false, $attributes = array('class' => 'form-control')) !!}
        </div>
        <div style="text-align:center;" class="form-group col-xs-5">
                {!! Form::textarea('contactResponse', '', $attributes = array('class'=>'form-control', 'rows' => '5')) !!}
        </div>
    </div>


    <div class="form-group col-xs-12">
        {!! Form::submit('Submit Survey Responses', array('class' => 'btn btn-primary')) !!}
    </div>

    {!! Form::close() !!}
    @include('v1.parts.end_content')


@endsection
