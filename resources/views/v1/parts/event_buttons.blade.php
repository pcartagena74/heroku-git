<?php
/**
 * Comment: Buttons to display on Event Management pages
 * Created: 10/21/2018
 */

$homeURL = env('APP_URL').'/manage_events';
$editURL    = env('APP_URL').'/event/' . $event->eventID . '/edit';
$displayURL = env('APP_URL').'/events/' . $event->slug;
$tktURL = env('APP_URL').'/event-tickets/'. $event->eventID;
$eventDiscountURL = env('APP_URL').'/eventdiscount/' . $event->eventID;
$trackURL = env('APP_URL').'/tracks/' . $event->eventID;
$rptURL = env('APP_URL').'/eventreport/' . $event->slug;
$copyURL = env('APP_URL').'/eventcopy/' . $event->slug;
$checkinURL = env('APP_URL').'/checkin/' . $event->slug;
$recordURL = env('APP_URL').'/record_attendance/' . $event->slug;

?>

<div class="col-xs-1">
    <a href='{{ $homeURL }}' class='btn btn-gray btn-sm' data-toggle='tooltip' data-placement='top'
       title='{{ trans('messages.buttons.return') }}'><i class='fas fa-fw fa-home'></i></a>
</div>
@if($event->ok_to_display())
    <div class="col-xs-1">
        <a target='_new' href='{{ $displayURL }}' class='btn btn-primary btn-sm' data-toggle='tooltip' data-placement='top'
           title='{{ trans('messages.headers.ev_prev') }}'><i class='far fa-fw fa-eye'></i></a>
    </div>
@else
    <div class="col-xs-1">
        <a target='_new' href='{{ $displayURL . "/1" }}' class='btn btn-yellow btn-sm' data-toggle='tooltip' data-placement='top'
           title='{{ trans('messages.headers.ev_prev') }}'><i style="color:black;" class='far fa-fw fa-eye'></i></a>
    </div>
@endif

<div class="col-xs-1">
    <a href='{{ $editURL }}' class='btn btn-primary btn-sm' data-toggle='tooltip' data-placement='top'
       title='{{ trans('messages.fields.edit_event') }}'><i class='far fa-fw fa-pencil'></i></a>
</div>

<div class="col-xs-1">
    <a href='{{ $eventDiscountURL }}' class='btn btn-success btn-sm' data-toggle='tooltip' data-placement='top'
       title='{{ trans('messages.fields.edit_event'). " " . trans('messages.fields.discs') }}'>{!! trans('messages.symbols.cur') !!}</a>
</div>

<div class="col-xs-1">
    <a href='{{ $tktURL }}' class='btn btn-info btn-sm' data-toggle='tooltip' data-placement='top'
       title='{{ trans('messages.buttons.edit_tkt') }}'><i class='far fa-fw fa-ticket-alt'></i></a>
</div>

@if($event->hasTracks)
    <div class="col-xs-1">
        <a href='{{ $trackURL }}' class='btn btn-brown btn-sm' data-toggle='tooltip' data-placement='top'
           title='{{ trans('messages.buttons.t&s_edit') }}'><i class='far fa-fw fa-container-storage'></i></a>
    </div>
@endif

<div class="col-xs-1">
    <a href='{{ $rptURL }}' class='btn btn-purple btn-sm' data-toggle='tooltip' data-placement='top'
       title='{{ trans('messages.headers.ev_rpt') }}'><i class='far fa-fw fa-chart-bar'></i></a>
</div>

<div class="col-xs-1">
    <a href='{{ $copyURL }}' class='btn btn-deep-orange btn-sm' data-toggle='tooltip' data-placement='top'
       onclick="return confirm('{{ trans('messages.tooltips.sure_copy') }}');"
       title='{{ trans('messages.headers.ev_copy') }}'><i class='far fa-fw fa-copy'></i></a>
</div>

@if(!$event->isActive && $event->regCount() == 0)
<div class="col-xs-1">
    {!! Form::open(['url' => env('APP_URL').'/event/' . $event->eventID, 'method' => 'DELETE']) !!}
    <button class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top"
            onclick="return confirm('{{ trans('messages.tooltips.sure') }}');"
            title="{{ trans('messages.buttons.delete') }}"> {!! trans('messages.symbols.trash') !!}</button>
    <input id="myDelete" type="submit" value="Go" class="hidden" />
    {!! Form::close() !!}
</div>
@endif

@if($event->hasTracks > 0 && $event->checkin_time())
    <div class="col-xs-1">
        <a href='{{ $recordURL }}' class='btn btn-pink btn-sm' data-toggle='tooltip' data-placement='top'
           title='{{ trans('messages.buttons.rec_att') }}'><i class='far fa-fw fa-check-square'></i></a>
    </div>
@endif

@if($event->checkin_time())
    <div class="col-xs-1">
        <a href='{{ $checkinURL }}' class='btn btn-pink btn-sm' data-toggle='tooltip' data-placement='top'
           title='{{ trans('messages.buttons.chk_vol') }}'><i class='far fa-fw fa-clipboard'></i></a>
    </div>
@endif
