<?php
/**
 * Comment: New user creation utility for Admins
 *          Creates a new user for $this->currentPerson->defaultOrgID
 *
 * Created: 12/31/2018
 */

$topBits = '';  // remove this if this was set in the controller
$header = implode(" ", [trans('messages.nav.ad_new'), trans('messages.headers.for'), $org->orgName]);
$currentOrg = $org;

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('settings-management'))
        || Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))

        @include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

        {!! Form::open(array('url' => '/newuser/create', 'method' => 'post')) !!}

        <div class="form-group col-xs-12">
            <div class="col-xs-12">
            {!! Form::label('email', trans('messages.fields.email'), array('class' => 'control-label')) !!}
            {!! Form::text('email', null, array('class' => 'form-control input-sm', 'required')) !!}
            </div>
        </div>

        <div class="form-group col-xs-12">
            <div class="col-xs-6">
            {!! Form::label('firstName', trans('messages.fields.firstName'), array('class' => 'control-label')) !!}
            {!! Form::text('firstName', null, array('class' => 'form-control input-sm', 'required')) !!}
            </div>
            <div class="col-xs-6">
                {!! Form::label('lastName', trans('messages.fields.lastName'), array('class' => 'control-label')) !!}
                {!! Form::text('lastName', null, array('class' => 'form-control input-sm', 'required')) !!}
            </div>
        </div>

        <div class="form-group col-xs-12">
            <div class="col-xs-6">
                {!! Form::label('password', trans('messages.fields.password'), array('class' => 'control-label')) !!}
                {!! Form::text('password', null, array('class' => 'form-control input-sm', 'required')) !!}
            </div>
            <div class="col-xs-6">
                {!! Form::label('password_confirmation', trans('messages.headers.pass_ver'), array('class' => 'control-label')) !!}
                {!! Form::text('password_confirmation', null, array('class' => 'form-control input-sm', 'required')) !!}
            </div>
        </div>

        <div class="form-group col-xs-12">
            <div class="col-xs-12">
                {!! Form::label('pmiID', trans('messages.fields.pmi_id'), array('class' => 'control-label')) !!}
                {!! Form::text('pmiID', null, array('class' => 'form-control input-sm', 'placeholder' => trans('messages.headers.opt'))) !!}
            </div>
        </div>

        <div class="form-group col-xs-12">
            <div class="col-xs-3">
                {!! Form::submit(trans('messages.nav.ad_new'), array('class' => 'btn btn-primary', 'name' => 'sub_changes')) !!}
            </div>
        </div>
        {!! Form::close() !!}

        @include('v1.parts.end_content')
    @endif
@endsection

@section('scripts')
    @include('v1.parts.menu-fix', ['path' => '/newuser'])
@endsection

@section('footer')
@endsection
