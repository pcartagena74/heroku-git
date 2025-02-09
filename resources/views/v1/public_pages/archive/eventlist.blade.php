@php
    /**
     * Comment: Used to display /eventlist/{orgID}/{etID}
     * Created: 11/28/2017
     * @var $etID
     * @var $events
     */

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
@endphp
@extends('v1.layouts.no-auth_no-nav_simple')
@section('content')
    @if($cnt > 0)
        <table class="table table-bordered table-striped condensed jambo_table" width="100%" id="eventlisting">
            <thead>
            <tr>
                <td><b>{{ $org->orgName }} @lang('messages.headers.tagged') '{{ $event_tag }}'</b></td>
            </tr>
            </thead>
            <tbody>
            @foreach($events as $e)
                <tr>
                    <td>
                        @if(0)
                            <div class="col-xs-1" style="float: left;">
                                <img src="{{ env('APP_URL') }}/images/eventlist.jpg" alt='{{ trans('messages.codes.img') }}' height='79' width='90' border='0'/>
                            </div>
                        @endif
                        <div class="col-xs-11" style="text-align: left;">
                            <div class="col-xs-9">
                                <b> {!! $e->eventName !!}</b>
                            </div>
                            <div class="col-xs-3">
                                on {{ $e->eventStartDate->toFormattedDateString() }}
                            </div>
                            <div class="col-xs-12 container">
                                {!! truncate_saw(strip_tags($e->eventDescription, '<br>'), 300) !!}
                            </div>
                            <div class="col-xs-9">
                                <b>Location: </b>{{ $e->location->locName ?? trans('messages.messages.unknown') }}
                            </div>
                            @if(!$past)
                                <div class="col-xs-3" style="text-align: center;">
                                    <a class="btn btn-danger btn-sm" target="_new"
                                       href="https://www.mcentric.org/events/{{ $e->slug }}">@lang('messages.buttons.ex_register')</a>
                                </div>
                            @else
                                <div class="col-xs-3" style="text-align: left;">
                                    <b>@lang('messages.fields.category'):</b>
                                    {!! $e->event_type->etName ?? trans('messages.messages.unknown') !!}
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        @lang('messages.messages.no_events', ['which' => strtolower(trans_choice('messages.var_words.time_period', $past))])
    @endif
@endsection

@section('scripts')
    {{-- Disabled sorting to prevent alpha-date sort fail --}}
    @if($cnt > 15)
        @include('v1.parts.footer-datatable')
        <script>
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