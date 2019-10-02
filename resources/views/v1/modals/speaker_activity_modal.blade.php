<?php
/**
 * Comment:
 * Date: 9/30/2019
 */

$year = '';
?>

@if(count($speaker_event_list) > 0)
    <h2>@lang('messages.headers.sAct'):
        {!! $speaker_event_list->first()->prefName ?? $speaker_event_list->first()->firstName !!}
        {!! $speaker_event_list->first()->lastName !!}
    </h2>
    @foreach($speaker_event_list as $e)
    <?php
    $date = \Carbon\Carbon::create($e->eventStartDate);
    $e->eventStartDate = $date;
    ?>
    @if($year != $e->eventStartDate->year)
    @if($year != '')
    </ul>
    @endif

<b>{!! $year = $e->eventStartDate->year !!}</b>
<ul>
    @endif
    <li>
        {!! ucwords(strtolower($e->eventName)) !!} <br/>
        {!! $e->eventStartDate->format('M d, Y') !!}<p>

    <?php
    if (null === $e->sessionID) {
        $event = \App\Event::find($e->eventID);
        $es = $event->mainSession;
    } else {
        $es = $e->sessionID;
    }

    $surveys = \App\RSSurvey::where([
        ['sessionID', '=', $es]
    ])
        ->selectRaw("avg(engageResponse) 'engage', avg(takeResponse) 'take', avg(contentResponse) 'content', avg(styleResponse) 'style', count(*) 'count'")
        ->first();
    ?>
    @if($surveys->count > 0)
        <b>{!! $surveys->count !!} @lang('messages.surveys.surveys')</b>
        <ul>
            <li> @lang('messages.surveys.engage'): {!! $surveys->engage !!} </li>
            <li> @lang('messages.surveys.take'): {!! $surveys->take !!} </li>
            <li> @lang('messages.surveys.content'): {!! $surveys->content !!} </li>
            <li> @lang('messages.surveys.style'): {!! $surveys->style !!} </li>
        </ul>
        <p>
    @endif
    </li>

    @endforeach
</ul>
@endif
