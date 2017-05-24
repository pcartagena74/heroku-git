<?php
/**
 * Comment:
 * Created: 2/6/2017
 *
 * @param   $attendance     structured array with last 14 event dates, attendee count and whether attended
 */

use Carbon\Carbon;

    $headers = ['Date', 'Event Type', 'Networking List'];
    $data = [];
    $current_user = auth()->user()->id;
    foreach($attendance as $event) {
        $dt = Carbon::parse($event->eventStartDate);
        $csrf = csrf_field();
        //$nlb = "<form id='netform' action='/activity/$current_user' method='post'>
        $nlb = "<form id='netform' action='#' method='get'>
                $csrf
                <input type='hidden' name='eventID' value='$event->eventID'>
                <input type='hidden' name='eventName' value='$event->eventName'>
                <button type='submit' disabled id='network' class='btn btn-success btn-xs btn'>View Networking List</button>
                </form>";
        array_push($data, [$dt->toFormattedDateString(), $event->eventName, $nlb]);
    }
    count($data) > 15 ? $scroll = 1 : $scroll = 0;

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @include('v1.parts.start_content', ['header' => 'Chapter Event Attendance', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
    Green bars indicate those events you registered to attend.  Red bars indicate events for which you did not register.
        <div id='canvas'></div>
    @include('v1.parts.end_content')


    @include('v1.parts.start_content', ['header' => 'Your Activity', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    @include('v1.parts.datatable', ['headers' => $headers, 'data' => $data, 'scroll' => $scroll])
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Networking List', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0, 'id' => 'Networking List'])
    <p>Data above will be more complete as more data from MEG is uploaded.  I'm having event history ported over wherever there were attendees.</p>

    <p>Button clicks to the left are temporarily disabled.  Clicking them in the future (i.e., in the next 2 weeks) will show the list of attendees for the event listed in this box.
        Attendees will only show up if they authorized it.</p>
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
<script>
    $(document).ready(function() {
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

        $SIDEBAR_MENU.find('a[href="/dashboard"]').parent('li').addClass('current-page').parents('ul').slideDown(function () {
            setContentHeight();
        }).parent().addClass('active');
    });
</script>

@endsection