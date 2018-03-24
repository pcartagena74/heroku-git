<?php
/**
 * Page to display change password form
 * Date: 3/24/2018
 */

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
<div class="col-sm-12">

    {!! Form::open(array('url' => env('APP_URL')."/force_password", 'method' => 'POST')) !!}
    <div class="form-group">
        {!! Form::label('userid', 'User ID', array('class' => 'control-label')) !!}
        {!! Form::password('userid', $attributes = array('class' => 'form-control', 'required')) !!}
    </div>
    <div class="form-group">
        {!! Form::label('newPass', 'New Password', array('class' => 'control-label')) !!}
        {!! Form::password('password', $attributes = array('class' => 'form-control', 'required')) !!}
    </div>
    <div class="form-group">
        {!! Form::label('password_confirmation', 'Verify Password', array('class' => 'control-label')) !!}
        {!! Form::password('password_confirmation', $attributes = array('class' => 'form-control', 'required')) !!}
    </div>
    <div class="form-group">
        {!! Form::submit('Change Password', array('class' => 'btn btn-primary btn-sm')) !!}
    </div>
    {{-- current, new, verify --}}
    {!! Form::close() !!}

</div>
@stop

