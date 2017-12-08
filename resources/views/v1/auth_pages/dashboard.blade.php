<?php
/**
 * Comment:
 * Created: 2/6/2017
 *
 * @param   $attendance     structured array with last 14 event dates, attendee count and whether attended
 */

use Carbon\Carbon;

$headers      = ['Date', 'Event Type', 'Networking List'];
$data         = [];
$current_user = auth()->user()->id;
foreach($attendance as $event) {
    $dt   = Carbon::parse($event->eventStartDate);
    if($event->cnt2 > 0) {
        $nlb  =
            '<a onclick="getList('. $event->eventID . ", '" . $event->eventName . "'" . ');" class="network btn btn-success btn-xs">View Networking List</a>';
    } else {
        $nlb  = '<a class="network btn btn-cancel btn-xs" disabled data-toggle="tooltip" data-placement="top" '.
                'title="This event has no networking list.">View Networking List</a>';
    }
    array_push($data, [$dt->format('Y/n/j'), $event->eventName, $nlb]);
}
count($data) > 15 ? $scroll = 1 : $scroll = 0;

$tbl_header = ['First', 'Last', 'Email', 'Company', 'Industry'];

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @include('v1.parts.start_content', ['header' => 'Chapter Event Attendance', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
    Green bars indicate those events you registered to attend.  Red bars indicate events for which you did not register.
    <div id='canvas'></div>
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Past Event Attendance', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    @include('v1.parts.datatable', ['headers' => $headers, 'data' => $data, 'scroll' => $scroll])
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Networking List', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0, 'id' => 'Networking List'])
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
                            { data: 'firstName', title: 'First' },
                            { data: 'lastName' , title: 'Last' },
                            { data: 'login', title: 'Email' },
                            { data: 'compName', title: 'Company' },
                            { data: 'indName', title: 'Industry' }
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
                xkey: 'ChMtg',
                ykeys: ['Attendees'],
                labels: ['Attendees'],
                barRatio: 0.1,
                barColors: function (row, series, type) {
                    if ({!!  $output !!}) {
                        return "#26B99A";
                    } else {
                        return "#f00";
                    }
                },
                xLabelAngle: 35,
                hideHover: 'auto',
                resize: true
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            var setContentHeight = function () {
                // reset height
                $RIGHT_COL.css('min-height', $(window).height());

                var bodyHeight = $BODY.outerHeight(),
                    footerHeight = $BODY.hasClass('footer_fixed') ? -10 : $FOOTER.height(),
                    leftColHeight = $LEFT_COL.eq(1).height() + $SIDEBAR_FOOTER.height(),
                    contentHeight = bodyHeight < leftColHeight ? leftColHeight : bodyHeight;

                // normalize content
                contentHeight -= $NAV_MENU.height() + footerHeight;

                $RIGHT_COL.css('min-height', contentHeight);
            };

            $SIDEBAR_MENU.find('a[href="{{ env('APP_URL') }}/dashboard"]').parent('li').addClass('current-page').parents('ul').slideDown(function () {
                setContentHeight();
            }).parent().addClass('active');
        });
    </script>

@endsection