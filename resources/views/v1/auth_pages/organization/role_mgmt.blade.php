<?php
/**
 * Comment:
 * Created: 2/9/2017
 */

$topBits = '';  // remove this if this was set in the controller
$counter = 0;
$headers = ['Last Name', 'First Name', 'Email', 'PMI ID'];
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @include('v1.parts.start_min_content', ['header' => $org->orgName . ': Role & Permission Management',
    'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
    <div>
        <div class="col-sm-12 well-sm">
            These values in these tables are not editable and are here for informational purposes only.
        </div>

        <div class="col-sm-6">
            <div class="col-sm-12 well-sm bg-primary">
                <b>{{ $org->orgName }} Roles</b>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-3" style="background-color:yellow;">Name</div>
                <div class="col-sm-3" style="background-color:yellow;">Display Name</div>
                <div class="col-sm-6" style="background-color:yellow;">Description</div>
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
                    <div class="col-sm-6 well-sm {{ $bg }}">{{ $r->description }} <br />
                        Permissions:
                    </div>
                    {{--
                                    <div class="col-sm-3 well-sm {{ $bg }}">
                                        <a href="#" id="role-name-{{ $r->id }}" data-pk="{{ $r->id }}"
                                           data-url="/role/{{ $org->orgID }}" data-value="{{ $r->name }}"></a>
                                    </div>
                    --}}
                </div>
            @endforeach

        </div>

        <div class="col-sm-6">
            <div class="col-sm-12 well-sm bg-primary">
                <b>{{ $org->orgName }} Permissions</b>
            </div>

            <div class="col-sm-12">
                <div class="col-sm-3" style="background-color:yellow;">Name</div>
                <div class="col-sm-3" style="background-color:yellow;">Display Name</div>
                <div class="col-sm-6" style="background-color:yellow;">Description</div>
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

    @include('v1.parts.start_content', ['header' => $org->orgName . ': Role Assignment',
    'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

    <div id="role_mgmt_status" class="col-sm-12"></div>

    @include('v1.parts.start_content', ['header' => $org->orgName . ': Member List',
             'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

    <div>
        <table id="datatable-fixed-header" class="table table-striped table-bordered table-condensed table-responsive">
            <thead>
            <tr>
                <th width="15%" style="vertical-align: top; text-align: left; min-width: 1px; max-width: 20%;">
                    Last Name
                </th>
                <th width="15%" style="vertical-align: top; text-align: left; min-width: 1px; max-width: 20%;">
                    First Name
                </th>
                <th width="15%" style="vertical-align: top; text-align: left; min-width: 1px; max-width: 20%;">
                    Email
                </th>
                <th width="15%" style="vertical-align: top; text-align: left; min-width: 1px; max-width: 20%;">
                    PMI ID
                </th>
                <th width="40%" style="vertical-align: top; text-align: left; min-width: 1px;">
                    Assign Roles
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach ($persons as $p)
<?php

                if($p->roles->contains('id', 1)) {
                    $board_color = 'btn-lpurple';
                } else {
                    $board_color = 'btn-purple';
                }

                $board =
                    '<button onclick="javascript:activate(' . $p->personID . ', ' . '1)" class="btn btn-sm ' . $board_color . '">'
                    . '<i class="fa fa-users"></i></button>';

                if($p->roles->contains('id', 2)) {
                    $board_color = 'btn-lred';
                } else {
                    $board_color = 'btn-red';
                }
                $speaker =
                    '<button onclick="javascript:activate(' . $p->personID . ', ' . '2)" class="btn btn-sm ' . $board_color . '">'
                    . '<i class="fa fa-microphone"></i></button>';

                if($p->roles->contains('id', 3)) {
                    $board_color = 'btn-ldeep-purple';
                } else {
                    $board_color = 'btn-deep-purple';
                }
                $event =
                    '<button onclick="javascript:activate(' . $p->personID . ', ' . '3)" class="btn btn-sm ' . $board_color . '">'
                    . '<i class="fa fa-calendar"></i></button>';

                if($p->roles->contains('id', 4)) {
                    $board_color = 'btn-lblue';
                } else {
                    $board_color = 'btn-blue';
                }
                $volunteer =
                    '<button onclick="javascript:activate(' . $p->personID . ', ' . '4)" class="btn btn-sm ' . $board_color . '">'
                    . '<i class="fa fa-thumbs-o-up"></i></button>';

                if($p->roles->contains('id', 6)) {
                    $board_color = 'btn-lcyan';
                } else {
                    $board_color = 'btn-cyan';
                }
                $spkvol =
                    '<button onclick="javascript:activate(' . $p->personID . ', ' . '6)" class="btn btn-sm ' . $board_color . '">'
                    . '<i class="fa fa-microphone-slash"></i></button>';

                if($p->roles->contains('id', 7)) {
                    $board_color = 'btn-lteal';
                } else {
                    $board_color = 'btn-teal';
                }
                $rtvol =
                    '<button onclick="javascript:activate(' . $p->personID . ', ' . '7)" class="btn btn-sm ' . $board_color . '">'
                    . '<i class="fa fa-circle-o"></i></button>';

                if($p->roles->contains('id', 8)) {
                    $board_color = 'btn-lgreen';
                } else {
                    $board_color = 'btn-green';
                }
                $admin =
                    '<button onclick="javascript:activate(' . $p->personID . ', ' . '8)" class="btn btn-sm ' . $board_color . '">'
                    . '<i class="fa fa-dashboard"></i></button>';

                if($p->roles->contains('id', 9)) {
                    $board_color = 'btn-lamber';
                } else {
                    $board_color = 'btn-amber';
                }
                $dev =
                    '<button onclick="javascript:activate(' . $p->personID . ', ' . '9)" class="btn btn-sm ' . $board_color . '">'
                    . '<i class="fa fa-file-archive-o"></i></button>';
?>
                <tr>
                    <td style="vertical-align: top; text-align: left;">{!! $p->lastName !!}</td>
                    <td style="vertical-align: top; text-align: left;">{!! $p->firstName !!}</td>
                    <td style="vertical-align: top; text-align: left;">{!! $p->login !!}</td>
                    <td style="vertical-align: top; text-align: left;">{!! $p->OrgStat1 !!}</td>
                    <td style="vertical-align: top; text-align: left;">
                        {!! $board !!} {!! $speaker !!} {!! $event !!} {!! $volunteer !!}
                        {!! $spkvol !!} {!! $rtvol !!}
    @if((Entrust::hasRole('Admin')))
                            {!! $admin !!} {!! $dev !!}
    @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        @include('v1.parts.end_content')

        @include('v1.parts.end_content')


    @endsection

    @section('scripts')
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script>
            $('.collapsed').css('height', 'auto');
            $('.collapsed').find('.x_content').css('display', 'none');
            {{--
                    $('.collapsed').find('i').toggleClass('fa-chevron-up fa-chevron-down');
            --}}
        </script>
        @include('v1.parts.footer-datatable')
        <script>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        </script>
        <script>
            $('[data-toggle=confirmation]').confirmation();
        </script>
        <script>
            $(document).ready(function () {
                $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                    $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
                });
                $('#datatable-fixed-header').DataTable().search('').draw();
            });
        </script>
        <script>
            function activate(personID, id) {
                $.ajax({
                    type: 'POST',
                    cache: false,
                    async: true,
                    url: '/role/' + personID + '/' + id,
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
            };
        </script>
    @endsection