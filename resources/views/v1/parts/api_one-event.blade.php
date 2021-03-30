@php
    /**
     * Comment: individual display of event with or without header
     * Created: 3/6/2021
     *
     * @var $e
     * @var $props
     */

$header = [];

    $sep = $props[0]->value;
    $btntxt = $props[2]->value;
    $btncolor = $props[3]->value;
    $hdrcolor = $props[4]->value;
    $btnsize = $props[5]->value;
    $hdr = $props[6]->value;
    $chars = $props[7]->value;
    $hdr_array = explode($sep, $hdr);

foreach($hdr_array as $hdr) {
    $working_string = '';
    switch($hdr){
        case "category":
            $working_string = $e->category->catTXT;
            break;
        case 'et':
            $working_string = $e->event_type->etName;
            break;
        case 'times':
            $working_string = $e->eventStartDate->format('g:i');
            $working_string .= "-".$e->eventEndDate->format('g:i A');
            break;
        case 'pdus':
            if($e->main_session->leadAmt) $working_string .= $e->main_session->leadAmt . " " . trans('messages.pdus.lead');
            if($e->main_session->stratAmt) {
                if(strlen($working_string)>0) $working_string .= ", ";
                $working_string .= $e->main_session->stratAmt . " " . trans('messages.pdus.strat');
            }
            if($e->main_session->techAmt) {
                //if(strlen($working_string)>0 && substr($working_string, -2, 1) !== ",") $working_string .= ", ";
                if(strlen($working_string)>0) $working_string .= ", ";
                $working_string .= $e->main_session->techAmt . " " . trans('messages.pdus.tech');
            }
            $working_string .= " " . trans_choice('messages.pdus.pdus', $e->main_session->leadAmt + $e->main_session->stratAmt + $e->main_session->techAmt);
            break;
        case "memprice":
            $working_string = trans('messages.fields.member'). ": ";
            $working_string .= trans('messages.symbols.cur');
            $working_string .= number_format($e->tickets->first()->memberBasePrice, 0);
            break;
        case "nonprice":
            $working_string = trans('messages.fields.nonmbr'). ": ";
            $working_string .= trans('messages.symbols.cur');
            $working_string .= number_format($e->tickets->first()->nonmbrBasePrice, 0);
            break;
    }
    array_push($header, $working_string);
}

$hdr = implode(" ".$sep." ", $header);

@endphp

{{--
This is the image that was displayed in MyEventGuru.  There was a request to remove it.
    <div class="col-xs-2" style="float: left;">
        <img src="{{ env('APP_URL') }}/images/eventlist.jpg"
             alt='{{ trans('messages.codes.img') }}' height='78' width='90' border='0'/>
    </div>
--}}
<div class="col-xs-12" style="text-align: left;">
    <div class="col-xs-9">
        <b> {!! $e->eventName !!}</b>
    </div>
    <div class="col-xs-3 text-center">
        @lang('messages.common.on') {{ $e->eventStartDate->toFormattedDateString() }}
    </div>
    <div class="col-xs-12 container">
        @if($hdr)
        <b class="text-{{ $hdrcolor }}">{!! $hdr !!}</b>
        <br/>
        @endif
        {!! truncate_saw(strip_tags($e->eventDescription, '<br>'), $chars) !!}
    </div>
    <div class="col-xs-9">
        <b>@lang('messages.fields.loc'): </b>{{ $e->location->locName ?? trans('messages.messages.unknown') }}
    </div>
    @if($past)
        <div class="col-xs-3 text-center">
            <a class="btn btn-{{ $btncolor }} btn-{{ $btnsize }}" target="_new"
               href="https://www.mcentric.org/events/{{ $e->slug }}">{!! $btntxt !!}</a>
        </div>
    @else
        <div class="col-xs-3 text-center">
            <b> {!! trans_choice('messages.headers.et', 1) !!}:</b>
            {!! $e->event_type->etName ?? trans('messages.messages.unknown') !!}
        </div>
    @endif
</div>
