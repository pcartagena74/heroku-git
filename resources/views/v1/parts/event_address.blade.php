@php
/**
 * Comment: Created to have a reusable event address display
 * Created: 6/23/2019
 */
@endphp

{{ $event->eventStartDate->format('n/j/Y g:i A') }} - {{ $event->eventEndDate->format('n/j/Y g:i A') }}<br>
<b>{{ $loc->locName }}</b><br>
@if(!$loc->isVirtual)
    {{ $loc->addr1 }} <i class="fas fa-circle fa-xs"></i> {{ $loc->city }}
    , {{ $loc->state }} {{ $loc->zip }}
@endif
