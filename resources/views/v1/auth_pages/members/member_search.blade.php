@php
    /**
     * Comment: Member Search Functionality
     * Created: 11/8/2018
     *
     * @var $topBits
     * @var $mbr_srch
     *
     */

    if($topBits === null){
        $topBits = '';
    }
    if ($mbr_srch){

        $headers = ['#', trans('messages.fields.name'), trans('messages.fields.pmi_id'), trans('messages.fields.classification'),
            trans('messages.fields.compName'), trans('messages.fields.title'), trans('messages.fields.indName'),
            trans('messages.fields.expr'), trans('messages.fields.buttons')];

        count($mbr_srch) > 10 ? $scroll = 1 : $scroll = 0;

        foreach($mbr_srch as $mbr) {

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

            $become_form = Form::open(array('url' => '/become', 'method' => 'POST', 'target' => '_blank'));
            $become_form .= Form::hidden('new_id', $mbr->personID);
            $submit_button = "<i class='fas fa-fw fa-user'></i>";
            $become_form .= '<button class="btn btn-xs btn-danger" title="' . trans('messages.nav.ms_become').
                            '" data-toggle="tooltip" onclick="return confirm(\'' . trans('messages.tooltips.sure_become') .
                            '\');">'. $submit_button . '</button>';
            $become_form .= Form::close();

            //$mbr->cnt = $profile_form . $merge_form . $activity_form . $become_form;
            $mbr->cnt = view('v1.parts.mbr_buttons', ['p' => $mbr->personID, 'c' => $mbr->cnt])->render();
        }

        $data = collect($mbr_srch);
    } else {
        $scroll = 0;
    }
@endphp

@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('header')
    @include('v1.parts.typeahead')
@endsection

@section('content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.person_search'),
             'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

    {{ html()->form('POST', env('APP_URL') . "/search")->data('toggle', 'validator')->open() }}
    <div id="custom-template" class="col-sm-12 form-group">
        <b>{!! trans('messages.instructions.mbr_search') !!}</b>
        <div class="col-xs-2">
            {{ html()->text('search_box')->id('helper')->class('typeahead input-xs') }}<br/>
        </div>
        <div id="search-results"></div>
    </div>
    <div class="col-sm-12">
        <div class="col-xs-2">
            {{ html()->submit(trans('messages.headers.person_search'))->class('btn btn-primary btn-xs form-control') }}
        </div>
    </div>
    {{ html()->form()->close() }}

    @include('v1.parts.end_content')

    @if($mbr_srch)
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
                    url: '{{ env('APP_URL') }}/autocomplete/?l=p&q=%QUERY',
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
    @if($scroll)
        <script>
            $(document).ready(function () {
                $('#member_table').DataTable({
                    "fixedHeader": true,
                    "order": [[0, "asc"]]
                });
            });
        </script>
    @endif
    @include('v1.parts.menu-fix', array('path' => 'search'))
@endsection

@section('modals')
    @include('v1.modals.dynamic', ['header' => trans('messages.headers.mAct'), 'url' => 'activity'])
    {{--
    @include('v1.modals.context_sensitive_issue')
    --}}
@endsection
