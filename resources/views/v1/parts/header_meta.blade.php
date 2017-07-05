<?php
/**
 * Comment: Separating to make cleaner
 * Created: 5/28/2017
 */
?>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<meta name="description" content="Integrated Member Management, Email Marketing, Event Registration, Surveys">
<meta name="author" content="mCentric / Efcico Corporation">
<meta name="csrf-token" content="{{ csrf_token() }}" />
@if(Auth::user())
    @if(!auth()->user()->remember_token)
        <meta http-equiv="refresh" content="3600;url={{ env('APP_URL') . "/logout" }}" />
    @endif
@endif
<link rel="icon" href="{{ env('APP_URL') }}/images/mCentric.ico">
<base href="{{ env('APP_URL') }}">
