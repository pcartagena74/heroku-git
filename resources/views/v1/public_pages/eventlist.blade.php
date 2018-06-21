<?php
/**
 * Comment: Used to display /eventlist/{orgID}/{etID}
 * Created: 11/28/2017
 */

function truncate_saw($string, $limit, $break = ".", $pad = "...") {
    if (strlen($string) <= $limit) return $string;
    if (false !== ($max = strpos($string, $break, $limit))) {
        if ($max < strlen($string) - 1) {
            $string = substr($string, 0, $max) . $pad;
        }
    }
    return $string;
}

if($etID == 99){
    $event_tag = 'All Events';
} else {
    $event_tag = $tag->etName;
}

?>
@extends('v1.layouts.no-auth_no-nav_simple')
@section('content')
    @if($cnt > 0)
    <table class="table table-bordered table-striped condensed jambo_table" width="100%" id="eventlisting">
        <thead>
        <tr><td><b>{{ $org->orgName }} Events tagged as '{{ $event_tag }}'</b></td></tr>
        </thead>
        <tbody>
        @foreach($events as $e)
            <tr>
                <td>
                    <div class="col-sm-1" style="float: left;">
                        <img src="{{ env('APP_URL') }}/images/eventlist.jpg" alt='Event Image' height='79'
                             width='90' border='0'/>
                    </div>
                    <div class="col-sm-11" style="text-align: left;">
                        <div class="col-sm-9">
                            <b>{!! $e->eventName !!}</b>
                        </div>
                        <div class="col-sm-3">
                            on {{ $e->eventStartDate->toFormattedDateString() }}
                        </div>
                        <div class="col-sm-12">
                            {!! truncate_saw($e->eventDescription, 400) !!}
                        </div>
                        <div class="col-sm-9">
                            <b>Location: </b>{{ $e->location->locName }}
                        </div>
                        <div class="col-sm-3" style="text-align: center;">
                            <a class="btn btn-danger btn-sm" href="https://www.mcentric.org/events/{{ $e->slug }}">R E G I S T E R</a>
                        </div>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @else
        There are no <b class="red">active</b> future events at this time.
    @endif
@endsection

@section('scripts')
    @if($cnt > 10)
    @include('v1.parts.footer-datatable')
    <script>
        $(document).ready(function() {
            $('#eventlisting').DataTable({
                "fixedHeader": true
            });
        });
    </script>
    @endif
@endsection