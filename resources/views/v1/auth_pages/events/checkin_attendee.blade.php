@php
    /**
     * Comment: a No-Auth way to mark session attendance (by Volunteer) on behalf of attendee
     * Created: 7/5/2017
     *
     * Consider the following:
     *  - what if the sessionID is somehow incorrect... show options?
     *
     * @var $org: Org object
     */
    use App\Models\EventSession;
    use League\Flysystem;

    //use hisorange\BrowserDetect\Provider\BrowserDetectService;
    //ini_set('memory_limit', '256M');

    $logo_url = '';
    $logo_filename = $org->orgPath . "/" . $org->orgLogo;
    $s3name = select_bucket('m', config('APP_ENV'));

    try {
        if ($org->orgLogo !== null) {
            if (Storage::disk($s3name)->exists($logo_filename)) {
                $logo_url = Storage::disk($s3name)->url($logo_filename);
            }
        }
    } catch (Exception $e) {
        $logo_url = '';
    }

@endphp

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
                <div style="background-color:#2a3f54; color:yellow;"
                     class="col-sm-{{ 3 * count($tracks) }} col-xs-{{ 3 * count($tracks) }}">
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
        {{ html()->form('POST', config('APP_URL') . '/process_checkin')->id('session_registration')->data('toggle', 'validator')->open() }}
        {{ html()->hidden('eventID', $event->eventID) }}
        {{ html()->hidden('orgID', $org->orgID) }}
        {{ html()->hidden('sessionID', $session->sessionID) }}
        @if($session->sessionName != 'def_sess')
            <b>Session: {{ $session->sessionName }}</b>
            <p>&nbsp;</p>
        @endif
        <div class="form-group has-feedback col-md-12 col-xs-12">
            {{ html()->label(trans('messages.headers.regID'), 'regID')->class('control-label') }}
            {{ html()->text('regID', '')->attributes($attributes = array('class'=>'form-control has-feedback-left', 'required')) }}
            <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span>
        </div>
        <div class="form-group col-md-12 col-xs-12">
            {{ html()->submit(trans('messages.headers.sub&') . trans('messages.headers.ret_sess_list'))->class('btn btn-primary')->name('list')->value('1') }}
            {{ html()->submit(trans('messages.headers.sub&') . trans('messages.headers.reg_another'))->class('btn btn-success')->name('return')->value('1') }}
        </div>
        {{ html()->form()->close() }}
    @endif

    @include('v1.parts.end_content')

@endsection