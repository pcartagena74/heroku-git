<?php
/**
 * Comment: a No-Auth way to mark session attendance
 * Created: 7/5/2017
 *
 * Consider the following:
 *  - what if the sessionID is somehow incorrect... show options?
 *
 */
use App\EventSession;
//use hisorange\BrowserDetect\Provider\BrowserDetectService;
//ini_set('memory_limit', '256M');
?>
@extends('v1.layouts.no-auth_simple')

@section('content')
    @include('v1.parts.start_content', ['header' => "Register Attendee", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @if($event->showLogo && $org->orgLogo !== null)
        <img src="{{ $org->orgPath . "/" . $org->orgLogo }}" height="50">
    @endif
    <h2>Event: {{ $event->eventName }}</h2>

    <p>&nbsp;</p>

    @if($event->hasTracks > 0 && $session->trackID == 0)

        <div class="col-sm-12 col-xs-12">
            @foreach($track as $t)
                <div class="col-sm-3 col-xs-3">
                    {{ $t->trackName }}
                </div>
            @endforeach
        </div>

        @for($i=1;$i<=$event->confDays;$i++)
            <div class="form-group col-sm-12 col-xs-12">
                <div style="background-color:#2a3f54; color:yellow;"
                     class="col-sm-{{ 3 * count($track) }} col-xs-{{ 3 * count($track) }}">
                    Day {{ $i }} Sessions
                </div>
            </div>

            @for($x=1;$x<=5;$x++)
<?php
                $s = EventSession::where([
                    ['eventID', $event->eventID],
                    ['confDay', $i],
                    ['order', $x]
                ])->first();
?>
                @if($s !== null)
                <div class="form-group col-sm-12 col-xs-12">
                    @foreach($track as $t)
<?php
                            $s = EventSession::where([
                                ['trackID', $t->trackID],
                                ['eventID', $event->eventID],
                                ['confDay', $i],
                                ['order', $x]
                            ])->first();
?>
                            @if($s !== null)
                            <div class="col-sm-3 col-xs-3">
                                <a href="/checkin/{{ $event->eventID}}/{{ $s->sessionID }}"
                                   style="white-space: normal;" class="btn btn-primary btn-sm">
                                    @if(Agent::isMobile())
                                        {{ $t->trackName . " " . $s->order }}
                                    @else
                                        {{ $s->sessionName }}
                                    @endif
                                </a>
                            </div>
                            @endif
                    @endforeach
                </div>
                @endif
            @endfor
        @endfor

    @else
        {!! Form::open((['url' => env('APP_URL').'/process_checkin', 'method' => 'post', 'id' => 'session_registration', 'data-toggle' => 'validator'])) !!}
        {!! Form::hidden('eventID', $event->eventID) !!}
        {!! Form::hidden('orgID', $org->orgID) !!}
        {!! Form::hidden('sessionID', $session->sessionID) !!}
        <b>Session: {{ $session->sessionName }}</b>
        <p>&nbsp;</p>
        <div class="form-group has-feedback col-md-12 col-xs-12">
            {!! Form::label('regID', 'Please enter registration id number', array('class' => 'control-label')) !!}
            {!! Form::text('regID', '', $attributes = array('class'=>'form-control has-feedback-left', 'required')) !!}
            <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span>
        </div>
        <div class="form-group col-md-12 col-xs-12">
            {!! Form::submit('Submit & Return to Session List', array('class' => 'btn btn-primary', 'name' => 'list', 'value' => '1')) !!}
            {!! Form::submit('Submit & Register Another', array('class' => 'btn btn-success', 'name' => 'return', 'value' => '1')) !!}
        </div>
        {!! Form::close() !!}
    @endif

    @include('v1.parts.end_content')

@endsection