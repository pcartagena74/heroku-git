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
     * @var bool $no_title  displays the location name WITHOUT "Location: " when == 1
     */

if(!isset($map)){
    $map = 0;
}

if(!isset($time)){
    $time = 0;
}

if(!isset($no_title)) {
    $no_title = 0;
}

@endphp

@if($time)
    {{ $event->eventStartDate->format('n/j/Y g:i A') }}
    - {{ $event->eventEndDate->format('n/j/Y g:i A') }}
    <br>
@endif
@if($no_title)
    <b> {{ $loc->locName }} </b>
@else
    <b> {{ trans('messages.fields.loc') }}: {{ $loc->locName }} </b>
@endif

@if(!$loc->isVirtual)
    <br/>
    {{ $loc->addr1 }}
    @if($loc->addr2)
        <br/>
        {!! $loc->addr2 !!}
    @endif
    @if($loc->city && $loc->state)
        <br/>
        {{ $loc->city }}, {{ $loc->state }} {{ $loc->zip }}
    @endif
    <br/>
@endif

@if($map && !$loc->isVirtual)
    <div class="col-md-12 col-sm-12 col-xs-12" id="map_canvas" style="padding:15px;">
        <iframe class="col-md-14 col-sm-12 col-xs-12" frameborder="ssss" marginheight="0" marginwidth="0"
                scrolling="no"
                src="https://maps.google.it/maps?q={{ $loc->addr1 }} {{ $loc->city }},
                                        {{ $loc->state }} {{ $loc->zip }}&hl={{ $locale }}&output=embed">
        </iframe>
    </div>
@endif
