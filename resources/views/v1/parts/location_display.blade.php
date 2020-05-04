@php
    /**
     * Comment: Component to smartly display an event location's address fields
     *          This component should be called within a set of DIV tags and assumes the parent page handles sizing, etc.
     * Created: 5/3/2020
     *
     * @var Location $loc
     * @var bool $map       displays google map based on address when == 1
     * @var bool $time      displays event start and end times when == 1
     * @var Event $event    required if $time == 1
     */

if(!isset($map)){
    $map = 0;
}

if(!isset($time)){
    $time = 0;
}

@endphp

@if($loc->isVirtual)
    {{ $loc->locName }}
    <br/>
@else
    @if($map)
        <div class="col-md-12 col-sm-12 col-xs-12" id="map_canvas" style="padding:15px;">
            <iframe class="col-md-14 col-sm-12 col-xs-12" frameborder="ssss" marginheight="0" marginwidth="0"
                    scrolling="no"
                    src="https://maps.google.it/maps?q={{ $loc->addr1 }} {{ $loc->city }},
                                        {{ $loc->state }} {{ $loc->zip }}&hl={{ $locale }}&output=embed">
            </iframe>
        </div>
    @endif
    @if($time)
        {{ $event->eventStartDate->format('n/j/Y g:i A') }}
        - {{ $event->eventEndDate->format('n/j/Y g:i A') }}
        <br>
    @endif

    <b> {{ $loc->locName }} </b>
    <br/>
    @if(!$loc->isVirtual)
        {{ $loc->addr1 }}
        <br/>
        {!! $loc->addr2 !!}
        @if($loc->addr2)
            <br />
        @endif
        {{ $loc->city }}, {{ $loc->state }} {{ $loc->zip }}
        <br/>
    @endif
@endif
