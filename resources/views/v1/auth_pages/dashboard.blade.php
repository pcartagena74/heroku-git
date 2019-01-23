<?php
/**
 * Comment:
 * Created: 2/6/2017
 *
 * @param   $attendance     structured array with last 14 event dates, attendee count and whether attended
 */

use Carbon\Carbon;

$headers      = [trans_choice('messages.headers.date', 1), trans_choice('messages.headers.et', 1), trans('messages.headers.net_list')];
$data         = [];
$current_user = auth()->user()->id;
foreach($attendance as $event) {
    $dt   = Carbon::parse($event->eventStartDate);
    if($event->cnt2 > 0) {
        $nlb  =
            '<a onclick="getList('. $event->eventID . ", '" . $event->eventName . "'" . ');" class="network btn btn-success btn-xs">'
            . trans('messages.headers.view') . " " . trans('messages.headers.net_list') . '</a>';
    } else {
        $nlb  = '<a class="network btn btn-cancel btn-xs" disabled data-toggle="tooltip" data-placement="top" '.
                'title="This event has no networking list.">View Networking List</a>';
    }
    array_push($data, [$dt->format('Y/n/j'), $event->eventName, $nlb]);
}
count($data) > 15 ? $scroll = 1 : $scroll = 0;

$tbl_header = [trans('messages.fields.first'), trans('messages.fields.last'), trans('messages.headers.email'), trans('messages.headers.comp'), trans('messages.fields.indName')];

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.chap_evt_att'), 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
    @lang('messages.instructions.bar_chart')
    <div id='canvas'></div>
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.past_evt_att'), 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    @include('v1.parts.datatable', ['headers' => $headers, 'data' => $data, 'scroll' => $scroll])
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.net_list'), 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0, 'id' => 'Networking List'])
    <div id="event_name" class="col-sm-12"></div><p>
    @include('v1.parts.datatable', ['headers' => $tbl_header, 'data' => [], 'scroll' => 1, 'id' => 'network_list'])
    @include('v1.parts.end_content')

@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.15/js/dataTables.bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/1.10.15/sorting/datetime-moment.js"></script>
    <script>
        $("#datatable-fixed-header").DataTable({
            "fixedHeader": true,
            order: [[ 0, 'desc' ]]
        });
        var list = $("#network_list").DataTable({
            "fixedHeader": true
        });
    </script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        function getList(eventID, eventName) {
            // lookup the clicked event's attendee list
            $.ajax({
                type: 'POST',
                cache: false,
                async: true,
                url: '{{ env('APP_URL') }}/networking',
                dataType: 'json',
                data: {
                    eventID: eventID,
                    eventName: eventName
                },
                success: function (data) {
                    var result = eval(data);
                    $("#event_name").text(result.event).css('font-weight', 'bold').css('font-size', '16px').css('color', 'red');
                    $("#network_list").DataTable({
                        destroy: true,
                        data: result.data,
                        "fixedHeader": true,
                        columns: [
                            { data: 'firstName', title: '{!! trans('messages.fields.first') !!}' },
                            { data: 'lastName' , title: '{!! trans('messages.fields.last') !!}' },
                            { data: 'login', title: '{!! trans('messages.headers.email') !!}' },
                            { data: 'compName', title: '{!! trans('messages.headers.comp') !!}' },
                            { data: 'indName', title: '{!! trans('messages.fields.indName') !!}' }
                        ]
                    });
                    {{--
                    console.log(result);
                    --}}
                },
                error: function (data) {
                    var result = eval(data);
                    console.log(result.event);
                    console.log(result.data);
                }
            });
        }
    </script>
    @include('v1.parts.footer-chart')
    <script>
        $(document).ready(function () {
            Morris.Bar({
                element: 'canvas',
                data: [
                    {!! $datastring !!}
                ],
                xLabelMargin: 2,
                padding: 80,
                xkey: 'ChMtg',
                ykeys: ['Attendees'],
                labels: ['Attendees'],
                barRatio: 0.5,
                barColors: function (row, series, type) {
                    if ({!!  $output !!}) {
                        return "#26B99A";
                    } else {
                        return "#f00";
                    }
                },
                xLabelAngle: 25,
                hideHover: 'auto',
                resize: true
            });
        });
    </script>
    @include('v1.parts.menu-fix', ['path' => '/dashboard'])
@endsection