@php

/**
 * Comment: Create New Organization and associate it with a user.
 *
 * Created: 18/02/2020
 */

$topBits = '';  // remove this if this was set in the controller
$header = implode(" ", [trans('messages.nav.o_create')]);

@endphp
@extends('v1.layouts.auth', ['topBits' => $topBits])
@section('header')
    @include('v1.parts.typeahead')
@endsection

@section('content')

    @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
        @include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

        {{ html()->form('POST', url('save_organization'))->open() }}
<div class="form-group col-xs-12">
    <div class="col-xs-4">
        {{ html()->label(trans('messages.fields.org_name'), 'orgName')->class('control-label') }}
            {{ html()->text('orgName', old('orgName'))->class('form-control input-sm')->required() }}
                @if ($errors->has('orgName'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgName') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-4">
        {{ html()->label(trans('messages.fields.org_path') . '*', 'orgPath')->class('control-label') }}
            {{ html()->text('orgPath', old('orgPath'))->class('form-control input-sm')->required() }}
                @if ($errors->has('orgPath'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgPath') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-4">
        {{ html()->label(trans('messages.fields.formal_name'), 'formalName')->class('control-label') }}
                {{ html()->text('formalName', old('formalName'))->class('form-control input-sm') }}
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
        {{ html()->label(trans('messages.fields.org_addr1'), 'orgAddr1')->class('control-label') }}
            {{ html()->text('orgAddr1', old('orgAddr1'))->class('form-control input-sm') }}
                @if ($errors->has('orgAddr1'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgAddr1') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-6">
        {{ html()->label(trans('messages.fields.org_addr2'), 'orgAddr2')->class('control-label') }}
                {{ html()->text('orgAddr2', old('orgAddr2'))->class('form-control input-sm') }}
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
        {{ html()->label(trans('messages.fields.city'), 'orgCity')->class('control-label') }}
            {{ html()->text('orgCity', old('orgCity'))->class('form-control input-sm') }}
                @if ($errors->has('orgCity'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgCity') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-4">
        {{ html()->label(trans('messages.fields.state'), 'orgState')->class('control-label') }}
                {{ html()->text('orgState', old('orgState'))->class('form-control input-sm')->maxlength('2') }}
                @if ($errors->has('orgState'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgState') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-4">
        {{ html()->label(trans('messages.fields.zip'), 'orgZip')->class('control-label') }}
            {{ html()->text('orgZip', old('orgZip'))->class('form-control input-sm') }}
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
        {{ html()->label(trans('messages.fields.main_email'), 'orgEmail')->class('control-label') }}
                {{ html()->text('orgEmail', old('orgEmail'))->class('form-control input-sm') }}
                @if ($errors->has('orgEmail'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgEmail') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-3">
        {{ html()->label(trans('messages.fields.main_number'), 'orgPhone')->class('control-label') }}
                {{ html()->text('orgPhone', old('orgPhone'))->class('form-control input-sm') }}
                @if ($errors->has('orgPhone'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgPhone') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-3">
        {{ html()->label(trans('messages.fields.org_fax'), 'orgFax')->class('control-label') }}
                {{ html()->text('orgFax')->class('form-control input-sm') }}
                @if ($errors->has('orgFax'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgFax') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-3">
        {{ html()->label(trans('messages.fields.admin_email'), 'adminEmail')->class('control-label') }}
                {{ html()->text('adminEmail')->class('form-control input-sm') }}
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
        {{ html()->label(trans('messages.fields.facebook_url'), 'facebookURL')->class('control-label') }}
                {{ html()->text('facebookURL', old('facebookURL'))->class('form-control input-sm') }}
                @if ($errors->has('facebookURL'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('facebookURL') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-3">
        {{ html()->label(trans('messages.fields.org_website'), 'orgURL')->class('control-label') }}
                {{ html()->text('orgURL', old('orgURL'))->class('form-control input-sm') }}
                @if ($errors->has('orgURL'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('orgURL') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-3">
        {{ html()->label(trans('messages.fields.credit_label'), 'creditLabel')->class('control-label') }}
                {{ html()->text('creditLabel', 'PDU')->class('form-control input-sm')->required() }}
                @if ($errors->has('creditLabel'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('creditLabel') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-3">
        {{ html()->label(trans('messages.fields.twitter_handle'), 'orgHandle')->class('control-label') }}
                {{ html()->text('orgHandle')->class('form-control input-sm') }}
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
        {{ html()->label(trans('messages.fields.admin_contact_statement'), 'adminContactStatement')->class('control-label') }}
                {{ html()->text('adminContactStatement', old('adminContactStatement'))->class('form-control input-sm')->placeholder(trans('messages.headers.opt')) }}
                @if ($errors->has('adminContactStatement'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('adminContactStatement') }}
            </strong>
        </span>
        @endif
    </div>
    <div class="col-xs-6">
        {{ html()->label(trans('messages.fields.tech_contact_statement'), 'techContactStatement')->class('control-label') }}
                {{ html()->text('techContactStatement', old('techContactStatement'))->class('form-control input-sm')->placeholder(trans('messages.headers.opt')) }}
                @if ($errors->has('techContactStatement'))
        <span class="help-block red">
            <strong>
                {{ $errors->first('techContactStatement') }}
            </strong>
        </span>
        @endif
    </div>
</div>
<div class="form-group col-xs-12{{$errors->has('create_user') ? ' has-error' : '' }}">
    <div class="col-xs-6" style="margin-left: 37%;font-size: 20px;">
        <label class="control-label">
            <a class="active" href="javascript:void(0)" id="select_user_link" onclick="toggleCreateUser(2)">
                {!! trans('messages.headers.select_user') !!}
            </a>
        </label>
        OR
        <label class="control-label">
            <a href="javascript:void(0)" id="create_user_link" onclick="toggleCreateUser(1)">
                {!! trans('messages.headers.create_user') !!}
            </a>
        </label>
        <br/>
        <input id="create_user_checkbox" name="create_user" type="hidden" value=""/>
    </div>
</div>
<div class="form-group col-xs-12{{ $errors->has('existing_user') ? ' has-error' : '' }}" id="custom-template" style="display: none;">
    {{ html()->label(trans('messages.headers.select_user_hint'), 'select_user')->class('control-label') }}
        {{ html()->text('existing_user')->id('helper')->class('typeahead input-xs') }}
    <div id="search-results">
    </div>
    @if ($errors->has('existing_user'))
    <span class="help-block red">
        <strong>
            {{ $errors->first('existing_user') }}
        </strong>
    </span>
    @endif
</div>
<div id="create_user" style="display: none;">
    <h2>
        Create New User
    </h2>
    <div class="form-group col-xs-12{{ $errors->has('email') ? ' has-error' : '' }}">
        <div class="col-xs-12">
            {{ html()->label(trans('messages.fields.email') . '*', 'email')->class('control-label') }}
            {{ html()->text('email', old('email'))->class('form-control input-sm')->required()->autofocus() }}
                @if ($errors->has('email'))
            <span class="help-block red">
                <strong>
                    {{ $errors->first('email') }}
                </strong>
            </span>
            @endif
        </div>
    </div>
    <div class="form-group col-xs-12{{ $errors->has('firstName') ? ' has-error' : '' }}">
        <div class="col-xs-6">
            {{ html()->label(trans('messages.fields.firstName') . '*', 'firstName')->class('control-label') }}
            {{ html()->text('firstName', old('firstName'))->class('form-control input-sm')->required() }}
                @if ($errors->has('firstName'))
            <span class="help-block red">
                <strong>
                    {{ $errors->first('firstName') }}
                </strong>
            </span>
            @endif
        </div>
        <div class="col-xs-6">
            {{ html()->label(trans('messages.fields.lastName') . '*', 'lastName')->class('control-label') }}
                {{ html()->text('lastName', old('lastName'))->class('form-control input-sm')->required() }}
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
            {{ html()->label(trans('messages.fields.password') . '*', 'password')->class('control-label') }}
                {{ html()->text('password')->class('form-control input-sm')->required() }}
                @if ($errors->has('password'))
            <span class="help-block red">
                <strong>
                    {{ $errors->first('password') }}
                </strong>
            </span>
            @endif
        </div>
        <div class="col-xs-6">
            {{ html()->label(trans('messages.headers.pass_ver') . '*', 'password_confirmation')->class('control-label') }}
                {{ html()->text('password_confirmation')->class('form-control input-sm')->required() }}
        </div>
    </div>
    <div class="form-group col-xs-12{{ $errors->has('pmiID') ? ' has-error' : '' }}">
        <div class="col-xs-12">
            {{ html()->label(trans('messages.fields.pmi_id'), 'pmiID')->class('control-label') }}
                {{ html()->text('pmiID', old('pmiID'))->class('form-control input-sm') }}
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
            {{ html()->label(trans('messages.headers.notify_user'), 'notify')->class('control-label') }}
            <br/>
            {{ html()->checkbox('notify', false, 1)->class('form-control flat input-sm') }}
        </div>
    </div>
</div>
<div class="form-group col-xs-12">
    <div class="col-xs-3">
        {{ html()->submit(trans('messages.nav.ad_new_org'))->class('btn btn-primary')->name('sub_changes') }}
    </div>
</div>
{{ html()->form()->close() }}

        @include('v1.parts.end_content')
    @endif
@endsection

@section('scripts')
    @include('v1.parts.menu-fix', ['path' => '/newuser'])
<script src="{{ env('APP_URL') }}/js/typeahead.bundle.min.js">
</script>
<script>
    function toggleCreateUser(type) {
            if(type == 1){
                $('#custom-template').hide();
                $('#create_user').show();
                $('#select_user_link').addClass('active');
                $('#create_user_link').removeClass('active');
                $('#create_user_checkbox').val(1);
                $('#create_user input[type=text]').each(function(){
                    if($(this).attr('name') != 'pmiID'){
                        $(this).attr('required','true');
                    }
                });
            } else {
                $('#custom-template').show();
                $('#create_user').hide();       
                $('#select_user_link').removeClass('active');
                $('#create_user_link').addClass('active');
                $('#create_user_checkbox').val(0);
                 $('#create_user input[type=text]').each(function(){
                    $(this).removeAttr('required');
                });
            }
        }
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
            if(create_user_input === ''){
                // toggleCreateUser(0);
            } else {
                if(create_user_input == 0) {
                    toggleCreateUser(0);
                }
                else {
                     toggleCreateUser(1);
                }

            }

            
            $('#toggleCreateUser').on('click', function() {
                console.log('here');
                // if(event.target.checked == true){
                //     $('#custom-template').hide();
                //     $('#create_user').show();
                //     $('#create_user input[type=text]').each(function(){
                //         $(this).attr('required','true');
                //     });
                // } else {
                //     $('#create_user').hide();
                //     $('#custom-template').show();
                //     $('#create_user input[type=text]').each(function(){
                //         $(this).removeAttr('required');
                //     });
                // }
            });

        });
</script>
@endsection

@section('footer')
@endsection
