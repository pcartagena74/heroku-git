<?php
/**
 * Comment:
 * Created: 2/2/2017
 *
 * $header, $subheader, $w1, $w2, $r1, $r2, $r3
 *
 */

$current_headers =
    ['Event Dates', 'Event Name', 'Event Type', 'Event Status', 'Attendee Count', 'Event Management'];
//    ['#', 'Event Name', 'Event Type', 'Event Dates', 'Event Status', 'Attendee Count', 'Event Management'];
$current_data    = [];

foreach($current_events as $event) {
    $csrf = csrf_field();

    $active_button = '<button onclick="javascript:activate(' . $event->eventID . ')" class="btn ';
    if($event->isActive) {
        $active_button .= "btn-success btn-sm";
    } else {
        $active_button .= "btn-cancel btn-xs";
    }
    $active_button .= '">';
    if($event->isActive) {
        $active_button .= "<b>Active</b>";
    } else {
        $active_button .= "Inactive";
    }
    $active_button .= "</button>";

    $progress_bar = '<div class="progress progress_sm">
        <div class="progress-bar bg-green" role="progressbar" data-transitiongoal="' . $event->cnt . '"></div>
        </div><small>' . $event->cnt . ' attendees</small>';

    $editURL    = env('APP_URL').'/event/' . $event->eventID . '/edit';
    $displayURL = env('APP_URL').'/events/' . $event->slug;
    $tktURL = env('APP_URL').'/event-tickets/'. $event->eventID;
    $eventDiscountURL = env('APP_URL').'/eventdiscount/' . $event->eventID;
    $trackURL = env('APP_URL').'/tracks/' . $event->eventID;
    $rptURL = env('APP_URL').'/eventreport/' . $event->slug;
    $copyURL = env('APP_URL').'/eventcopy/' . $event->slug;
    $checkinURL = env('APP_URL').'/checkin/' . $event->slug;

    $edit_link_button    = "<a href='$editURL' class='btn btn-primary btn-xs'><i class='fa fa-pencil'></i> Edit Event</a>";
    $track_link_button    = "<a href='$trackURL' class='btn btn-success btn-xs'><i class='fa fa-pencil'></i> Tracks & Sessions</a>";
    if($event->hasTracks == 0){
        $track_link_button = '';
    }
    $rpt_link_button    = "<a href='$rptURL' class='btn btn-purple btn-xs'><i class='fa fa-bar-chart-o'></i> Event Reporting</a>";
    $copy_link_button    = "<a href='$copyURL' class='btn btn-deep-orange btn-xs'><i class='fa fa-copy'></i> Copy Event</a>";
    $display_link_button =
        "<a target='_new' href='$displayURL' class='btn btn-primary btn-xs'><i class='fa fa-eye'></i> Preview</a>";
    $eventDiscount_button =
       "<a href='$eventDiscountURL' class='btn btn-success btn-xs'><i class='fa fa-pencil'></i> Event Discounts</a>";
    $delete_button       = Form::open(['url' => env('APP_URL').'/event/' . $event->eventID, 'method' => 'DELETE']) .
        '<button class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Delete</button>
            <input id="myDelete" type="submit" value="Go" class="hidden" /></form>';
    if($event->isActive) {
        $delete_button = '';
    }

    $ticket_button =
        "<a href='$tktURL' class='btn btn-info btn-xs'><i class='fa fa-pencil'></i> Tickets</a>";
    $checkin_button    = "<a href='$checkinURL' class='btn btn-purple btn-xs'><i class='fa fa-check-square-o'></i> Check-in Attendees</a>";

    array_push($current_data, ["<nobr>" . $event->eventStartDateF . "  - </nobr><br><nobr>" . $event->eventEndDateF . "</nobr>",
        $event->eventName, $event->etName, $active_button, $progress_bar, $display_link_button . $edit_link_button . $eventDiscount_button .
        $rpt_link_button  . $copy_link_button . $track_link_button . $ticket_button . $checkin_button . $delete_button]);
//    array_push($current_data, [$event->eventID, $event->eventName, $event->etName,
//        "<nobr>" . $event->eventStartDateF . "  - </nobr><br><nobr>" . $event->eventEndDateF . "</nobr>",
//        $active_button, $progress_bar, $display_link_button . $edit_link_button . $eventDiscount_button .
//        $rpt_link_button  . $copy_link_button . $track_link_button . $ticket_button . $checkin_button . $delete_button]);
}

count($current_data) > 15 ? $current_scroll = 1 : $current_scroll = 0;

$past_headers =
['Event Dates', 'Event Name', 'Event Type', 'Attendee Count', 'Event Management'];
//['#', 'Event Name', 'Event Type', 'Event Dates', 'Attendee Count', 'Event Management'];
$past_data    = [];

foreach($past_events as $event) {
    $rptURL = env('APP_URL').'/eventreport/' . $event->slug;
    $tktURL = env('APP_URL').'/event-tickets/'. $event->eventID;
    $copyURL = env('APP_URL').'/eventcopy/' . $event->slug;

    $ticket_button =
        "<a href='$tktURL' class='btn btn-info btn-xs'><i class='fa fa-pencil'></i> Tickets</a>";
    $rpt_link_button    = "<a href='$rptURL' class='btn btn-purple btn-xs'><i class='fa fa-bar-chart-o'></i> Event Reporting</a>";
    $copy_link_button    = "<a href='$copyURL' class='btn btn-deep-orange btn-xs'><i class='fa fa-copy'></i> Copy Event</a>";

    array_push($past_data, [ "<nobr>" . $event->eventStartDateF . "  - </nobr><br><nobr>" . $event->eventEndDateF . "</nobr>",
        $event->eventName, $event->etName, $event->cnt, $ticket_button . $rpt_link_button . $copy_link_button]);
//    array_push($past_data, [$event->eventID, $event->eventName, $event->etName,
//        "<nobr>" . $event->eventStartDateF . "  - </nobr><br><nobr>" . $event->eventEndDateF . "</nobr>",
//        $event->cnt, $ticket_button . $rpt_link_button . $copy_link_button]);
}
//dd($past_data);
count($past_data) > 15 ? $past_scroll = 1 : $past_scroll = 0;

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
    @include('v1.parts.header-datatable')

    @include('v1.parts.start_content', ['header' => '', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <div class="col-md-12 col-sm-12 col-xs-12">
        <ul id="myTab" class="nav nav-tabs bar_tabs nav-justified" role="tablist">
            <li class="active"><a href="#tab_content1" id="current_events-tab" data-toggle="tab"
                                  aria-expanded="true"><b>Upcoming Events</b></a></li>
            <li class=""><a href="#tab_content2" id="past_events-tab" data-toggle="tab" aria-expanded="false"><b>Past
                        Events</b></a></li>
        </ul>
        <div id="tab-content" class="tab-content">
            <div class="tab-pane active" id="tab_content1" aria-labelledby="current_events-tab">
                <p>&nbsp;</p>
                @if(count($current_data) > 0)
                @include('v1.parts.datatable', ['headers' => $current_headers,
                    'data' => $current_data,
                    'id' => 'current_events',
                    'scroll' => $current_scroll])
                @else
                    There are no future events in the system.
                @endif
            </div>
            <div class="tab-pane fade" id="tab_content2" aria-labelledby="past_events-tab">
                <p>&nbsp;</p>
                @include('v1.parts.datatable', ['headers' => $past_headers,
                    'data' => $past_data,
                    'id' => 'past_events',
                    'scroll' => $past_scroll])
            </div>
        </div>
    </div>
    @include('v1.parts.end_content')

@endsection

@section('scripts')
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
        $(document).ready(function() {
            $('#past_events').DataTable({
                "fixedHeader": true,
                "order": [[ 0, "desc" ]]
            });
        });
        $(document).ready(function () {
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
            });
            $('#past_events').DataTable().search('').draw();
        });
    </script>
    <script>
        function activate(eventID) {
            $.ajax({
                type: 'POST',
                cache: false,
                async: true,
                url: '{{ env('APP_URL') }}/activate/' + eventID,
                dataType: 'json',
                success: function (data) {
                    console.log(data);
                    var result = eval(data);
                    window.location="/events";
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