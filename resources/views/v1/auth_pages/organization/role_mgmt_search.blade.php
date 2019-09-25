<?php
/**
 * Comment: Member Search Functionality for Role Management
 * Created: 11/8/2018
 */

if($topBits === null){
    $topBits = '';
}
$counter = 0;
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('header')
    @include('v1.parts.typeahead')
@endsection

@section('content')



    @include('v1.parts.start_min_content', ['header' => $org->orgName . ': ' . trans('messages.headers.roles&perms'),
                                            'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
    <div>
        <div class="col-sm-12 well-sm">
            @lang('messages.instructions.role_txt')
        </div>

        <div class="col-sm-6">
            <div class="col-sm-12 well-sm bg-primary">
                <b>{{ $org->orgName }} @lang('messages.headers.roles')</b>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-3" style="background-color:yellow;">@lang('messages.fields.name')</div>
                <div class="col-sm-3" style="background-color:yellow;">@lang('messages.headers.disp_name')</div>
                <div class="col-sm-6" style="background-color:yellow;">@lang('messages.headers.desc')</div>
            </div>

            @foreach($roles as $r)
<?php
                if($counter++ % 2) {
                    $bg = "bg-info";
                } else {
                    $bg = "";
                }
?>
                <div class="col-sm-12">
                    <div class="col-sm-3 well-sm {{ $bg }}">{{ $r->name }}</div>
                    <div class="col-sm-3 well-sm {{ $bg }}">{{ $r->display_name }}</div>
                    <div class="col-sm-6 well-sm {{ $bg }}">{{ $r->description }} <br/>
                        @lang('messages.headers.perms'):
                    </div>
                </div>
            @endforeach

        </div>

        <div class="col-sm-6">
            <div class="col-sm-12 well-sm bg-primary">
                <b>{{ $org->orgName }} @lang('messages.headers.perms')</b>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-3" style="background-color:yellow;">@lang('messages.fields.name')</div>
                <div class="col-sm-3" style="background-color:yellow;">@lang('messages.headers.disp_name')</div>
                <div class="col-sm-6" style="background-color:yellow;">@lang('messages.headers.desc')</div>
            </div>

            <?php $counter = 0 ?>
            @foreach($permissions as $p)
<?php
                if($counter++ % 2) {
                    $bg = "bg-info";
                } else {
                    $bg = "";
                }
?>
                <div class="col-sm-12 well-sm">
                    <div class="col-sm-3 well-sm {{ $bg }}">{{ $p->name }}</div>
                    <div class="col-sm-3 well-sm {{ $bg }}">{{ $p->display_name }}</div>
                    <div class="col-sm-6 well-sm {{ $bg }}">{{ $p->description }}</div>
                </div>
            @endforeach

        </div>

    </div>
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.person_search'),
         'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

    {!! Form::open(array('url' => env('APP_URL')."/role_search", 'method' => 'POST')) !!}
    <div id="custom-template" class="col-sm-12 form-group">
        <b>{!! trans('messages.instructions.mbr_search') !!}</b>
        <div class="col-xs-2">
            {!! Form::text('string', null, array('id' => 'helper', 'class' => 'typeahead input-xs')) !!}<br />
        </div>
        <div id="search-results"></div>
    </div>
    <div class="col-sm-12">
        <div class="col-xs-2">
            {!! Form::submit(trans('messages.headers.person_search'), array('class' => 'btn btn-primary btn-xs form-control')) !!}
        </div>
    </div>
    {!! Form::close() !!}

    @include('v1.parts.end_content')

    <div id="role_mgmt_status" class="col-sm-12"></div>

    @if($persons !== null)
    @include('v1.parts.start_content', ['header' => $org->orgName . ': '. trans('messages.headers.mList'),
             'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

    <div>
        <table id="datatable-fixed-header" class="table table-striped table-bordered table-condensed table-responsive">
            <thead>
            <tr>
                <th width="15%" style="vertical-align: top; text-align: left; min-width: 1px; max-width: 20%;">
                    @lang('messages.fields.lastName')
                </th>
                <th width="15%" style="vertical-align: top; text-align: left; min-width: 1px; max-width: 20%;">
                    @lang('messages.fields.firstName')
                </th>
                <th width="15%" style="vertical-align: top; text-align: left; min-width: 1px; max-width: 20%;">
                    @lang('messages.headers.email')
                </th>
                <th width="15%" style="vertical-align: top; text-align: left; min-width: 1px; max-width: 20%;">
                    @lang('messages.fields.pmi_id')
                </th>
                <th width="40%" style="vertical-align: top; text-align: left; min-width: 1px;">
                    @lang('messages.headers.ass_roles')
                </th>
                <th width="40%" style="vertical-align: top; text-align: left; min-width: 1px;">
                    @lang('messages.actions.merge')
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach ($persons as $p)
<?php
                if($p->roles->contains('id', 1)) {
                    $board_color = 'btn-purple';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.remove').trans('messages.topBits.board')."'";
                } else {
                    $board_color = 'btn-lpurple';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.add').trans('messages.topBits.board')."'";
                }

                $board =
                    '<a ' . $tooltip . 'onclick="javascript:activate(' . $p->personID . ', ' . '1)" class="btn btn-sm ' . $board_color . '">'
                    . '<i class="fa fa-users"></i></a>';

                if($p->roles->contains('id', 2)) {
                    $board_color = 'btn-red';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.remove').trans('messages.topBits.speaker')."'";
                } else {
                    $board_color = 'btn-lred';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.add').trans('messages.topBits.speaker')."'";
                }
                $speaker =
                    '<a ' . $tooltip . 'onclick="javascript:activate(' . $p->personID . ', ' . '2)" class="btn btn-sm ' . $board_color . '">'
                    . '<i class="fas fa-microphone"></i></a>';

                if($p->roles->contains('id', 3)) {
                    $board_color = 'btn-deep-purple';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.remove').trans('messages.topBits.events')."'";
                } else {
                    $board_color = 'btn-ldeep-purple';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.add').trans('messages.topBits.events')."'";
                }
                $event =
                    '<a ' . $tooltip . 'onclick="javascript:activate(' . $p->personID . ', ' . '3)" class="btn btn-sm ' . $board_color . '">'
                    . '<i class="far fa-calendar-alt"></i></a>';

                if($p->roles->contains('id', 4)) {
                    $board_color = 'btn-blue';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.remove').trans('messages.topBits.vol')."'";
                } else {
                    $board_color = 'btn-lblue';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.add').trans('messages.topBits.vol')."'";
                }
                $volunteer =
                    '<a ' . $tooltip . 'onclick="javascript:activate(' . $p->personID . ', ' . '4)" class="btn btn-sm ' . $board_color . '">'
                    . '<i class="fas fa-hands-helping"></i></a>';

                if($p->roles->contains('id', 6)) {
                    $board_color = 'btn-cyan';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.remove').trans('messages.topBits.spk_vol')."'";
                } else {
                    $board_color = 'btn-lcyan';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.add').trans('messages.topBits.spk_vol')."'";
                }
                $spkvol =
                    '<a ' . $tooltip . 'onclick="javascript:activate(' . $p->personID . ', ' . '6)" class="btn btn-sm ' . $board_color . '">'
                    . '<i class="fas fa-microphone-slash"></i></a>';

                if($p->roles->contains('id', 7)) {
                    $board_color = 'btn-teal';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.remove').trans('messages.topBits.rt')."'";
                } else {
                    $board_color = 'btn-lteal';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.add').trans('messages.topBits.rt')."'";
                }
                $rtvol =
                    '<a ' . $tooltip . 'onclick="javascript:activate(' . $p->personID . ', ' . '7)" class="btn btn-sm ' . $board_color . '">'
                    . '<i class="fas fa-circle"></i></a>';

                if($p->roles->contains('id', 8)) {
                    $board_color = 'btn-green';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.remove').trans('messages.topBits.admin')."'";
                } else {
                    $board_color = 'btn-lgreen';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.add').trans('messages.topBits.admin')."'";
                }
                $admin =
                    '<a ' . $tooltip . 'onclick="javascript:activate(' . $p->personID . ', ' . '8)" class="btn btn-sm ' . $board_color . '">'
                    . '<i class="fas fa-tachometer-alt"></i></a>';

                if($p->roles->contains('id', 9)) {
                    $board_color = 'btn-amber';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.remove').trans('messages.topBits.dev')."'";
                } else {
                    $board_color = 'btn-lamber';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.add').trans('messages.topBits.dev')."'";
                }
                $dev =
                    '<a ' . $tooltip . 'onclick="javascript:activate(' . $p->personID . ', ' . '9)" class="btn btn-sm ' . $board_color . '">'
                    . '<i class="fas fa-archive"></i></a>';

                if($p->roles->contains('id', 10)) {
                    $board_color = 'btn-brown';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.remove').trans('messages.topBits.mktg')."'";
                } else {
                    $board_color = 'btn-lbrown';
                    $tooltip     = "data-toggle='tooltip' title='".trans('messages.actions.add').trans('messages.topBits.mktg')."'";
                }
                $mktg =
                    '<a ' . $tooltip . 'onclick="javascript:activate(' . $p->personID . ', ' . '10)" class="btn btn-sm ' . $board_color . '">'
                    . '<i class="fas fa-chart-bar"></i></a>';

                $merge_form = "<a href='" . env('APP_URL') . "/merge/p/$p->personID' data-toggle='tooltip' data-placement='top'
                    title='" . trans('messages.tooltips.mr') . "' class='btn btn-sm btn-warning'>
                    <i class='far fa-fw fa-code-branch'></i></a>";

                $a = "<a href='" . env('APP_URL')."/profile/$p->personID" . "' target='_new'>";
?>
                <tr>
                    <td style="vertical-align: top; text-align: left;">{!! $a . $p->lastName . "</a>" !!}</td>
                    <td style="vertical-align: top; text-align: left;">{!! $p->firstName !!}</td>
                    <td style="vertical-align: top; text-align: left;">{!! $p->login !!}</td>
                    <td style="vertical-align: top; text-align: left;">{!! $p->OrgStat1 !!}</td>
                    <td style="vertical-align: top; text-align: left;">
                        {!! $board !!}
                        {!! $mktg !!}
                        {!! $event !!}
                        {!! $rtvol !!}
                        {!! $spkvol !!}
                        {!! $volunteer !!}
                        {!! $speaker !!}
                        @if(Entrust::hasRole('Admin') || Entrust::hasRole('Developer'))
                            {!! $admin !!}
                        @endif
                        @if((Entrust::hasRole('Developer')))
                            {!! $dev !!}
                        @endif
                    </td>
                    <td style="vertical-align: top; text-align: left;">
                        {!! $merge_form !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

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

    @include('v1.parts.menu-fix', array('path' => '/role_mgmt'))
    <script>
        $('.collapsed').css('height', 'auto');
        $('.collapsed').find('.x_content').css('display', 'none');
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#datatable-fixed-header').DataTable();
        });

        function activate(personID, id) {
            $.ajax({
                type: 'POST',
                cache: false,
                async: true,
                url: '{{ env('APP_URL') }}/role/' + personID + '/' + id,
                dataType: 'json',
                success: function (data) {
                    console.log(data);
                    var result = eval(data);
                    $('#role_mgmt_status').html(data.message);
                    // window.location="/role_mgmt";
                },
                error: function (data) {
                    console.log(data);
                    var result = eval(data);
                    //$('#status_msg').html(result.message).fadeIn(0);
                }
            });
        }
    </script>
@endsection

@section('modals')
    @include('v1.modals.dynamic', ['header' => trans('messages.headers.mAct'), 'url' => 'activity'])
@endsection
