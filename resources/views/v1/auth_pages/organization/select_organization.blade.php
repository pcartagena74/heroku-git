@php
/**
 * Comment:
 * Created: 2/9/2017
 */

$topBits = '';  // remove this if this was set in the controller
@endphp
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('header')
    @include('v1.parts.typeahead')
@endsection

@section('content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.select_organization'),
             'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

            {{ html()->form('POST', url('update-default-org'))->open() }}
            {{ html()->label(trans('messages.instructions.select_default_organization') . ':', 'org')->class('control-label') }}
            {{ html()->select('org', $org_list, $orgID)->id('eventID')->class('form-control') }}
            {{ html()->submit('Submit')->class('btn btn-primary btn-sm') }}
            {{ html()->form()->close() }}

    @include('v1.parts.end_content')

@endsection

@section('scripts')


@endsection
