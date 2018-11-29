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

$person->user->password === null ? $set_pass = 1: $set_pass = 0;

if(count($person->emails) > 0){
    $x = '';
    foreach($person->emails as $e){
        $x .= "<li>$e->emailADDR</li>";
    }
    $email_list = trans('messages.instructions.pmi_emails', ['emails' => $x, 'pmiID' => $person->orgperson->OrgStat1]);
}

?>
@extends('v1.layouts.no-auth_simple')

@section('content')
    @include('v1.parts.start_content', ['header' => trans('messages.headers.acc_lookup'), 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @if($logo)
        <img src="{{ $logo }}" height="50">
    @endif

    <p>&nbsp;</p>

    @if($person)
        <div class="form-group col-xs-12">
            @lang('messages.instructions.pmiID_found', ['pmiID' => $person->orgperson->OrgStat1, 'id' => $person->personID,
                                                        'login' => $person->login, 'name' => $person->showFullName()])
            {!! trans_choice('messages.instructions.pmi_pass', $set_pass) !!}
            <p></p>
            {!! $email_list !!}
        </div>
    @else
        {!! Form::open((['url' => env('APP_URL').'/pmi_lookup/', 'method' => 'post', 'id' => 'pmiID_Lookup', 'data-toggle' => 'validator'])) !!}

        {!! Form::hidden('orgID', $org->orgID) !!}

        <div class="form-group has-feedback col-md-12 col-xs-12">
            {!! Form::label('pmiID', trans('messages.instructions.pmiID'), array('class' => 'control-label')) !!}
            {!! Form::number('pmiID', '', $attributes = array('class'=>'form-control has-feedback-left', 'required')) !!}
            <span class="fa fa-user form-control-feedback left" aria-hidden="true"></span>
        </div>
        <div class="form-group col-md-12 col-xs-12">
            {!! Form::submit(trans('messages.headers.acc_lookup'), array('class' => 'btn btn-primary')) !!}
        </div>

        {!! Form::close() !!}
    @endif
    @include('v1.parts.end_content')


@endsection