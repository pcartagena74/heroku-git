<?php
/**
 * Comment:
 * Created: 2/6/2017
 *
 * @param   $attendance     structured array with last 14 event dates, attendee count and whether attended
 */

    $headers = ['Date', 'Event Type', 'Networking List'];
    $data = [];
    foreach($attendance as $event) {
        $csrf = csrf_field();
        $nlb = "<form id='netform' action='/include/show_network_attendees.php' method='post'>
$csrf
<input type='hidden' name='eventID' value='$event->eventID'>
<input type='hidden' name='eventName' value='$event->eventName'>
<button type='submit' id='network' class='btn btn-success btn-xs btn'>View Networking List</button>
</form>";
        array_push($data, [$event->eventStartDate, $event->eventName, $nlb]);
    }
    count($data) > 15 ? $scroll = 1 : $scroll = 0;

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @include('v1.parts.start_content', ['header' => 'Chapter Event Attendance', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
        <div id='canvas'></div>
    @include('v1.parts.end_content')


    @include('v1.parts.start_content', ['header' => 'Your Activity', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    @include('v1.parts.datatable', ['headers' => $headers, 'data' => $data, 'scroll' => $scroll])
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Networking List', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0, 'id' => 'Networking List'])
    @include('v1.parts.end_content')

@endsection

@section('scripts')

@include('v1.parts.footer-chart')

<script>
    $(document).ready(function() {
        Morris.Bar({
            element: 'canvas',
            data: [
                {!! $datastring !!}
            ],
            xkey: 'ChMtg',
            ykeys: ['Attendees'],
            labels: ['Attendees'],
            barRatio: 0.1,
            barColors:
                function (row, series, type) {
                    if({!!  $output !!}) {
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

@endsection