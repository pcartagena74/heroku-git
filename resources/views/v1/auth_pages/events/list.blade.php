<?php
/**
 * Comment:
 * Created: 2/2/2017
 *
 * $header, $subheader, $w1, $w2, $r1, $r2, $r3
 *
 */

$current_headers = [trans('messages.headers.event_dates'), trans('messages.fields.event'), trans('messages.profile.type'),
    trans('messages.headers.status'), trans('messages.fields.count'), trans('messages.nav.ev_mgmt')];
$current_data    = [];

$today = \Carbon\Carbon::now();

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
        <div class="progress-bar bg-green" role="progressbar" data-transitiongoal="' . $event->registrations_count . '"></div>
        </div><small>' . $event->registrations_count . ' attendees</small>';

    $editURL    = env('APP_URL').'/event/' . $event->eventID . '/edit';
    $displayURL = env('APP_URL').'/events/' . $event->slug;
    $tktURL = env('APP_URL').'/event-tickets/'. $event->eventID;
    $eventDiscountURL = env('APP_URL').'/eventdiscount/' . $event->eventID;
    $trackURL = env('APP_URL').'/tracks/' . $event->eventID;
    $rptURL = env('APP_URL').'/eventreport/' . $event->slug;
    $copyURL = env('APP_URL').'/eventcopy/' . $event->slug;
    $checkinURL = env('APP_URL').'/checkin/' . $event->slug;

    $edit_link_button    = "<a href='$editURL' class='btn btn-primary btn-sm' data-toggle='tooltip' data-placement='top' title='".trans('messages.fields.edit_event')."'><i class='far fa-fw fa-pencil'></i></a>";
    $track_link_button    = "<a href='$trackURL' class='btn btn-success btn-sm' data-toggle='tooltip' data-placement='top' title='" . trans('messages.buttons.t&s_edit') . "'><i class='far fa-fw fa-pencil'></i></a>";
    if($event->hasTracks == 0){
        $track_link_button = '';
    }
    $rpt_link_button    = "<a href='$rptURL' class='btn btn-purple btn-sm' data-toggle='tooltip' data-placement='top' title='" . trans('messages.headers.ev_rpt') . "'><i class='far fa-fw fa-chart-bar'></i></a>";
    $copy_link_button    = "<a href='$copyURL' class='btn btn-deep-orange btn-sm' data-toggle='tooltip' data-placement='top' title='" . trans('messages.headers.ev_copy') . "'><i class='far fa-fw fa-copy'></i></a>";
    if($event->isActive){
        $display_link_button =
            "<a target='_new' href='$displayURL' class='btn btn-primary btn-sm' data-toggle='tooltip' data-placement='top' title='" . trans('messages.headers.ev_prev') . "'><i class='far fa-fw fa-eye'></i></a>";
    } else {
        $display_link_button =
            "<a target='_new' style='color:black;' href='$displayURL/1' class='btn btn-yellow btn-sm' data-toggle='tooltip' data-placement='top' title='" . trans('messages.headers.ev_prev') . "'><i class='far fa-fw fa-eye'></i></a>";
    }
    $eventDiscount_button =
       "<a href='$eventDiscountURL' class='btn btn-success btn-sm' data-toggle='tooltip' data-placement='top' title='".
        trans('messages.fields.edit_event'). " " . trans('messages.fields.discs') ."'>" . trans('messages.symbols.cur') . "</a>";
    $delete_button       = Form::open(['url' => env('APP_URL').'/event/' . $event->eventID, 'method' => 'DELETE']) .
        '<button class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="'. trans('messages.buttons.delete') .'">' . trans('messages.symbols.trash') . '</button>
            <input id="myDelete" type="submit" value="Go" class="hidden" /></form>';
    // Deletion should not be possible when 1. event is active 2. event has registrations.
    if($event->isActive || $event->registrations_count > 0) {
        $delete_button = '';
    }

    $ticket_button =
        "<a href='$tktURL' class='btn btn-info btn-sm' data-toggle='tooltip' data-placement='top' title='".
                          trans('messages.buttons.edit_tkt') . "'><i class='far fa-fw fa-ticket-alt'></i></a>";
    $checkin_button    = "<a href='$checkinURL' class='btn btn-purple btn-sm' data-toggle='tooltip' data-placement='top'
                          title='" . trans('messages.buttons.chk_att') . "'><i class='far fa-fw fa-check-square-o'></i></a>";

    if($today < $event->eventStartDate){
        $checkin_button = '';
    }

    // ->format('D, M d, Y h:i A')
    array_push($current_data, ["<nobr>" . $event->eventStartDate->toDayDateTimeString() . "  - </nobr><br><nobr>" .
        $event->eventEndDate->toDayDateTimeString() . "</nobr>",
        $event->eventName, $event->etName, $active_button, $progress_bar, $display_link_button . $edit_link_button .
        $eventDiscount_button . $ticket_button . $track_link_button . $rpt_link_button  . $copy_link_button .
        $checkin_button . $delete_button]);
}

count($current_data) >= 15 ? $current_scroll = 1 : $current_scroll = 0;

$past_headers = [trans('messages.headers.event_dates'), trans('messages.fields.event'), trans('messages.profile.type'),
                 trans('messages.fields.count'), trans('messages.nav.ev_mgmt')];

$past_data    = [];

foreach($past_events as $event) {
    $rptURL = env('APP_URL').'/eventreport/' . $event->slug;
    $tktURL = env('APP_URL').'/event-tickets/'. $event->eventID;
    $copyURL = env('APP_URL').'/eventcopy/' . $event->slug;
    $editURL    = env('APP_URL').'/event/' . $event->eventID . '/edit';

    // 10/7/17: blanked out button to remove from past event display
    $ticket_button = '';
        //"<a href='$tktURL' class='btn btn-info btn-xs'><i class='fa fa-pencil'></i> Tickets</a>";
    $edit_link_button    = "<a href='$editURL' class='btn btn-primary btn-sm' data-toggle='tooltip' data-placement='top' title='Edit Event'><i class='far fa-fw fa-pencil'></i></a>";
    $rpt_link_button    = "<a href='$rptURL' class='btn btn-purple btn-sm' data-toggle='tooltip' data-placement='top' title='Event Report'><i class='far fa-fw fa-chart-bar'></i></a>";
    $copy_link_button    = "<a href='$copyURL' class='btn btn-deep-orange btn-sm' data-toggle='tooltip' data-placement='top' title='Copy Event'><i class='far fa-fw fa-copy'></i></a>";
    $delete_button       = Form::open(['url' => env('APP_URL').'/event/' . $event->eventID, 'method' => 'DELETE']) .
                           '<button class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Delete"><i class="far fa-fw fa-trash-alt"></i></button>
                            <input id="myDelete" type="submit" value="Go" class="hidden" /></form>';
    if($event->cnt > 0 && !Entrust::hasRole('Developer')) {
        $delete_button = '';
    }

    if(!Entrust::hasRole('Developer')){
        $edit_link_button = '';
    }

    array_push($past_data, [ "<nobr>" . $event->eventStartDate->format('Y-m-d') . "  - </nobr><br><nobr>" . $event->eventEndDate->format('Y-m-d') . "</nobr>",
        $event->eventName, $event->etName, $event->registrations_count, $edit_link_button . $rpt_link_button . $copy_link_button . $delete_button]);
}
count($past_data) > 15 ? $past_scroll = 1 : $past_scroll = 0;

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('header')
    @include('v1.parts.header-datatable')
@endsection

@section('content')

    @include('v1.parts.start_content', ['header' => '', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <div class="col-md-12 col-sm-12 col-xs-12">
        <ul id="myTab" class="nav nav-tabs bar_tabs nav-justified" role="tablist">
            <li class="active"><a href="#tab_content1" id="current_events-tab" data-toggle="tab"
                                  aria-expanded="true"><b>@lang('messages.fields.up_event')</b></a></li>
            <li class=""><a href="#tab_content2" id="past_events-tab" data-toggle="tab" aria-expanded="false"><b>@lang('messages.fields.past_events')</b></a></li>
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
                    @lang('messages.messages.no_events')
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
            @if(count($current_data) >= 15)
            $('#current_events').DataTable({
                "fixedHeader": true,
                "order": [[ 0, "desc" ]]
            });
            @endif

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