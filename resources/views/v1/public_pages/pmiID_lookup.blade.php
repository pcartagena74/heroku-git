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
    if($person){
        $person->user->password === null ? $set_pass = 1: $set_pass = 0;

        if(count($person->emails) > 0){
            $x = '';
            foreach($person->emails as $e){
                $x .= "<li>$e->emailADDR</li>";
            }
            $email_list = trans('messages.instructions.pmi_emails', ['emails' => $x, 'pmiID' => $person->orgperson->OrgStat1, 'admin_email' => $org->adminEmail]);
        }
    }

@endphp

@extends('v1.layouts.no-auth_simple')

@section('content')
    @include('v1.parts.start_content', ['header' => trans('messages.headers.acc_lookup'), 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @if($logo)
        <img src="{{ $logo }}" height="50" alt="logo">
    @endif

    <p>&nbsp;</p>

    @if($person)
        <div class="form-group col-xs-12">
            @lang('messages.instructions.pmiID_found', ['pmiID' => $person->orgperson->OrgStat1, 'id' => $person->personID,
                                                        'login' => $person->login, 'name' => $person->showFullName()])
            {!! trans_choice('messages.instructions.pmi_pass', $set_pass, ['admin_email' => $org->adminEmail]) !!}
            <p></p>
            {!! $email_list !!}
        </div>
    @else
        {{ html()->form('POST', env('APP_URL') . '/pmi_lookup/')->id('pmiID_Lookup')->data('toggle', 'validator')->open() }}

        {{ html()->hidden('orgID', $org->orgID) }}

        <div class="form-group has-feedback col-md-12 col-xs-12">
            {{ html()->label(trans('messages.instructions.pmiID'), 'pmiID')->class('control-label') }}
            {{ html()->number('pmiID', '')->attributes($attributes = array('class'=>'form-control has-feedback-left', 'required', 'placeholder' => trans('messages.instructions.no_pmiID_zero'))) }}
            <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span>
        </div>
        <div class="form-group has-feedback col-md-12 col-xs-12">
            {{ html()->label(trans('messages.instructions.no_pmiID'), 'email')->class('control-label') }}
            {{ html()->text('email', '')->attributes($attributes = array('class'=>'form-control has-feedback-left')) }}
            <span class="fa fa-envelope form-control-feedback left" aria-hidden="true"></span>
        </div>
        <div class="form-group col-md-12 col-xs-12">
            {{ html()->submit(trans('messages.headers.acc_lookup'))->class('btn btn-primary') }}
        </div>

        {{ html()->form()->close() }}
    @endif
    @include('v1.parts.end_content')

@endsection