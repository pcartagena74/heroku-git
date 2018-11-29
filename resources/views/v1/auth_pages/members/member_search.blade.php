<?php
/**
 * Comment: Use 'Become' UI to assist with Member Search Functionality
 * Created: 11/8/2018
 */

if($topBits === null){
    $topBits = '';
}
if ($mbr_list){

    $headers = ['#', 'Name', trans('messages.fields.pmi_id'), trans('messages.fields.classification'),
        trans('messages.fields.compName'), trans('messages.fields.title'), trans('messages.fields.indName'),
        trans('messages.fields.expr'), trans('messages.fields.buttons')];

    count($mbr_list) > 10 ? $scroll = 1 : $scroll = 0;

    foreach($mbr_list as $mbr) {

        $profile_form = "<a target='_new' href='". env('APP_URL') . "/profile/$mbr->personID' type='button' data-toggle='tooltip' data-placement='top'
                     title='" . trans('messages.tooltips.vep') . "' class='btn btn-xs btn-primary'><i class='far fa-fw fa-edit'></i></a>";

        if($mbr->cnt > 0) {
            $activity_form = "<div data-toggle='tooltip' data-placement='top' title='" . trans('messages.tooltips.va') . "'>
                          <button data-toggle='modal' class='btn btn-xs btn-success' data-target='#dynamic_modal'
                           data-target-id='" . $mbr->personID . "'><i class='far fa-fw fa-book'></i></button></div>";
        } else {
            $activity_form = '';
        }

        $merge_form = "<a href='" . env('APP_URL') . "/merge/p/$mbr->personID' data-toggle='tooltip' data-placement='top'
                    title='" . trans('messages.tooltips.mr') . "' class='btn btn-xs btn-warning'>
                   <i class='far fa-fw fa-code-branch'></i></a>";

        $mbr->cnt = $profile_form . $merge_form . $activity_form;
    }

    $data = collect($mbr_list);
}
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('header')
    @include('v1.parts.typeahead')
@endsection

@section('content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.person_search'),
             'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

    {!! Form::open(array('url' => env('APP_URL')."/search", 'method' => 'POST')) !!}
    <div id="custom-template" class="col-sm-12 form-group">
        {!! Form::label('string', trans('messages.instructions.mbr_search')) !!}<br/>
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

    @if($mbr_list)
        @include('v1.parts.start_content', ['header' => trans('messages.headers.person_search') . " " . trans('messages.headers.results'),
                 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

        @include('v1.parts.datatable', ['headers' => $headers, 'data' => $data->toArray(), 'id' => 'member_table', 'scroll' => $scroll])

        @include('v1.parts.end_content')

    @endif

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
    @include('v1.parts.footer-datatable')
    <script>
        $(document).ready(function() {
            $('#member_table').DataTable({
                "fixedHeader": true,
                "order": [[ 0, "asc" ]]
            });
        });
    </script>
    @include('v1.parts.menu-fix', array('path' => '/search'))
@endsection

@section('modals')
    @include('v1.modals.dynamic', ['header' => trans('messages.headers.mAct'), 'show_past' => 1])
@endsection
