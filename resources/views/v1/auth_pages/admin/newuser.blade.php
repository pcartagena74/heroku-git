@php
/**
 * Comment: New user creation utility for Admins
 *          Creates a new user for $this->currentPerson->defaultOrgID
 *
 * Created: 12/31/2018
 * @var $org
 */

$topBits = '';  // remove this if this was set in the controller
$header = implode(" ", [trans('messages.nav.ad_new'), trans('messages.headers.for'), $org->orgName]);
$currentOrg = $org;

@endphp
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @if((Entrust::can('settings-management'))
        || Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))

        @include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

        {!! Form::open(array('url' => env('APP_URL').'/newuser', 'method' => 'post')) !!}

        <div class="form-group col-xs-12{{ $errors->has('email') ? ' has-error' : '' }}">
            <div class="col-xs-12">
            {!! Form::label('email', trans('messages.fields.email'), array('class' => 'control-label')) !!}
            {!! Form::text('email', old('email'), array('class' => 'form-control input-sm', 'required', 'autofocus')) !!}
                @if ($errors->has('email'))
                    <span class="help-block red"><strong>{{ $errors->first('email') }}</strong></span>
                @endif
            </div>
        </div>

        <div class="form-group col-xs-12{{ $errors->has('email') ? ' has-error' : '' }}">
            <div class="col-xs-6">
            {!! Form::label('firstName', trans('messages.fields.firstName'), array('class' => 'control-label')) !!}
            {!! Form::text('firstName', old('firstName'), array('class' => 'form-control input-sm', 'required')) !!}
                @if ($errors->has('firstName'))
                    <span class="help-block red"><strong>{{ $errors->first('firstName') }}</strong></span>
                @endif
            </div>
            <div class="col-xs-6">
                {!! Form::label('lastName', trans('messages.fields.lastName'), array('class' => 'control-label')) !!}
                {!! Form::text('lastName', old('lastName'), array('class' => 'form-control input-sm', 'required')) !!}
                @if ($errors->has('lastName'))
                    <span class="help-block red"><strong>{{ $errors->first('lastName') }}</strong></span>
                @endif
            </div>
        </div>

        <div class="form-group col-xs-12{{ $errors->has('password') ? ' has-error' : '' }}">
            <div class="col-xs-6">
                {!! Form::label('password', trans('messages.fields.password'), array('class' => 'control-label')) !!}
                {!! Form::text('password', null, array('class' => 'form-control input-sm', 'required')) !!}
                @if ($errors->has('password'))
                    <span class="help-block red"><strong>{{ $errors->first('password') }}</strong></span>
                @endif
            </div>
            <div class="col-xs-6">
                {!! Form::label('password_confirmation', trans('messages.headers.pass_ver'), array('class' => 'control-label')) !!}
                {!! Form::text('password_confirmation', null, array('class' => 'form-control input-sm', 'required')) !!}
            </div>
        </div>

        <div class="form-group col-xs-12{{ $errors->has('email') ? ' has-error' : '' }}">
            <div class="col-xs-12">
                {!! Form::label('pmiID', trans('messages.fields.pmi_id'), array('class' => 'control-label')) !!}
                {!! Form::text('pmiID', old('pmiID'), array('class' => 'form-control input-sm', 'placeholder' => trans('messages.headers.opt'))) !!}
                @if ($errors->has('pmiID'))
                    <span class="help-block red"><strong>{{ $errors->first('pmiID') }}</strong></span>
                @endif
            </div>
        </div>

        <div class="form-group col-xs-12">
            <div class="col-xs-3">
                {!! Form::label('notify', trans('messages.headers.notify_user'), array('class' => 'control-label')) !!}<br />
                {!! Form::checkbox('notify', 1, false, ['class' => 'form-control flat input-sm']) !!}
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
