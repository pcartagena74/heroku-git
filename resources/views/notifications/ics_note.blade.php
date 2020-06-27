@php
    $loc = $event->location; $time = 0;
    $loc_tooltip = view('v1.parts.location_display', compact('event', 'loc', 'time'))->render();
@endphp

@component('mail::message', ['orgURL' => $orgURL, 'logoPath' => $logoPath])
# {!! $event->eventName !!}

{!! $loc_tooltip !!}

@if(isset($event->postRegInfo))
@component('mail::promotion')
# {!! trans('messages.notifications.RegNote.postRegHeader') !!}
{!! $event->postRegInfo !!}
@endcomponent
@endif

{!! $event->eventDescription !!}

@endcomponent
