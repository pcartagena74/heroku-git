@php
    /**
     * Comment: Used to display /eventlist/{orgID}/{etID}
     * Created: 11/28/2017
     * @var $events
     * @var $etID
     * @var $tag
     * @var $admin_props
     */

    use mCentric\LaravelFullcalendar\Facades\Calendar;

    /*
    $sep = $admin_props[0]->value;
    $btntxt = $admin_props[2]->value;
    $btncolor = $admin_props[3]->value;
    $hdrcolor = $admin_props[4]->value;
    $btnsize = $admin_props[5]->value;
    $hdr = $admin_props[6]->value;
    $chars = $admin_props[7]->value;
    */
    $ban_bkgd = $admin_props[8]->value;
    $ban_text = $admin_props[9]->value;

    function truncate_saw($string, $limit, $break = ".", $pad = "...")
    {
        if (strlen($string) <= $limit) return $string;
        if (false !== ($max = strpos($string, $break, $limit))) {
            if ($max < strlen($string) - 1) {
                $string = substr($string, 0, $max) . $pad;
            }
        }
        return $string;
    }

    if (null === $etID || preg_match('/,/', $etID)) {
        $event_tag = $tag;
    } else {
        $event_tag = $tag->etName;
    }
    // ({{ count($events) }})  // Add to header for count to debug
    // @lang('messages.headers.tagged')
@endphp
@extends('v1.layouts.no-auth_no-nav_simple')
@section('header')
    <base target="_new">
    {{--
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.css"/>
    </head>
@endsection

@section('content')
    @if($cnt > 0)
        @if(!$cal)
            <table class="table table-bordered table-striped table-sm condensed jambo_table" width="100%"
                   id="eventlisting">
                <thead>
                <tr>
                    <td><b>{{ $org->orgName }}: {{ $event_tag }}</b></td>
                </tr>
                </thead>
                <tbody>
                @foreach($events as $e)
                    <tr>
                        <td>
                            @include('v1.parts.api_one-event', ['e' => $e, 'props' => $admin_props])
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else

            @php
                $cal_events = [];
                foreach ($events as $key => $value) {
                    $cal_events[] = Calendar::event(
                        $value->eventName,
                        false,
                        new \DateTime($value->eventStartDate),
                        new \DateTime($value->eventEndDate),
                        $value->eventID,
                        // Add color
                        [
                            'color' => '#0000FF',
                            'textColor' => '#FFFFFF',
                            'url' => env('APP_URL')."/events/$value->slug",
                            'description' => $value->eventDescription,
                        ]
                    );
                }
                //dd($cal_events, $events);
                $calendar = Calendar::addEvents($cal_events)
                ->setCallbacks([
                    'eventRender' => 'function(event, element) {
                        element.popover({
                            container: "body",
                            width: "90%",
                            html: true,
                            placement: "top",
                            trigger: "hover",
                            title: "<b>"+event.title+"</b>",
                            content: strip(event.description)
                        });
                     }'
                ]);

            @endphp
            @include('v1.parts.calendar', ['header' => "$org->orgName: $event_tag"])
        @endif
    @else
        @lang('messages.messages.no_events', ['which' => strtolower(trans_choice('messages.var_words.time_period', $past))])
    @endif
@endsection

@section('scripts')
    {{-- Disabled sorting to prevent alpha-date sort fail --}}
    @if($cnt > 15)
        @include('v1.parts.footer-datatable')
        <script nonce="{{ $cspScriptNonce }}">
            $(document).ready(function () {
                //$.fn.dataTable.moment('MMM D, YYYY');
                $('#eventlisting').DataTable({
                    "ordering": false,
                    "fixedHeader": true
                });
            });
        </script>
    @endif
@endsection
