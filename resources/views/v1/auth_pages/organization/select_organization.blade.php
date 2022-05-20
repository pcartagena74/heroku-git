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

            {!! Form::open(array('url' => url('update-default-org'), 'method' => 'POST')) !!}
            {!! Form::label('org', trans('messages.instructions.select_default_organization').':', array('class' => 'control-label')) !!}
            {!! Form::select('org', $org_list, $orgID, array('id' => 'eventID', 'class' =>'form-control')) !!}
            {!! Form::submit('Submit', array('class' => 'btn btn-primary btn-sm')) !!}
            {!! Form::close() !!}

    @include('v1.parts.end_content')

@endsection

@section('scripts')


@endsection
