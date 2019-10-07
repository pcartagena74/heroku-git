<?php
/**
 * Comment: a No-Auth way to mark session attendance (by Volunteer) on behalf of attendee
 * Created: 7/5/2017
 *
 * Consider the following:
 *  - what if the sessionID is somehow incorrect... show options?
 *
 */
use App\EventSession;
use GrahamCampbell\Flysystem\Facades\Flysystem;

//use hisorange\BrowserDetect\Provider\BrowserDetectService;
//ini_set('memory_limit', '256M');

try {
    if ($event->showLogo && $org->orgLogo !== null) {
        $s3m = Flysystem::connection('s3_media');
        $logo_url = $s3m->getAdapter()->getClient()->getObjectURL(env('AWS_BUCKET3'), $org->orgPath . "/" . $org->orgLogo);
    }
} catch (\League\Flysystem\Exception $exception) {
    $logo_url = '';
}

?>
@extends('v1.layouts.no-auth_simple')

@section('content')

    @include('v1.parts.start_content', ['header' => trans('messages.buttons.chk_vol'), 'subheader' => '',
             'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @if($logo_url)
        <img src="{{ $logo_url }}" height="50">
    @endif
    <h2>@lang('messages.fields.event'): {{ $event->eventName }}</h2>

    <p>&nbsp;</p>

    @if($event->hasTracks > 0 && $session === null)

        @foreach($event->default_sessions() as $s)
            <div class="col-sm-12 col-xs-12">
                <a href="/checkin/{{ $event->eventID}}/{{ $s->sessionID }}"
                   style="white-space: normal;" class="btn btn-primary btn-sm">
                   {{ $s->sessionName }}
                </a>
            </div>
        @endforeach

        <div class="col-sm-12 col-xs-12">
            @foreach($tracks as $t)
                <div class="col-sm-3 col-xs-3">
                    <b>{{ $t->trackName }}</b>
                </div>
            @endforeach
        </div>

        @for($i=1;$i<=$event->confDays;$i++)
            <div class="form-group col-sm-12 col-xs-12">
                <div style="background-color:#2a3f54; color:yellow;" class="col-sm-{{ 3 * count($tracks) }} col-xs-{{ 3 * count($tracks) }}">
                    @lang('messages.headers.day') {{ $i }} @lang('messages.fields.sessions')
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
                        @foreach($tracks as $t)
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
        @if($session->sessionName != 'def_sess')
        <b>Session: {{ $session->sessionName }}</b>
        <p>&nbsp;</p>
        @endif
        <div class="form-group has-feedback col-md-12 col-xs-12">
            {!! Form::label('regID', trans('messages.headers.regID'), array('class' => 'control-label')) !!}
            {!! Form::text('regID', '', $attributes = array('class'=>'form-control has-feedback-left', 'required')) !!}
            <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span>
        </div>
        <div class="form-group col-md-12 col-xs-12">
            {!! Form::submit(trans('messages.headers.sub&').trans('messages.headers.ret_sess_list'),
                array('class' => 'btn btn-primary', 'name' => 'list', 'value' => '1')) !!}
            {!! Form::submit(trans('messages.headers.sub&').trans('messages.headers.reg_another'),
                array('class' => 'btn btn-success', 'name' => 'return', 'value' => '1')) !!}
        </div>
        {!! Form::close() !!}
    @endif

    @include('v1.parts.end_content')

@endsection