<?php
/**
 * Comment: Use 'Become' UI to assist with Member Search Functionality
 * Created: 11/8/2018
 */

$topBits = '';  // remove this if this was set in the controller
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('header')
    @include('v1.parts.typeahead')
@endsection

@section('content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.admin_func').trans('messages.headers.person_search'),
             'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
    <!-- stuff -->

    {!! Form::open(array('url' => env('APP_URL')."/search", 'method' => 'POST')) !!}
    <div id="custom-template" class="col-sm-12 form-group">
        {!! Form::label('string', trans('messages.instructions.mbr_search'). ":") !!}<br/>
        {!! Form::text('string', null, array('id' => 'helper', 'class' => 'typeahead input-xs')) !!}<br />
        <div id="search-results"></div>
    </div>
    <div class="col-sm-12 form-group">
        <div class="col-xs-1">
    {!! Form::submit(trans('messages.headers.person_search'), array('class' => 'btn btn-primary btn-xs form-control')) !!}
        </div>
    </div>
    {!! Form::close() !!}

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