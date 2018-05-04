<?php
/**
 * Comment:
 * Date: 5/3/2018
 */

$year = '';
?>

@foreach($event_list as $e)
    @if($year != $e->eventStartDate->year)
        @if($year != '')
            </ul>
        @endif

        <b>{!! $year = $e->eventStartDate->year !!}</b>
        <ul>
    @endif
            <li>{!! ucwords(strtolower($e->eventName)) !!}</li>
@endforeach
        </ul>
