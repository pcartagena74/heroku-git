@php
/**
 * Comment: Page to list campaigns
 * Created: 9/10/2017
 */

use \Illuminate\Support\Facades\DB;

$topBits = '';  // remove this if this was set in the controller
//$topBits = array([3, 'label', 123, 'ctxt', 'rtxt', 0]);
@endphp
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @include('v1.parts.start_content', ['header' => 'Campaigns', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
<a class="btn btn-primary btn-lg" href="{{ env('APP_URL') }}/campaign/create" style="float:right;">
    Create Campaign
</a>
@foreach($campaigns as $c)
@php
        if($c->sendDate === null) {
            $status = "DRAFT";
        } else {
            $status = "SENT";
        }
        $opens = DB::table('sent_emails')->where('campaignID', $c->campaignID)->sum('opens');
        $clicks = DB::table('sent_emails')->where('campaignID', $c->campaignID)->sum('clicks');
@endphp
        {{-- @if($c == $campaigns->first()) --}}
<div class="col-md-10 col-xs-10 campaign-panel-{{$c->campaignID}}">
    <div class="x_panel">
        <div class="x_title">
            <h2>
                <a href="{!! url('campaign',[$c->campaignID,'edit']) !!}">
                    <img class="img-thumbnail" height="100px" src="{{getEmailTemplateThumbnailURL($c)}}" width="70px">
                        {!! $c->title !!}
                    </img>
                </a>
                <small>
                    {!! $status !!}
                                @if($c->sendDate)
                                    on {!! $c->sendDate->format('M j, Y') !!}
                                @else
                                    created on {!! $c->createDate->format('n/j/Y') !!}
                                @endif
                </small>
            </h2>
            <ul class="nav navbar-right panel_toolbox">
                <li>
                    <a class="collapse-link">
                        <i class="fa fa-chevron-down">
                        </i>
                    </a>
                </li>
                <li class="dropdown">
                    <a aria-expanded="false" class="dropdown-toggle" data-toggle="dropdown" href="#" role="button">
                        <i class="fa fa-wrench">
                        </i>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <a href="{!! url('campaign',[$c->campaignID,'copy']) !!}">
                                Copy
                            </a>
                        </li>
                        <li>
                            <a href="{!! url('campaign',[$c->campaignID,'edit']) !!}">
                                Edit
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="javascript:void(0)" onclick="deleteCampaign('{{$c->title}}','{{$c->campaignID}}')">
                        <i class="fa fa-close">
                        </i>
                    </a>
                </li>
            </ul>
            <div class="clearfix">
            </div>
        </div>
        <div class="x_content campaign-panel-{{$c->campaignID}}">
            @if($c->sendDate)
            @php
            $open = 0;
            $click = 0;
            $emails_count = 0;
            $width_per_open = 0;
            $width_per_click = 0;
            if(!empty($c->mailgun->total_sent)){
                $emails_count = $c->mailgun->total_sent;
            }
            if(!empty($c->mailgun->open)){
                $open = $c->mailgun->open;
                if($emails_count > 0){
                    $width_per_open = ($open / $emails_count) * 100;
                }
            }
            if(!empty($c->mailgun->click)){
                $click = $c->mailgun->click;
                if($emails_count > 0){
                    $width_per_click = ($click / $emails_count) * 100;
                }
            }
            @endphp
            <div class="row tile_count">
                <div class="col-sm-2 tile_stats_count" style="text-align: center;">
                    <div class="count green tiles-stats">
                        {{ $open }}
                    </div>
                    <small>
                        OPENS
                    </small>
                </div>
                <div class="col-sm-3">
                    <br/>
                    <small>
                        OPEN RATE
                    </small>
                    <div class="progress progress_sm">
                        <div aria-valuemax="{{ $emails_count }}" aria-valuemin="0" aria-valuenow="{{ $open }}" class="progress-bar bg-green" role="progressbar" style="width:{{$width_per_open}}%">
                        </div>
                    </div>
                    {{--
                    <div class="progress progress_sm">
                        <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{ $c->emails_count }}" class="progress-bar bg-blue" role="progressbar">
                        </div>
                    </div>
                    <small>
                        AVERAGE OPENS PER EMAIL
                    </small>
                    --}}
                </div>
                <div class="col-sm-2 tile_stats_count" style="text-align: center;">
                    <div class="count green tiles-stats">
                        {{ $click}}
                    </div>
                    <small>
                        CLICKS
                    </small>
                </div>
                <div class="col-sm-3">
                    <br/>
                    <small>
                        CLICK RATE
                    </small>
                    <div class="progress progress_sm">
                        <div aria-valuemax="{{ $emails_count }}" aria-valuemin="0" aria-valuenow="{{ $click }}" class="progress-bar bg-green" role="progressbar" style="width:{{$width_per_click}}%">
                        </div>
                    </div>
                    {{--
                    <div class="progress progress_sm">
                        <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{ $c->emails_count }}" class="progress-bar bg-blue" role="progressbar">
                        </div>
                    </div>
                    <small>
                        AVERAGE CLICKS PER URL
                    </small>
                    --}}
                </div>
                <div class="col-sm-2 tile_stats_count" style="text-align: center;">
                    <div class="count green tiles-stats">
                        {!! $emails_count !!}
                    </div>
                    <small>
                        EMAILS SENT
                    </small>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
<!-- x_content -->
<!-- x_panel -->
{{-- @else
<div class="col-md-10 col-xs-10">
    <div class="x_panel collapsed">
        <div class="x_title">
            <h2>
                {!! $c->title !!}
                <small>
                    {!! $status !!}
                                @if($c->sendDate)
                                    on {!! $c->sendDate->format('M j, Y') !!}
                                @else
                                    created on {!! $c->createDate->format('n/j/Y') !!}
                                @endif
                </small>
            </h2>
            <ul class="nav navbar-right panel_toolbox">
                <li>
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up">
                        </i>
                    </a>
                </li>
                <li class="dropdown">
                    <a aria-expanded="false" class="dropdown-toggle" data-toggle="dropdown" href="#" role="button">
                        <i class="fa fa-wrench">
                        </i>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <a href="#">
                                Copy
                            </a>
                        </li>
                        @if($c->sendDate)
                        <li>
                            <a href="#">
                                Delete
                            </a>
                        </li>
                        @else
                        <li>
                            <a href="#">
                                Edit
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                <li>
                    <a class="close-link">
                        <i class="fa fa-close">
                        </i>
                    </a>
                </li>
            </ul>
            <div class="clearfix">
            </div>
        </div>
        <div class="x_content">
            @if($c->sendDate)
            <div class="row tile_count">
                <div class="col-sm-2 tile_stats_count" style="text-align: center;">
                    <div class="count green tiles-stats">
                        {{ $opens }}
                    </div>
                    <small>
                        OPENS
                    </small>
                </div>
                <div class="col-sm-3">
                    <small>
                        OPEN RATE
                    </small>
                    <div class="progress progress_sm">
                        <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{ $c->emails_count }}" class="progress-bar bg-green" role="progressbar">
                        </div>
                    </div>
                    <div class="progress progress_sm">
                        <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{ $c->emails_count }}" class="progress-bar bg-blue" role="progressbar">
                        </div>
                    </div>
                    <small>
                        AVERAGE USER
                    </small>
                </div>
                <div class="col-sm-2 tile_stats_count" style="text-align: center;">
                    <div class="count green tiles-stats">
                        {!! $clicks !!}
                    </div>
                    <small>
                        CLICKS
                    </small>
                </div>
                <div class="col-sm-3">
                    <small>
                        CLICK RATE
                    </small>
                    <div class="progress progress_sm">
                        <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{ $c->emails_count }}" class="progress-bar bg-green" role="progressbar">
                        </div>
                    </div>
                    <div class="progress progress_sm">
                        <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{ $c->emails_count }}" class="progress-bar bg-blue" role="progressbar">
                        </div>
                    </div>
                    <small>
                        AVERAGE USER
                    </small>
                </div>
                <div class="col-sm-2 tile_stats_count" style="text-align: center;">
                    <div class="count green tiles-stats">
                        {!! $c->emails_count !!}
                    </div>
                    <small>
                        EMAILS SENT
                    </small>
                </div>
            </div>
            @endif
        </div>
        <!-- x_content -->
    </div>
    <!-- x_panel -->
</div>
@endif --}}

    @endforeach

    @include('v1.parts.end_content')

@endsection

@section('scripts')
<script>
    $('.collapsed').css('height', 'auto');
        $('.collapsed').find('.x_content').css('display', 'none');
    var delete_campaign_id = '';
    function deleteCampaign(title,id){
        delete_campaign_id = id;
        $('#popup-confirm-campaign-delete').find('.modal-body').html('{{trans('messages.campaign_delete_popup.body')}} '+ title + ' ?')
        $('#popup-confirm-campaign-delete').modal('show');
    }

    function setExitPopButtonValue(btn_press){
        switch(btn_press) {
          case 'yes':
            $('#popup-confirm-campaign-delete').modal('hide');
            $.ajax({
                url: '{{ url('deleteCampaign') }}',
                type: 'POST',
                dataType: 'json',
                data: {
                    campaign:delete_campaign_id
                },
                success: function(data) {
                    if(data.success == false){
                        $('#popup-campaign-delete-success').find('.modal-title').html("{{ trans('messages.campaign_delete_popup_success.title_error') }}");
                        $('#popup-campaign-delete-success').find('.modal-body').html(data.message)
                        $('#popup-campaign-delete-success').modal('show');
                    } else {
                        $('.campaign-panel-'+delete_campaign_id).hide();
                        $('#popup-campaign-delete-success').find('.modal-title').html("{{ trans('messages.campaign_delete_popup_success.title') }}");
                        $('#popup-campaign-delete-success').find('.modal-body').html(data.message)
                        $('#popup-campaign-delete-success').modal('show');
                    }
                    delete_campaign_id = '';
                },
                error: function(error) {
                    console.log(error)
                }
            });
            break;
        }
    }
</script>
@endsection

@section('modals')
@include('v1.modals.context_sensitive_issue')
<div aria-hidden="true" aria-labelledby="popup-confirm-campaign-delete" class="modal fade" id="popup-confirm-campaign-delete" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        ×
                    </span>
                </button>
                <h4 class="modal-title">
                    {{ trans('messages.campaign_delete_popup.title') }}
                </h4>
            </div>
            <div class="modal-body">
                <p>
                    {{ trans('messages.campaign_delete_popup.body') }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-warning" onclick="setExitPopButtonValue('yes')" type="button">
                    {{ trans('messages.campaign_delete_popup.btn_ok') }}
                </button>
                <button class="btn btn-success" data-dismiss="modal" type="button">
                    {{ trans('messages.campaign_delete_popup.btn_cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
<div aria-hidden="true" aria-labelledby="popup-campaign-delete-success" class="modal fade" id="popup-campaign-delete-success" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        ×
                    </span>
                </button>
                <h4 class="modal-title">
                    {{ trans('messages.campaign_delete_popup_success.title') }}
                </h4>
            </div>
            <div class="modal-body">
                <p>
                    {{ trans('messages.campaign_delete_popup_success.body') }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-warning" data-dismiss="modal" type="button">
                    {{ trans('messages.campaign_delete_popup_success.btn_ok') }}
                </button>
            </div>
        </div>
    </div>
</div>
{{--
<div aria-hidden="true" aria-labelledby="campaign_label" class="modal fade" id="campaign_modal" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-label="Close" class="close" data-dismiss="modal" style="float:right;" type="button">
                    <span aria-hidden="true">
                        ×
                    </span>
                </button>
                <h4 class="modal-title" id="campaign_label">
                    Campaign Editor
                </h4>
            </div>
            <div class="modal-body">
                {!! Form::open(array('url' =>env('APP_URL')."/campaign", 'method' => 'get')) !!}
                <div class="col-md-3 col-sm-3 col-xs-12">
                    <button class="btn btn-sm btn-warning" id="add_row" type="button">
                        Add Another
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal" type="button">
                    Close
                </button>
                <button class="btn btn-sm btn-success" id="addr_submit" type="submit">
                    Save Address
                </button>
            </div>
        </div>
    </div>
</div>
--}}
@endsection
