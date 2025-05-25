@php
// include in all forms:   {{ csrf_field() }}
@endphp
@extends('v1.layouts.no-auth_no-nav')
@section('content')
<div class="container" style="padding-top: 20px;">
    <h4>@lang('messages.public_marketing.main.open1')</h4>
    <h4>@lang('messages.public_marketing.main.open2')</h4>
    <div class="row">
        <div class="col-md-4 column_text">
            <h2>@lang('messages.public_marketing.main.mktg')</h2>
            @lang('messages.public_marketing.main.mktg_msg')
            <p><a class="btn btn-info" href="/mktg" role="button">@lang('messages.public_marketing.main.view') &raquo;</a></p>
        </div>
        <div class="col-md-4 column_text">
            <h2>@lang('messages.public_marketing.main.mail')</h2>
            @lang('messages.public_marketing.main.mail_msg')
            <p><a class="btn btn-info" href="/mail" role="button">@lang('messages.public_marketing.main.view') &raquo;</a></p>
        </div>
        <div class="col-md-4 column_text">
            <h2>@lang('messages.public_marketing.main.meet')</h2>
            @lang('messages.public_marketing.main.meet_msg')
            <p><a class="btn btn-info" href="/mtgs" role="button">@lang('messages.public_marketing.main.view') &raquo;</a></p>
        </div>
    </div>
</div>
@stop

@section('scripts')
@stop