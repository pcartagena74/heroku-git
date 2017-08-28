<?php
/**
 * Comment:
 * Created: 2/9/2017
 */

$topBits = '';  // remove this if this was set in the controller
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
    @include('v1.parts.typeahead')

    @include('v1.parts.start_content', ['header' => 'Chapter Event Attendance', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
    <!-- stuff -->

    <div id="custom-template" class="col-sm-12">
        {!! Form::label('helper', 'Find an ID:') !!}<br/>
        {!! Form::text('helper', null, array('id' => 'helper', 'class' => 'typeahead input-xs')) !!}<br />
        <div id="search-results"></div>
    </div>

    <p>&nbsp;</p>

    <div class="col-sm-12">
        {!! Form::open(array('url' => env('APP_URL')."/become", 'method' => 'POST')) !!}
        {!! Form::text('new_id', '', array('class' => 'form-control')) !!}
        {!! Form::submit('Become!', array('class' => 'btn btn-primary btn-xs')) !!}
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