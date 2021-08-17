@php
    /**
     * Comment:
     * Date: 9/30/2019
     *
     * @var $entry
     */

use App\Models\Event;
    $year = '';
@endphp

@if(count($speaker_event_list) > 0)
    <h2>@lang('messages.headers.sAct'):
        {!! $speaker_event_list->first()->prefName ?? $speaker_event_list->first()->firstName !!}
        {!! $speaker_event_list->first()->lastName !!}
    </h2>
    @foreach($speaker_event_list as $entry)
    @php
        $date = \Carbon\Carbon::create($entry->eventStartDate);
        $entry->eventStartDate = $date;
    @endphp

    @if($year != $entry->eventStartDate->year)
    @if($year != '')
    </ul>
@endif

<b>{!! $year = $entry->eventStartDate->year !!}</b>
<ul>
    @endif
    <li>
        {!! ucwords(strtolower($entry->eventName)) !!} <br/>
        {!! $entry->eventStartDate->format('M d, Y') !!}<p>

        @php
            $event = Event::find($entry->eventID);
            if (null === $entry->sessionID) {
                $es = $event->mainSession;
            } else {
                $es = $entry->sessionID;
            }
            $sess = \App\Models\EventSession::find($es);
        @endphp

        @include('v1.parts.session_stats', ['session' => $sess, 'event' => $event])
    </li>

    @endforeach
</ul>
@endif