<?php
/**
 * Comment:
 * Created: 2/2/2017
 *
 * $header, $subheader, $w1, $w2, $r1, $r2, $r3
 *
 */

$current_headers = ['#', 'Event Name', 'Event Type', 'Event Dates', 'Event Status', 'Attendee Count', 'Event Management'];
$current_data    = [];

foreach($current_events as $event) {
    $csrf = csrf_field();

    $active_button = '<button type="button" class="btn ';
    if($event->isActive) {
        $active_button .= "btn-success";
    } else {
        $active_button .= "btn-cancel";
    }
    $active_button .= ' btn-xs">';
    if($event->isActive) {
        $active_button .= "Active";
    } else {
        $active_button .= "Inactive";
    }
    $active_button .= "</button>";

    $progress_bar = '<div class="progress progress_sm">
        <div class="progress-bar bg-green" role="progressbar" data-transitiongoal="' . $event->cnt . '"></div>
        </div><small>' . $event->cnt . ' attendees</small>';

    $editURL = '/event/' . $event->eventID . '/edit';

    $edit_button   = "<form method='post' action='$editURL'>" .
        $csrf . '
        <label for="mySubmit' . $event->eventID . '" class="btn btn-info btn-xs"><i class="fa fa-pencil"></i> Edit</label>
        <input type="hidden" name="eventID" value="' . $event->eventID . '">
        <input type="hidden" name="function" value="edit">
        <input id="mySubmit' . $event->eventID . '" type="submit" value="Go" class="hidden" />
        </form>';


    $edit_link_button = "<a href='$editURL' class='btn btn-primary btn-xs'><i class='fa fa-pencil'></i> Edit</a>";
    $delete_button = Form::open(['url' => '/event/'. $event->eventID, 'method' => 'DELETE']) .
        '<button class="btn btn-danger btn-xs">
            <i class="fa fa-trash"></i> Delete</button>
            <input id="myDelete" type="submit" value="Go" class="hidden" /></form>';

    $ticket_button = '<form method="post" action="/event-tickets/' . $event->eventID . '">' . $csrf .
        '<label for="TicketSubmit' . $event->eventID . '" class="btn btn-info btn-xs"><i class="fa fa-pencil"></i> Tickets</label>
            <input type="hidden" name="eventID" value="' . $event->eventID . '">
            <input type="hidden" name="eventName" value="' . $event->eventID . '">
            <input type="hidden" name="function" value="ticket">
            <input id="TicketSubmit' . $event->eventID . '" type="submit" value="Go" class="hidden" /></form>';

    array_push($current_data, [$event->eventID, $event->eventName, $event->etName,
        "<nobr>" . $event->eventStartDateF . "  - </nobr><br><nobr>" . $event->eventEndDateF . "</nobr>",
        $active_button, $progress_bar, $edit_link_button . $delete_button . $ticket_button]);
}

count($current_data) > 15 ? $current_scroll = 1 : $current_scroll = 0;

$past_headers = ['#', 'Event Name', 'Event Type', 'Event Dates', 'Attendee Count', 'Event Management'];
$past_data    = [];

foreach($past_events as $event) {
    $csrf = csrf_field();

    /*
     * Not needed for past events
     *
    $edit_button   = '<form method="post" action="event_add-edit.php">' .
        $csrf . '
        <label for="mySubmit' . $event->eventID . '" class="btn btn-info btn-xs"><i class="fa fa-pencil"></i> Edit</label>
        <input type="hidden" name="eventID" value="' . $event->eventID . '">
        <input type="hidden" name="function" value="edit">
        <input id="mySubmit' . $event->eventID . '" type="submit" value="Go" class="hidden" />
        </form>';
    $delete_button = '<form method="post" action="include/add-edit-delete_event.php">' .
        $csrf .
        '<button class="btn btn-danger btn-xs" data-toggle="confirmation"
                data-btn-ok-label="Continue" data-btn-ok-icon="glyphicon glyphicon-share-alt"
                data-btn-ok-class="btn-success"
                data-btn-cancel-label="Stop!" data-btn-cancel-icon="glyphicon glyphicon-ban-circle"
                data-btn-cancel-class="btn-danger"
                data-title="Are you sure?" data-content="This cannot be undone.">
            <i class="fa fa-trash"></i> Delete</button>
            <input type="hidden" name="eventID" value="' . $event->eventID . '">
            <input type="hidden" name="function" value="delete">
            <input id="myDelete" type="submit" value="Go" class="hidden" /></form>';
     *
     *
     */

    $ticket_button = '<form method="post" action="/event-tickets/' . $event->eventID . '">' . $csrf .
        '<label for="TicketSubmit' . $event->eventID . '" class="btn btn-info btn-xs"><i class="fa fa-pencil"></i> Tickets</label>
            <input type="hidden" name="eventID" value="' . $event->eventID . '">
            <input type="hidden" name="eventName" value="' . $event->eventID . '">
            <input type="hidden" name="function" value="ticket">
            <input id="TicketSubmit' . $event->eventID . '" type="submit" value="Go" class="hidden" /></form>';

    array_push($past_data, [$event->eventID, $event->eventName, $event->etName,
        "<nobr>" . $event->eventStartDateF . "  - </nobr><br><nobr>" . $event->eventEndDateF . "</nobr>",
        $event->cnt, $ticket_button]);
}

count($past_data) > 15 ? $past_scroll = 1 : $past_scroll = 0;

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

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
                @include('v1.parts.datatable', ['headers' => $current_headers, 'data' => $current_data, 'scroll' => $current_scroll])
            </div>
            <div class="tab-pane fade" id="tab_content2" aria-labelledby="past_events-tab">
                <p>&nbsp;</p>
                @include('v1.parts.datatable', ['headers' => $past_headers, 'data' => $past_data, 'scroll' => $past_scroll])
            </div>

        </div>
    </div>
    @include('v1.parts.end_content')

@endsection

@section('scripts')
    @include('v1.parts.footer-datatable')
    <script>
        $('[data-toggle=confirmation]').confirmation();
    </script>
    <script>
        $(document).ready(function () {
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
            });
            $('#datatable-fixed-header').DataTable().search( '' ).draw();
        });
    </script>
@endsection