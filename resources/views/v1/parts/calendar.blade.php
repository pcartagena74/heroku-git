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
            <b>{!! $header !!} </b>
        </div>
        <div class="panel-body">
            {!! $calendar->calendar() !!}
        </div>
    </div>
</div>
<script nonce="{{ $cspScriptNonce }}">
    function strip(html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        return truncateString(doc.body.textContent, 300) || "";
    }

    function truncateString(str, num) {
        if (str.length <= num) {
            return str
        }
        return str.slice(0, num) + '...'
    }
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
<script src="https://unpkg.com/tooltip.js/dist/umd/tooltip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.2.7/fullcalendar.min.js"></script>
{!! $calendar->script() !!}