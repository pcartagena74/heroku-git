@php

    /**
     * Comment: a No-Auth way to mark session attendance
     * Created: 7/5/2017
     *
     * Consider the following:
     *  - what if the sessionID is somehow incorrect... show options?
     *
     * @var $org: Org object
     * @var $person: Person object
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
@endphp

@extends('v1.layouts.no-auth_simple')

@section('content')
    @include('v1.parts.start_content', ['header' => trans('messages.headers.rec_sess_att'), 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @if($event->showLogo && $logo)
        <img src="{{ $logo }}" height="50">
    @endif
    <h2>@lang('messages.fields.event'): {{ $event->eventName }}</h2>
    <b>@lang('messages.fields.session'): {{ $session->sessionName }}</b>

    <p>&nbsp;</p>
    {{ html()->form('POST', env('APP_URL') . '/rs/' . $session->sessionID . '/edit')->id('session_registration')->data('toggle', 'validator')->open() }}

    {{ html()->hidden('eventID', $event->eventID) }}
    {{ html()->hidden('orgID', $org->orgID) }}

    <div class="form-group has-feedback col-md-12 col-xs-12">
        {{ html()->label(trans('messages.headers.regID'), 'regID')->class('control-label') }}
        {{ html()->text('regID', '')->attributes($attributes = array('class'=>'form-control has-feedback-left', 'required')) }}
        <span class="fas fa-user form-control-feedback left" aria-hidden="true"></span>
    </div>
    <div class="form-group col-md-12 col-xs-12">
        {{ html()->submit(trans('messages.headers.sub_sess_att'))->class('btn btn-primary') }}
    </div>

    {{ html()->form()->close() }}
    @include('v1.parts.end_content')

@endsection