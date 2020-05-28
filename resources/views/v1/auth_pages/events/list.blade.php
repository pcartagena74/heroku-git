@php
/**
 * Comment:
 * Created: 2/2/2017
 *
 * $header, $subheader, $w1, $w2, $r1, $r2, $r3
 * @var $past
 * @var $current_events
 * @var $past_events
 *
 */

$current_headers = [trans('messages.headers.event_dates'), trans('messages.fields.event'), trans('messages.profile.type'),
    trans('messages.headers.status'), trans('messages.fields.count'), trans('messages.nav.ev_mgmt')];
$current_data = [];

$today = \Carbon\Carbon::now();

if (!$past) {
    foreach ($current_events as $event) {
        $csrf = csrf_field();

        $progress_bar = '<div class="progress progress_sm">
        <div class="progress-bar bg-green" role="progressbar" data-transitiongoal="' . $event->registrations_count . '"></div>
        </div><small>' . $event->registrations_count . ' attendees</small>';

        $active_button = '<button onclick="javascript:activate(' . $event->eventID . ')" class="btn ';
        if ($event->isActive) {
            $active_button .= "btn-success btn-sm";
        } else {
            $active_button .= "btn-cancel btn-xs";
        }
        $active_button .= '">';
        if ($event->isActive) {
            $active_button .= "<b>" . trans('messages.reg_status.active') . "</b>";
        } else {
            $active_button .= trans('messages.reg_status.inactive');
        }
        $active_button .= "</button>";

        $buttons = view('v1.parts.event_buttons', ['event' => $event, 'suppress' => 1, 'size' => 'xs'])->render();

        array_push($current_data, ["<nobr>" . $event->eventStartDate->format('Y-m-d') . "  - </nobr><br><nobr>" .
            $event->eventEndDate->format('Y-m-d') . "</nobr>", $event->eventName,
            Lang::has('messages.event_types.' . $event->etName) ? trans_choice('messages.event_types.' . $event->etName, 1) : $event->etName,
            $active_button, $progress_bar, $buttons]);
    }

    count($current_data) >= 15 ? $current_scroll = 1 : $current_scroll = 0;
}

$past_headers = [trans('messages.headers.event_dates'), trans('messages.fields.event'), trans('messages.profile.type'),
    trans('messages.fields.count'), trans('messages.nav.ev_mgmt')];

$past_data = [];

foreach ($past_events as $event) {

    $buttons = view('v1.parts.event_buttons', ['event' => $event, 'suppress' => 1, 'size' => 'xs'])->render();
    array_push($past_data, ["<nobr>" . $event->eventStartDate->format('Y-m-d') . "  - </nobr><br><nobr>" . $event->eventEndDate->format('Y-m-d') . "</nobr>",
        $event->eventName, $event->etName, $event->registrations_count, $buttons]);
}
count($past_data) > 15 ? $past_scroll = 1 : $past_scroll = 0;

if ($past) {
    $header = trans('messages.nav.ev_old');
} else {
    $header = '';
}
@endphp

@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('header')
    @include('v1.parts.header-datatable')
@endsection

@section('content')

    @include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    @if(!$past)
        <div class="col-md-12 col-sm-12 col-xs-12">
            <ul id="myTab" class="nav nav-tabs bar_tabs nav-justified" role="tablist">
                <li class="active"><a href="#tab_content1" id="current_events-tab" data-toggle="tab"
                                      aria-expanded="true"><b>@lang('messages.fields.up_event')</b></a></li>
                <li class=""><a href="#tab_content2" id="past_events-tab" data-toggle="tab"
                                aria-expanded="false"><b>@lang('messages.fields.past_events') ({{ $today->year - 1 }} - {{ $today->year }})</b></a></li>
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
                        @lang('messages.messages.no_events', ['which' => strtolower(trans('messages.fields.up'))])
                    @endif
                </div>
                <div class="tab-pane fade" id="tab_content2" aria-labelledby="past_events-tab">
                    @endif
                    <p>&nbsp;</p>
                    @include('v1.parts.datatable', ['headers' => $past_headers,
                        'data' => $past_data,
                        'id' => 'past_events',
                        'scroll' => $past_scroll])
                    @if(!$past)
                </div>
            </div>
        </div>
    @endif
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
        $(document).ready(function () {
            @if(count($current_data) >= 15)
            $('#current_events').DataTable({
                "fixedHeader": true,
                "order": [[0, "asc"]]
            });
            @endif

            $('#past_events').DataTable({
                "fixedHeader": true,
                "order": [[0, "desc"]]
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
                    window.location = "/manage_events";
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