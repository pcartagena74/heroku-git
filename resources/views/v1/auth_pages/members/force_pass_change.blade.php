<?php
/**
 * Page to display change password form
 * Date: 3/24/2018
 */

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
<div class="col-sm-12">

    {{ html()->form('POST', env('APP_URL') . "/force_password")->open() }}
    <div class="form-group">
        {{ html()->label('User ID', 'userid')->class('control-label') }}
        {{ html()->password('userid')->attributes($attributes = array('class' => 'form-control', 'required')) }}
    </div>
    <div class="form-group">
        {{ html()->label('New Password', 'newPass')->class('control-label') }}
        {{ html()->password('password')->attributes($attributes = array('class' => 'form-control', 'required')) }}
    </div>
    <div class="form-group">
        {{ html()->label('Verify Password', 'password_confirmation')->class('control-label') }}
        {{ html()->password('password_confirmation')->attributes($attributes = array('class' => 'form-control', 'required')) }}
    </div>
    <div class="form-group">
        {{ html()->submit('Change Password')->class('btn btn-primary btn-sm') }}
    </div>
    {{ html()->form()->close() }}

</div>
@stop

