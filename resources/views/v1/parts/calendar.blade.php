@php
/**
 * Comment: Created to display events in a calendar format
 *          Leverages Laravel Full Calendar package
 * Created: 1/2/2020
 */
@endphp


<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading">
            <b>{{ $org->orgName }}: {{ $event_tag }}</b>
        </div>
        <div class="panel-body" >
            {!! $calendar->calendar() !!}
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
<script src="https://unpkg.com/tooltip.js/dist/umd/tooltip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.js"></script>
{!! $calendar->script() !!}