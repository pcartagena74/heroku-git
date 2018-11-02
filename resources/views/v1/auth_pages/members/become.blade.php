<?php
/**
 * Comment:
 * Created: 2/9/2017
 */

$topBits = '';  // remove this if this was set in the controller
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('header')
    @include('v1.parts.typeahead')
@endsection

@section('content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.admin_func').trans('messages.headers.person_sim'),
             'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
    <!-- stuff -->

    <div id="custom-template" class="col-sm-12">
        {!! Form::label('helper', trans('messages.instructions.become_instr'). ":") !!}<br/>
        {!! Form::text('helper', null, array('id' => 'helper', 'class' => 'typeahead input-xs')) !!}<br />
        <div id="search-results"></div>
    </div>

    <p>&nbsp;</p>

    <div class="col-sm-12">
        {!! Form::open(array('url' => env('APP_URL')."/become", 'method' => 'POST')) !!}
        <div class="form-group col-sm-12">
            {!! Form::label('new_id', trans('messages.instructions.become_id')) !!}<br/>
            {!! Form::text('new_id', '', array('class' => 'form-control', 'required')) !!}
        </div>
        <div class="form-group col-sm-1">
            {!! Form::submit('Become!', array('class' => 'btn btn-primary btn-xs form-control')) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @include('v1.parts.end_content')

@endsection

@section('scripts')
    <script src="{{ env('APP_URL') }}/js/typeahead.bundle.min.js"></script>
    <script>
        $(document).ready(function ($) {
            var people = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                remote: {
                    url: '{{ env('APP_URL') }}/autocomplete/?q=%QUERY',
                    wildcard: '%QUERY'
                }
            });

            $('#custom-template .typeahead').typeahead(null, {
                name: 'people',
                display: 'value',
                source: people
            });
        });
    </script>
@endsection