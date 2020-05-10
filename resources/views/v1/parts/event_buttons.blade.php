@php
    /**
     * Comment: Buttons to display on Event Management pages
     * Created: 10/21/2018
     *
     * @var $event
     *
     */

    $today = \Carbon\Carbon::now();
    if (!isset($size)) {
        $size = 'sm';
    }

    if ($today->gt($event->eventEndDate)) {
        $past = 1;
    } else {
        $past = 0;
    }

    $homeURL = env('APP_URL') . '/manage_events';
    $editURL = env('APP_URL') . '/event/' . $event->eventID . '/edit';
    $displayURL = env('APP_URL') . '/events/' . $event->slug;
    $tktURL = env('APP_URL') . '/event-tickets/' . $event->eventID;
    $eventDiscountURL = env('APP_URL') . '/eventdiscount/' . $event->eventID;
    $trackURL = env('APP_URL') . '/tracks/' . $event->eventID;
    $rptURL = env('APP_URL') . '/eventreport/' . $event->slug;
    $copyURL = env('APP_URL') . '/eventcopy/' . $event->slug;
    $checkinURL = env('APP_URL') . '/checkin/' . $event->slug;
    $recordURL = env('APP_URL') . '/record_attendance/' . $event->slug;

    if(!isset($suppress)){
        $loc = $event->location;
        $loc_tooltip = trans('messages.fields.loc') . ": " . view('v1.parts.location_display', compact('event', 'loc'))->render();
    }

@endphp

@if(!isset($suppress))
    <!-- div class="col-xs-1" -->
    <a href='{{ $homeURL }}' class='btn btn-gray btn-{{ $size }}' data-toggle='tooltip' data-placement='right'
       title='{{ trans('messages.buttons.return') }}'><i class='fas fa-fw fa-home'></i></a>
    <!-- /div -->

    <a href="" class="btn btn-green btn-{{ $size }}" data-toggle="tooltip" data-placement="bottom"
       title="{{ strip_tags($loc_tooltip) }}"><i class="fal fa-map-marked-alt"></i></a>

@endif

@if($event->ok_to_display() && !$past)
    <!-- div class="col-xs-1" -->
    <a target='_new' href='{{ $displayURL }}' class='btn btn-primary btn-{{ $size }}' data-toggle='tooltip'
       data-placement='top'
       title='{{ trans('messages.headers.ev_prev') }}'><i class='far fa-fw fa-eye'></i></a>
    <!-- /div -->
@else
    <!-- div class="col-xs-1" -->
    <a target='_new' href='{{ $displayURL . "/1" }}' class='btn btn-yellow btn-{{ $size }}' data-toggle='tooltip'
       data-placement='top'
       title='{{ trans('messages.headers.ev_prev') }}'><i style="color:black;" class='far fa-fw fa-eye'></i></a>
    <!-- /div -->
@endif

@if(!$past)
    <!-- div class="col-xs-1" -->
    <a href='{{ $editURL }}' class='btn btn-primary btn-{{ $size }}' data-toggle='tooltip' data-placement='top'
       title='{{ trans('messages.fields.edit_event') }}'><i class='far fa-fw fa-pencil'></i></a>
    <!-- /div -->

    <!-- div class="col-xs-1" -->
    <a href='{{ $eventDiscountURL }}' class='btn btn-success btn-{{ $size }}' data-toggle='tooltip' data-placement='top'
       title='{{ trans('messages.fields.edit_event'). " " . trans('messages.fields.discs') }}'>{!! trans('messages.symbols.cur') !!}</a>
    <!-- /div -->

    <!-- div class="col-xs-1" -->
    <a href='{{ $tktURL }}' class='btn btn-info btn-{{ $size }}' data-toggle='tooltip' data-placement='top'
       title='{{ trans('messages.buttons.edit_tkt') }}'><i class='far fa-fw fa-ticket-alt'></i></a>
    <!-- /div -->

    @if($event->hasTracks)
        <!-- div class="col-xs-1" -->
        <a href='{{ $trackURL }}' class='btn btn-brown btn-{{ $size }}' data-toggle='tooltip' data-placement='top'
           title='{{ trans('messages.buttons.t&s_edit') }}'><i class='far fa-fw fa-container-storage'></i></a>
        <!-- /div -->
    @endif
@elseif(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
    <a href='{{ $editURL }}' class='btn btn-gray btn-{{ $size }}' data-toggle='tooltip' data-placement='top'
       title='{{ trans('messages.fields.edit_event') }}'><i class='far fa-fw fa-pencil'></i></a>
    <a href='{{ $eventDiscountURL }}' class='btn btn-gray btn-{{ $size }}' data-toggle='tooltip' data-placement='top'
       title='{{ trans('messages.fields.edit_event'). " " . trans('messages.fields.discs') }}'>{!! trans('messages.symbols.cur') !!}</a>
    <a href='{{ $tktURL }}' class='btn btn-gray btn-{{ $size }}' data-toggle='tooltip' data-placement='top'
       title='{{ trans('messages.buttons.edit_tkt') }}'><i class='far fa-fw fa-ticket-alt'></i></a>
    @if($event->hasTracks)
        <a href='{{ $trackURL }}' class='btn btn-gray btn-{{ $size }}' data-toggle='tooltip' data-placement='top'
           title='{{ trans('messages.buttons.t&s_edit') }}'><i class='far fa-fw fa-container-storage'></i></a>
    @endif
@endif

<!-- div class="col-xs-1" -->
<a href='{{ $rptURL }}' class='btn btn-purple btn-{{ $size }}' data-toggle='tooltip' data-placement='top'
   title='{{ trans('messages.headers.ev_rpt') }}'><i class='far fa-fw fa-chart-bar'></i></a>
<!-- /div -->

<!-- div class="col-xs-1" -->
<a href='{{ $copyURL }}' class='btn btn-deep-orange btn-{{ $size }}' data-toggle='tooltip' data-placement='top'
   onclick="return confirm('{{ trans('messages.tooltips.sure_copy') }}');"
   title='{{ trans('messages.headers.ev_copy') }}'><i class='far fa-fw fa-copy'></i></a>
<!-- /div -->

@if($event->hasTracks > 0 && $event->checkin_period())
    <!-- div class="col-xs-1" -->
    <a href='{{ $recordURL }}' class='btn btn-pink btn-{{ $size }}' data-toggle='tooltip' data-placement='top'
       title='{{ trans('messages.buttons.rec_att') }}'><i class='far fa-fw fa-check-square'></i></a>
    <!-- /div -->
@endif

@if($event->checkin_time())
    @if($event->hasTracks > 0)
        <!-- div class="col-xs-1" -->
        <a href='{{ $checkinURL }}' class='btn btn-pink btn-{{ $size }}' data-toggle='tooltip' data-placement='top'
           title='{{ trans('messages.buttons.chk_vol') }}'><i class='far fa-fw fa-clipboard'></i></a>
        <!-- /div -->
    @else
        <a href='{{ $rptURL }}#tab_content7' class='btn btn-pink btn-{{ $size }}' data-toggle='tooltip'
           data-placement='top'
           title='{{ trans('messages.buttons.chk_vol') }}'><i class='far fa-fw fa-clipboard'></i></a>
    @endif
@elseif(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
    @if($event->hasTracks > 0)
        <a href='{{ $checkinURL }}' class='btn btn-gray btn-{{ $size }}' data-toggle='tooltip' data-placement='top'
           title='{{ trans('messages.buttons.chk_vol') }}'><i class='far fa-fw fa-clipboard'></i></a>
    @else
        <a href='{{ $rptURL }}#tab_content7' class='btn btn-gray btn-{{ $size }}' data-toggle='tooltip'
           data-placement='top'
           title='{{ trans('messages.buttons.chk_vol') }}'><i class='far fa-fw fa-clipboard'></i></a>
    @endif
@endif

@if(!$event->isActive && $event->regCount() == 0)
    <!-- div class="col-xs-1" -->
    {!! Form::open(['url' => env('APP_URL').'/event/' . $event->eventID, 'method' => 'DELETE']) !!}
    <button class="btn btn-danger btn-{{ $size }}" data-toggle="tooltip" data-placement="top"
            onclick="return confirm('{{ trans('messages.tooltips.sure') }}');"
            title="{{ trans('messages.buttons.delete') }}"> {!! trans('messages.symbols.trash') !!}</button>
    <input id="myDelete" type="submit" value="Go" class="hidden"/>
    {!! Form::close() !!}
    <!-- /div -->
@endif