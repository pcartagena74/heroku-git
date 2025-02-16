@php
    /**
     * Comment: after registering for a session, show the session form
     * Created: 7/6/2017
     *
     * @var $session
     * @var $org
     *
     * Consider the following:
     *  -
     *
     */

    use League\Flysystem;

    $logo = '';
    $logo_filename = $org->orgPath . "/" . $org->orgLogo;

    try {
        if ($org->orgLogo !== null) {
            if (Storage::disk('s3_media')->exists($logo_filename)) {
                $logo = Storage::disk('s3_media')->url($logo_filename);
            }
        }
    } catch (Exception $e) {
        $logo = '';
    }
    $speakers = $session->show_speakers();

@endphp
@extends('v1.layouts.no-auth_simple')

@section('content')
    @include('v1.parts.start_content', ['header' => trans('messages.surveys.title'), 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @if($event->showLogo && $logo)
        <img src="{{ $logo }}" height="50">
    @endif
    <h2>@lang('messages.fields.event'): {{ $event->eventName }}</h2>
    @if($session->sessionName != "def_sess")
        <b>@lang('messages.fields.session'):</b> {{ $session->sessionName }} <br/>
    @endif
    @if($speakers)
        <b>@lang('messages.fields.speakers'):</b> {{ $speakers }}
    @endif

    <p>&nbsp;</p>
    {!! trans('messages.instructions.survey_instructions') !!}
    <p>&nbsp;</p>
    {{ html()->form('POST', env('APP_URL') . '/rs_survey')->id('session_survey')->data('toggle', 'validator')->open() }}

    {{ html()->hidden('eventID', $event->eventID) }}
    {{ html()->hidden('orgID', $org->orgID) }}
    {{ html()->hidden('regID', $rs->regID) }}
    {{ html()->hidden('personID', $rs->personID) }}
    {{ html()->hidden('sessionID', $rs->sessionID) }}

    <h2>{!! trans('messages.surveys.q1') !!}<SUP style="color:red;">*</SUP></h2><br/>
    @include('v1.parts.survey_buttons', ['button_name' => 'engageResponse'])

    <h2>{!! trans('messages.surveys.q2') !!}<SUP style="color:red;">*</SUP></h2><br/>
    @include('v1.parts.survey_buttons', ['button_name' => 'takeResponse'])

    <h2>{!! trans('messages.surveys.q3') !!}<SUP style="color:red;">*</SUP></h2><br/>
    @include('v1.parts.survey_buttons', ['button_name' => 'contentResponse'])

    <h2>{!! trans('messages.surveys.q4') !!}<SUP style="color:red;">*</SUP></h2><br/>
    @include('v1.parts.survey_buttons', ['button_name' => 'styleResponse'])

    <h2><b>{!! trans('messages.surveys.q5') !!}</b></h2><br/>
    <div class="form-group col-xs-12">
        <div style="text-align:center;" class="form-group col-xs-12">
            {{ html()->textarea('favoriteResponse', '')->attributes($attributes = array('class'=>'form-control', 'rows' => '5')) }}
        </div>
    </div>

    <h2><b>{!! trans('messages.surveys.q6') !!}</b></h2><br/>
    <div class="form-group col-xs-12">
        <div style="text-align:center;" class="form-group col-xs-12">
            {{ html()->textarea('suggestResponse', '')->attributes($attributes = array('class'=>'form-control', 'rows' => '5')) }}
        </div>
    </div>

    <h2><b>{!! trans('messages.surveys.q7') !!}</b></h2><br/>
    <div class="form-group col-xs-12">
        <div style="text-align:center;" class="form-group col-xs-2">
            {{ html()->checkbox('wantsContact', false, 'wantsContact')->attributes($attributes = array('class' => 'form-control')) }}
        </div>
        <div style="text-align:center;" class="form-group col-xs-10">
            {{ html()->textarea('contactResponse', '')->attributes($attributes = array('class'=>'form-control', 'rows' => '5')) }}
        </div>
    </div>

    <div class="form-group col-xs-12">
        {{ html()->submit(trans('messages.surveys.submit'))->class('btn btn-primary') }}
    </div>

    {{ html()->form()->close() }}
    @include('v1.parts.end_content')

@endsection
