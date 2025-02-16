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

        @lang('messages.instructions.new_user')
        {{ html()->form('POST', env('APP_URL') . '/newuser')->open() }}

        <div class="form-group col-xs-12{{ $errors->has('email') ? ' has-error' : '' }}">
            <div class="col-xs-12">
            {{ html()->label(trans('messages.fields.email'), 'email')->class('control-label') }}
            {{ html()->text('email', old('email'))->class('form-control input-sm')->required()->autofocus() }}
                @if ($errors->has('email'))
                    <span class="help-block red"><strong>{{ $errors->first('email') }}</strong></span>
                @endif
            </div>
        </div>

        <div class="form-group col-xs-12{{ $errors->has('email') ? ' has-error' : '' }}">
            <div class="col-xs-6">
            {{ html()->label(trans('messages.fields.firstName'), 'firstName')->class('control-label') }}
            {{ html()->text('firstName', old('firstName'))->class('form-control input-sm')->required() }}
                @if ($errors->has('firstName'))
                    <span class="help-block red"><strong>{{ $errors->first('firstName') }}</strong></span>
                @endif
            </div>
            <div class="col-xs-6">
                {{ html()->label(trans('messages.fields.lastName'), 'lastName')->class('control-label') }}
                {{ html()->text('lastName', old('lastName'))->class('form-control input-sm')->required() }}
                @if ($errors->has('lastName'))
                    <span class="help-block red"><strong>{{ $errors->first('lastName') }}</strong></span>
                @endif
            </div>
        </div>

        <div class="form-group col-xs-12{{ $errors->has('password') ? ' has-error' : '' }}">
            <div class="col-xs-6">
                {{ html()->label(trans('messages.fields.password'), 'password')->class('control-label') }}
                {{ html()->text('password')->class('form-control input-sm')->required() }}
                @if ($errors->has('password'))
                    <span class="help-block red"><strong>{{ $errors->first('password') }}</strong></span>
                @endif
            </div>
            <div class="col-xs-6">
                {{ html()->label(trans('messages.headers.pass_ver'), 'password_confirmation')->class('control-label') }}
                {{ html()->text('password_confirmation')->class('form-control input-sm')->required() }}
            </div>
        </div>

        <div class="form-group col-xs-12{{ $errors->has('email') ? ' has-error' : '' }}">
            <div class="col-xs-12">
                {{ html()->label(trans('messages.fields.pmi_id'), 'pmiID')->class('control-label') }}
                {{ html()->text('pmiID', old('pmiID'))->class('form-control input-sm')->placeholder(trans('messages.headers.opt')) }}
                @if ($errors->has('pmiID'))
                    <span class="help-block red"><strong>{{ $errors->first('pmiID') }}</strong></span>
                @endif
            </div>
        </div>

        <div class="form-group col-xs-12">
            <div class="col-xs-3">
                {{ html()->label(trans('messages.headers.notify_user'), 'notify')->class('control-label') }}<br />
                {{ html()->checkbox('notify', false, 1)->class('form-control flat input-sm') }}
            </div>
        </div>

        <div class="form-group col-xs-12">
            <div class="col-xs-3">
                {{ html()->submit(trans('messages.nav.ad_new'))->class('btn btn-primary')->name('sub_changes') }}
            </div>
        </div>
        {{ html()->form()->close() }}

        @include('v1.parts.end_content')
    @endif
@endsection

@section('scripts')
    @include('v1.parts.menu-fix', ['path' => '/newuser'])
@endsection

@section('footer')
@endsection
