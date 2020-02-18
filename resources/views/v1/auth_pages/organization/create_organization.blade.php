<?php
/**
 * Comment: Create New Organization and associate it with a user.
 *
 * Created: 18/02/2020
 */

$topBits = '';  // remove this if this was set in the controller
$header = implode(" ", [trans('messages.nav.o_create')]);

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])
@section('header')
    @include('v1.parts.typeahead')
@endsection

@section('content')

    @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
        @include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

        {!! Form::open(array('url' => url('save_organization'), 'method' => 'post')) !!}
<div class="form-group col-xs-12">
    <div class="col-xs-4">
        {!! Form::label('orgName', trans('messages.fields.org_name'), array('class' => 'control-label')) !!}
            {!! Form::text('orgName', old('orgName'), array('class' => 'form-control input-sm', 'required')) !!}
                @if ($errors->has('orgName'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgName') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-4">
        {!! Form::label('orgPath', trans('messages.fields.org_path'), array('class' => 'control-label')) !!}
            {!! Form::text('orgPath', old('orgPath'), array('class' => 'form-control input-sm', 'required')) !!}
                @if ($errors->has('orgPath'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgPath') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-4">
        {!! Form::label('formalName', trans('messages.fields.formal_name'), array('class' => 'control-label')) !!}
                {!! Form::text('formalName', old('formalName'), array('class' => 'form-control input-sm')) !!}
                @if ($errors->has('formalName'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('formalName') }}
            </strong>
        </span>
        @endif
    </div>
</div>
<div class="form-group col-xs-12">
    <div class="col-xs-6">
        {!! Form::label('orgAddr1', trans('messages.fields.org_addr1'), array('class' => 'control-label')) !!}
            {!! Form::text('orgAddr1', old('orgAddr1'), array('class' => 'form-control input-sm')) !!}
                @if ($errors->has('orgAddr1'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgAddr1') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-6">
        {!! Form::label('orgAddr2', trans('messages.fields.org_addr2'), array('class' => 'control-label')) !!}
                {!! Form::text('orgAddr2', old('orgAddr2'), array('class' => 'form-control input-sm')) !!}
                @if ($errors->has('orgAddr2'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgAddr2') }}
            </strong>
        </span>
        @endif
    </div>
</div>
<div class="form-group col-xs-12">
    <div class="col-xs-4">
        {!! Form::label('orgCity', trans('messages.fields.city'), array('class' => 'control-label')) !!}
            {!! Form::text('orgCity', old('orgCity'), array('class' => 'form-control input-sm')) !!}
                @if ($errors->has('orgCity'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgCity') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-4">
        {!! Form::label('orgState', trans('messages.fields.state'), array('class' => 'control-label')) !!}
                {!! Form::text('orgState', old('orgState'), array('class' => 'form-control input-sm')) !!}
                @if ($errors->has('orgState'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgState') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-4">
        {!! Form::label('orgZip', trans('messages.fields.zip'), array('class' => 'control-label')) !!}
            {!! Form::text('orgZip', old('orgZip'), array('class' => 'form-control input-sm')) !!}
                @if ($errors->has('orgZip'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgZip') }}
            </strong>
        </span>
        @endif
    </div>
</div>
<div class="form-group col-xs-12">
    <div class="col-xs-3">
        {!! Form::label('orgEmail', trans('messages.fields.main_email'), array('class' => 'control-label')) !!}
                {!! Form::text('orgEmail', old('orgEmail'), array('class' => 'form-control input-sm')) !!}
                @if ($errors->has('orgEmail'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgEmail') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-3">
        {!! Form::label('orgPhone', trans('messages.fields.main_number'), array('class' => 'control-label')) !!}
                {!! Form::text('orgPhone', old('orgPhone'), array('class' => 'form-control input-sm')) !!}
                @if ($errors->has('orgPhone'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgPhone') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-3">
        {!! Form::label('orgFax', trans('messages.fields.org_fax'), array('class' => 'control-label')) !!}
                {!! Form::text('orgFax', null, array('class' => 'form-control input-sm')) !!}
                @if ($errors->has('orgFax'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgFax') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-3">
        {!! Form::label('adminEmail', trans('messages.fields.admin_email'), array('class' => 'control-label')) !!}
                {!! Form::text('adminEmail', null, array('class' => 'form-control input-sm')) !!}
                @if ($errors->has('adminEmail'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('adminEmail') }}
            </strong>
        </span>
        @endif
    </div>
</div>
<div class="form-group col-xs-12">
    <div class="col-xs-3">
        {!! Form::label('facebookURL', trans('messages.fields.facebook_url'), array('class' => 'control-label')) !!}
                {!! Form::text('facebookURL', old('facebookURL'), array('class' => 'form-control input-sm')) !!}
                @if ($errors->has('facebookURL'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('facebookURL') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-3">
        {!! Form::label('orgURL', trans('messages.fields.org_website'), array('class' => 'control-label')) !!}
                {!! Form::text('orgURL', old('orgURL'), array('class' => 'form-control input-sm')) !!}
                @if ($errors->has('orgURL'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgURL') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-3">
        {!! Form::label('creditLabel', trans('messages.fields.credit_label'), array('class' => 'control-label')) !!}
                {!! Form::text('creditLabel', null, array('class' => 'form-control input-sm','required')) !!}
                @if ($errors->has('creditLabel'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('creditLabel') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-3">
        {!! Form::label('orgHandle', trans('messages.fields.twitter_handle'), array('class' => 'control-label')) !!}
                {!! Form::text('orgHandle', null, array('class' => 'form-control input-sm')) !!}
                @if ($errors->has('orgHandle'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgHandle') }}
            </strong>
        </span>
        @endif
    </div>
</div>
<div class="form-group col-xs-12">
    <div class="col-xs-6">
        {!! Form::label('adminContactStatement', trans('messages.fields.admin_contact_statement'), array('class' => 'control-label')) !!}
                {!! Form::text('adminContactStatement', old('adminContactStatement'), array('class' => 'form-control input-sm', 'placeholder' => trans('messages.headers.opt'))) !!}
                @if ($errors->has('adminContactStatement'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('adminContactStatement') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-6">
        {!! Form::label('techContactStatement', trans('messages.fields.tech_contact_statement'), array('class' => 'control-label')) !!}
                {!! Form::text('techContactStatement', old('techContactStatement'), array('class' => 'form-control input-sm', 'placeholder' => trans('messages.headers.opt'))) !!}
                @if ($errors->has('techContactStatement'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('techContactStatement') }}
            </strong>
        </span>
        @endif
    </div>
</div>
<div class="form-group col-xs-12" id="custom-template">
    {!! Form::label('select_user', trans('messages.headers.select_user'), array('class' => 'control-label')) !!}
    
        {!! Form::text('existing_user', null, array('id' => 'helper', 'class' => 'typeahead input-xs')) !!}
    <div id="search-results">
    </div>
</div>
<div class="form-group col-xs-12">
    <div class="col-xs-3">
        {!! Form::label('create_user', trans('messages.headers.create_user'), array('class' => 'control-label')) !!}
        <br/>
        {!! Form::checkbox('create_user', 1, old('create_user'), ['class' => 'form-control flat input-sm i-checks','id'=>'toggleCreateUser']) !!}
    </div>
</div>
<div id="create_user" style="display: none;">
    <h2>
        Create New User
    </h2>
    <div class="form-group col-xs-12{{ $errors->has('email') ? ' has-error' : '' }}">
        <div class="col-xs-12">
            {!! Form::label('email', trans('messages.fields.email'), array('class' => 'control-label')) !!}
            {!! Form::text('email', old('email'), array('class' => 'form-control input-sm', 'required', 'autofocus')) !!}
                @if ($errors->has('email'))
            <span class="help-block red">
                <strong>
                    {{ $errors->first('email') }}
                </strong>
            </span>
            @endif
        </div>
    </div>
    <div class="form-group col-xs-12{{ $errors->has('email') ? ' has-error' : '' }}">
        <div class="col-xs-6">
            {!! Form::label('firstName', trans('messages.fields.firstName'), array('class' => 'control-label')) !!}
            {!! Form::text('firstName', old('firstName'), array('class' => 'form-control input-sm', 'required')) !!}
                @if ($errors->has('firstName'))
            <span class="help-block red">
                <strong>
                    {{ $errors->first('firstName') }}
                </strong>
            </span>
            @endif
        </div>
        <div class="col-xs-6">
            {!! Form::label('lastName', trans('messages.fields.lastName'), array('class' => 'control-label')) !!}
                {!! Form::text('lastName', old('lastName'), array('class' => 'form-control input-sm', 'required')) !!}
                @if ($errors->has('lastName'))
            <span class="help-block red">
                <strong>
                    {{ $errors->first('lastName') }}
                </strong>
            </span>
            @endif
        </div>
    </div>
    <div class="form-group col-xs-12{{ $errors->has('password') ? ' has-error' : '' }}">
        <div class="col-xs-6">
            {!! Form::label('password', trans('messages.fields.password'), array('class' => 'control-label')) !!}
                {!! Form::text('password', null, array('class' => 'form-control input-sm', 'required')) !!}
                @if ($errors->has('password'))
            <span class="help-block red">
                <strong>
                    {{ $errors->first('password') }}
                </strong>
            </span>
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
                {!! Form::text('pmiID', old('pmiID'), array('class' => 'form-control input-sm')) !!}
                @if ($errors->has('pmiID'))
            <span class="help-block red">
                <strong>
                    {{ $errors->first('pmiID') }}
                </strong>
            </span>
            @endif
        </div>
    </div>
    <div class="form-group col-xs-12">
        <div class="col-xs-3">
            {!! Form::label('notify', trans('messages.headers.notify_user'), array('class' => 'control-label')) !!}
            <br/>
            {!! Form::checkbox('notify', 1, false, ['class' => 'form-control flat input-sm']) !!}
        </div>
    </div>
</div>
<script src="{{ env('APP_URL') }}/js/typeahead.bundle.min.js">
</script>
<script>
    $(document).ready(function ($) {
            var people = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                remote: {
                    url: '{{ env('APP_URL') }}/autocomplete/?l=p&q=%QUERY',
                    wildcard: '%QUERY'
                }
            });

            $('#custom-template .typeahead').typeahead(null, {
                name: 'people',
                display: 'value',
                source: people
            });
            // function toggleCreateUser(){
            //     $('#create_user').toggle();
            // }
            $('#create_user input[type=text]').each(function(){
                $(this).removeAttr('required');  
            });
            let create_user_input = '{{old('create_user')}}';
            if(create_user_input){
                $('#custom-template').hide();
                $('#create_user').show();
            } else {
                $('#custom-template').show();
                $('#create_user').hide();
            }
            $('.i-checks').on('ifChanged', function(event) {
                if(event.target.checked == true){
                    $('#custom-template').hide();
                    $('#create_user').show();
                    $('#create_user input[type=text]').each(function(){
                        $(this).attr('required','true');
                    });
                } else {
                    $('#create_user').hide();
                    $('#custom-template').show();
                    $('#create_user input[type=text]').each(function(){
                        $(this).removeAttr('required');
                    });
                }
            });

        });
</script>
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
