@php 
/**
 * Comment: Display the form to store or update a new campaign
 * Created: 9/18/2017
 *
 * @var $current_datetime
 * @var $campaign
 *
 */

if(!isset($campaign)) {
    $campaign = '';
}

$topBits = '';  // remove this if this was set in the controller

$disable = '';
$date_picker_schedule = '';
if(!empty($campaign)){
    if(!empty($campaign->scheduleDate) && $campaign->scheduleDate <= $current_datetime){
        $disable = 'disabled';
        $date_picker_schedule = convertToDatePickerFormat($campaign->scheduleDate);
    }
    if(empty($campaign->scheduleDate) && !empty($campaign->sendDate)){
        $disable = 'disabled';
    }
}

@endphp

@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.campaign_heading'),
             'subheader' => '', 'w1' => '9', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
<div aria-multiselectable="true" class="accordion" id="accordion" role="tablist">
    @if($campaign == null)
        {!! Form::open(array('url' => url('campaign'), 'method' => 'post' ,'id'=>'save_campaign','onsubmit'=>'return false;')) !!}
    @else
        {!! Form::model($campaign, array('url' => env('APP_URL')."/campaign", 'method' => 'post','id'=>'edit_campaign','onsubmit'=>'return false;')) !!}
    @endif
    <div class="panel">
        <a aria-controls="collapseOne" aria-expanded="true" class="panel-heading" data-parent="#accordion" data-toggle="collapse" href="#collapseOne" id="headingOne" role="tab">
            <i class="panel-title">
                {{ trans('messages.fields.camp_message_header') }}
            </i>
        </a>
        @if(!empty($disable))
        <div aria-labelledby="headingOne" class="panel-collapse collapse" id="collapseOne" role="tabpanel">
            @else
            <div aria-labelledby="headingOne" class="panel-collapse collapse in" id="collapseOne" role="tabpanel">
                @endif
                <div class="panel-body">
                    @if(!empty($campaign))
                    <div class="form-group col-sm-12 col-xs-12">
                        {!! Form::text('name', $campaign->title, array('class' => 'form-control input-sm', 'placeholder' => 'Campaign title','id'=>'campaign_title',$disable)) !!}
                    </div>
                    @endif
                    <div class="form-group col-sm-6 col-xs-12">
                        @if($campaign == null)
                            {!! Form::text('from_name', $org->orgName, array('class' => 'form-control input-sm', 'placeholder' => 'From Name','id'=>'from_name')) !!}
                        @else
                            {!! Form::text('from_name', $campaign->fromName, array('class' => 'form-control input-sm', 'placeholder' => 'From Name','id'=>'from_name',$disable)) !!}
                        @endif
                    </div>
                    <div class="form-group col-sm-6 col-xs-12">
                        @if($campaign == null)
                            {!! Form::text('from_email', $org->adminEmail, array('class' => 'form-control input-sm', 'placeholder' => 'From Email','id'=>'from_email',$disable)) !!}
                        @else
                            {!! Form::text('from_email', $campaign->fromEmail, array('class' => 'form-control input-sm', 'placeholder' => 'From Email','id'=>'from_email',$disable)) !!}
                        @endif
                    </div>
                    <div class="form-group col-sm-12 col-xs-12">
                        @if($campaign == null)
                            {!! Form::text('subject', '', array('class' => 'form-control input-sm', 'placeholder' => 'Subject Line','id'=>'subject')) !!}
                        @else
                            {!! Form::text('subject', $campaign->subject, array('class' => 'form-control input-sm', 'placeholder' => 'Subject Line','id'=>'subject',$disable)) !!}
                        @endif
                    </div>
                    <div class="form-group col-sm-12 col-xs-12">
                        @if($campaign == null)
                            {!! Form::text('preheader', '', array('class' => 'form-control input-sm', 'placeholder' => 'Preheader Line','id'=>'preheader')) !!}
                        @else
                            {!! Form::text('preheader', $campaign->preheader, array('class' => 'form-control input-sm', 'placeholder' => 'Preheader Line','id'=>'preheader',$disable)) !!}
                        @endif
                    </div>
                    <div class="form-group col-sm-12 col-xs-12 clear-fix">
                        {{ trans('messages.fields.camp_email_list') }}
                     @if($campaign == null)
                        {!! Form::select('email_list', $list_dp, null, array('class' => 'form-control input-sm','id'=>'email_list')) !!}
                    @else
                        {!! Form::select('email_list', $list_dp, $campaign->emailListID, array('class' => 'form-control input-sm','id'=>'email_list',$disable)) !!}
                    @endif
                    </div>
                    <div class="form-group col-sm-12 col-xs-12 clear-fix" id="send_campaign_response">
                    </div>
                </div>
            </div>
        </div>
        {{--
        <div class="panel">
            <a aria-controls="collapseThree" aria-expanded="false" class="panel-heading" data-parent="#accordion" data-toggle="collapse" href="#collapseThree" id="headingThree" role="tab">
                <i class="panel-title">
                    {{ trans('messages.fields.camp_email_list') }}
                </i>
            </a>
            <div aria-labelledby="headingThree" class="panel-collapse collapse" id="collapseThree" role="tabpanel">
                <div class="panel-body">
                </div>
            </div>
        </div>
        --}}
        <div class="panel">
            <a aria-controls="collapseTwo" class="panel-heading" data-parent="#accordion" data-toggle="collapse" href="#collapseTwo" role="tab">
                @if(empty($campaign))
                <i class="panel-title" id="show-etb">
                    {{ trans('messages.fields.camp_create_email_template') }}
                </i>
                @else
                <i class="panel-title">
                    {{ trans('messages.fields.camp_edit_email_template') }}
                </i>
                @endif
            </a>
            <div aria-labelledby="collapseTwo" class="panel-collapse collapse" id="collapseTwo" role="tabpanel">
                <div class="panel-body">
                    @if(!empty($campaign))
                    <a href="javascript:void(0)" id="show-etb">
                        <img class="img-thumbnail" height="100px" src="{{getEmailTemplateThumbnailURL($campaign)}}" width="70px">
                            {!! $campaign->title !!}
                        </img>
                    </a>
                    @endif
                    @if(empty($disable))
                    <div class="etb-wrapper">
                        <i class="fa fa-close etb-wrapper__close-btn" id="hide-etb">
                        </i>
                        <div class="etb-wrapper__inner">
                            @include('v1.auth_pages.campaigns.email_builder')
                            <div class="etb-wrapper__inner-footer">
                                <img alt="mcentric logo" class="etb-wrapper__inner-footer-logo" src="/images/mCentric_logo_blue.png"/>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @if(!empty($campaign->sendDate))
        <div class="panel">
            <a aria-controls="headingStatics" aria-expanded="true" class="panel-heading" data-parent="#accordion" data-toggle="collapse" href="#headingStatics" role="tab">
                <i class="panel-title">
                    {{ trans('messages.headers.campaign_statics') }}
                </i>
            </a>
            <div aria-labelledby="headingStatics" class="panel-collapse collapse in" id="headingStatics" role="tabpanel">
                <div class="panel-body">
                    <div class=" col-sm-12 col-xs-12">
                        @php
                    $statics = $campaign->mailgun;
                    // $total = $statics->total;
                    $open_rate = round((($statics->open / $statics->total_sent)*100),2) . '%';
                    $delivered = $statics->delivered;
                    $click = $statics->click;
                    $total = $statics->sent;
                    $open = $statics->open;
                    $temp_failed = $statics->temporary_failure;
                    $perm_failed = $statics->permanent_fail;
                    $report_spam = $statics->spam;
                    $unsubscribed = $statics->unsubscribe;
                    $did_not_open = $delivered - $open;
                    $mobile_percent = round((($statics->mobile_count / $statics->total_sent)*100),2) . '%';
                    $desktop_percent = round((($statics->desktop_count / $statics->total_sent)*100),2) . '%';
                    @endphp
                        <table class="table">
                            <tr>
                                <td>
                                    @lang('messages.fields.camp_total')
                                </td>
                                <td>
                                    {{$total}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    @lang('messages.fields.camp_open_rate')
                                </td>
                                <td>
                                    <i aria-hidden="true" class="fa fa-mobile">
                                    </i>
                                    {{$mobile_percent}}
                                    <i aria-hidden="true" class="fa fa-desktop">
                                    </i>
                                    {{$desktop_percent}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    @lang('messages.fields.camp_open')
                                </td>
                                <td>
                                    {{$open}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    @lang('messages.fields.camp_delivered')
                                </td>
                                <td>
                                    {{$delivered}}
                                </td>
                            </tr>
                            <tr class="hidden">
                                <td>
                                    @lang('messages.fields.camp_click')
                                </td>
                                <td>
                                    {{$click}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    @lang('messages.fields.camp_not_open')
                                </td>
                                <td>
                                    {{$did_not_open}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    @lang('messages.fields.camp_unsubs')
                                </td>
                                <td>
                                    {{$unsubscribed}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    @lang('messages.fields.camp_spam')
                                </td>
                                <td>
                                    {{$report_spam}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    @lang('messages.fields.camp_temp_fail')
                                </td>
                                <td>
                                    {{$temp_failed}}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    @lang('messages.fields.camp_perm_fail')
                                </td>
                                <td>
                                    {{$perm_failed}}
                                </td>
                            </tr>
                        </table>
                        @if(!empty($campaign->campaign_links) && count($campaign->campaign_links) > 0)
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        @lang('messages.fields.camp_url')
                                    </th>
                                    <th>
                                        @lang('messages.fields.camp_unique_click')
                                    </th>
                                    <th>
                                        @lang('messages.fields.camp_total_click')
                                    </th>
                                    <th>
                                        @lang('messages.fields.camp_first_click')
                                    </th>
                                    <th>
                                        @lang('messages.fields.camp_last_click')
                                    </th>
                                    <th>
                                        @lang('messages.fields.camp_url_click_summary')
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($campaign->campaign_links as $row)
                                <tr>
                                    <td>
                                        {{$row->url}}
                                    </td>
                                    <td>
                                        {{$row->unique_clicks}}
                                    </td>
                                    <td>
                                        {{$row->total_clicks}}
                                    </td>
                                    <td>
                                        @php
                                    if(empty($row->first_click)) {
                                        echo '--';
                                    } else {
                                      echo convertToDatePickerFormat($row->first_click);  
                                    }
                                    @endphp
                                    </td>
                                    <td>
                                        @php
                                    if(empty($row->last_click)) {
                                        echo '--';
                                    } else {
                                      echo convertToDatePickerFormat($row->last_click);  
                                    }
                                    @endphp
                                    </td>
                                    <td>
                                        <a href="javascript:void(0)" onclick="getUrlClickEmails({{$row->id}})">
                                            Show
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <table class="table" id="url_email_list" style="display: none;">
                            <thead>
                            </thead>
                            <tbody style="overflow-y: auto; height: 200px">
                            </tbody>
                        </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    {!! Form::close() !!}
    </div>
    @include('v1.parts.end_content')
    {{-- Test Email Div --}}
    <div>
        @include('v1.parts.start_content', ['header' => trans('messages.headers.campaign_test_email'),
                 'subheader' => '', 'w1' => '3', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
        <div id="test-email">
            <div class="form-group">
                {!! Form::label('email1', trans('messages.fields.camp_five_email') ) !!}
            {!! Form::email('email1', $org->adminEmail, array('class' => 'form-control input-sm')) !!}
            @if($campaign == null)
            @else
            @endif
            </div>
            <div class="form-group">
                {!! Form::email('email2', '', array('class' => 'form-control input-sm', 'style' => 'display:none;',
                'id' => 'email2')) !!}
            </div>
            <div class="form-group">
                {!! Form::email('email3', '', array('class' => 'form-control input-sm', 'style' => 'display:none;',
                'id' => 'email3')) !!}
            </div>
            <div class="form-group">
                {!! Form::email('email4', '', array('class' => 'form-control input-sm', 'style' => 'display:none;',
            'id' => 'email4')) !!}
            </div>
            <div class="form-group">
                {!! Form::email('email5', '', array('class' => 'form-control input-sm', 'style' => 'display:none;',
            'id' => 'email5')) !!}
            </div>
            <a id="add_email" onclick="add_email();">
                {{trans('messages.fields.camp_add_another')}}
            </a>
            <p>
            </p>
            <div class="form-group">
                {!! Form::label('note', trans('messages.fields.camp_lbl_personal_note')) !!}
            {!! Form::textarea('note', '', array('class' => 'form-control', 'rows' => '4','id'=>'text_note',
                'placeholder' => 'Enter a note that will appear at the top of the test message.')) !!}
            </div>
            <div class="form-group">
                {!! Form::button(trans('messages.fields.camp_btn_send_test_message'), array('class' => 'btn btn-primary btn-sm', 'name' => 'clicked','onclick'=>'sendTestEmail(this)',$disable)) !!}
            </div>
            <div class="form-group" id="response">
            </div>
            @include('v1.parts.end_content')
        </div>
        @include('v1.parts.start_content', ['header' => trans('messages.headers.campaign_scheduling'),
             'subheader' => '', 'w1' => '3', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0,'class'=>'
             pull-right'])
        <div class="form-group">
            <div class="col-sm-3">
                {!! Form::label('send', trans('messages.fields.camp_lbl_send_now'), array('class' => 'control-label')) !!}
            </div>
            @if(!empty($campaign->scheduleDate))
            <div class="col-sm-5" style="text-align: center;">
                {!! Form::checkbox('send', '1', true, array('class' => 'js-switch','id'=>'send_later',$disable)) !!}
            </div>
            @else
            <div class="col-sm-5" style="text-align: center;">
                {!! Form::checkbox('send', '1', false, array('class' => 'js-switch','id'=>'send_later',$disable)) !!}
            </div>
            @endif
            <div class="col-sm-3">
                {!! Form::label('send',trans('messages.fields.camp_lbl_send_later'), array('class' => 'control-label')) !!}
            </div>
            @if(!empty($campaign))
    @if(!empty($campaign->sendDate) && empty($campaign->scheduleDate))
            <div class="row">
                <div class="col-sm-12">
                    {{trans('messages.messages.camp_sent_on',['date'=>$campaign->sendDate])}}
                </div>
            </div>
            @endif
    @if(!empty($campaign->sendDate) && !empty($campaign->scheduleDate) && $campaign->scheduleDate <= $current_datetime)
            <div class="row">
                <div class="col-sm-12">
                    {{trans('messages.messages.camp_sent_on',['date'=>$campaign->scheduleDate])}}
                </div>
            </div>
            @endif
    @if(!empty($campaign->sendDate) && !empty($campaign->scheduleDate) && $campaign->scheduleDate > $current_datetime)
            <div class="row">
                <div class="col-sm-12">
                    {{trans('messages.messages.camp_scheduled_sent_on',['date'=>$campaign->scheduleDate])}}
                </div>
            </div>
            @endif
    @endif
        </div>
        <p>
        </p>
        <div id="schedule" style="display: none;">
            {!! Form::label('schedule', trans('messages.fields.camp_lbl_release_date')) !!}
    @if($campaign == null)
            <div class="form-group col-sm-12">
                {!! Form::text('schedule', '', array('class' => 'form-control input-sm has-feedback-left','id'=>'schedule_date')) !!}
                <span aria-hidden="true" class="fa fa-calendar form-control-feedback left">
                </span>
            </div>
            @else
            <div class="form-group col-sm-12">
                {!! Form::text('schedule', '', array('class' => 'form-control has-feedback-left','id'=>'schedule_date',$disable)) !!}
                <span aria-hidden="true" class="fa fa-calendar form-control-feedback left">
                </span>
            </div>
            @endif
        </div>
        <div class="form-group">
            {!! Form::button(trans('messages.fields.camp_btn_send_now'), array('class' => 'btn btn-success btn-sm', 'name' => 'clicked', 'id'=>'send_now', 'onclick' => 'sendCampaign()',$disable)) !!}
        </div>
        @include('v1.parts.end_content')
    </div>
    @endsection

@section('scripts')
    {{-- @include('v1.parts.footer-tinymce2') --}}
    {{-- @include('v1.parts.footer-daterangepicker', ['fieldname' => 'schedule_date', 'time' => 'true', 'single' => 'true']) --}}
    <script type="text/javascript">
        $('#schedule_date').daterangepicker({
            timePicker: true,
            autoUpdateInput: true,
            singleDatePicker: true,
            showDropdowns: true,
            timePickerIncrement: 15,
            startDate: moment(new Date()),
            minDate: moment(new Date()),
            locale: {
                format: 'M/D/Y h:mm A'
            },
        });

    @if(!empty($campaign->scheduleDate))
        $('#schedule_date').val('{{$date_picker_schedule}}');
    @endif
    var x = 2;
    function add_email() {
        $('#email' + x).show();
        x += 1;
        if (x > 5) {
            $('#add_email').hide();
        }
    }

    function sendLater(){
        if($('#send_later').is(':checked')){
            $("#schedule").show();
            $("#send_now").text("{{trans('messages.fields.camp_btn_schedule')}}");
        } else {
            $("#schedule").hide();
            $("#send_now").text("{{trans('messages.fields.camp_btn_send_now')}}");
        }
    }

    sendLater();
    function sendTestEmail(ths){
        var html = _emailBuilder.getContentHtml();
        var email1 = $('#email1').val();
        var email2 = $('#email2').val();
        var email3 = $('#email3').val();
        var email4 = $('#email4').val();
        var email5 = $('#email5').val();
        var note = $('#text_note').val();
        var campaign = '';
        @if($campaign != null)
            campaign = {{$campaign->campaignID}}
        @endif
        $('#response').html('');
        $(ths).attr("disabled", true);
        $.ajax({
            url: '{{url('send-test-email')}}',
            type: 'post',
            dataType: 'json',
            data: {note:note,email1:email1,email2:email2,email3:email3,email4:email4,email5:email5,html:html,campaign:campaign},
            success: function(data) {
                $(ths).removeAttr('disabled');
                if (data.success == true) {
                    var msg = '<div class="alert alert-success"><a aria-label="close" class="close" data-dismiss="alert" href="#">×</a>'
                            + data.message +
                            '</div>';
                    $('#response').html(msg);
                } else {
                    var msg = '<div class="alert alert-danger"><a aria-label="close" class="close" data-dismiss="alert" href="#">×</a>'
                            + data.message +
                            '</div>';
                    $('#response').html(msg);
                }
            }, 
        });
    }
    function getUrlClickEmails($url_id){
        @if($campaign != null)
            campaign = {{$campaign->campaignID}}
        @endif
        $.ajax({
            url: '{{ url('campaign/url-clicked-email-list') }}',
            type: 'post',
            dataType: 'json',       
            data: {campaign:campaign,url_id:$url_id},
            success: function(data) {
                 if (data.success == true) {
                    $str = '<tr><th>{{trans('messages.fields.camp_url_summary')}} '+ data.url+'</th></tr>';
                    $('#url_email_list thead').html($str);
                    $str = '';
                    $.each(data.email_list,function(index,value){
                        $str += '<tr><td>'+ value['email_id'] +'</td></tr>';
                    });

                    $('#url_email_list tbody').html($str);
                    $('#url_email_list').show();
                } else {
                    console.log('heere',data.error);
                }
            }, 
        });
    }
    function sendCampaign() {
        $( "#collapseOne" ).collapse("show");
        var campaign = '';
        @if(!empty($campaign->campaignID))
        campaign = '{{$campaign->campaignID}}'
        @endif
        var schedule = '';
        if($('#send_later').is(':checked')){
            schedule = $('#schedule_date').val();
        }
        var campaign_title = $('#campaign_title').val();
        var from_name = $('#from_name').val();
        var from_email = $('#from_email').val();
        var subject = $('#subject').val();
        var preheader = $('#preheader').val();
        var email_list = $('#email_list').val();
        $.ajax({
            url: '{{ url('sendCampaign') }}',
            type: 'post',
            dataType: 'json',       
            data: {campaign:campaign,schedule:schedule,campaign_title:campaign_title,from_name:from_name,from_email:from_email,subject:subject,preheader:preheader,email_list:email_list},
            success: function(data) {
                if (data.success == true) {
                    window.location.href = data.redirect;
                } else {
                    var msg = '';
                    $.each(data.validation_error,function(index,value){
                        msg += '<div class="alert alert-danger"><a aria-label="close" class="close" data-dismiss="alert" href="#">×</a>'
                            + value[0] +
                            '</div>'; 
                    })
                    $('#send_campaign_response').html(msg);
                    // window.href = data.success.redirect;
                }
            }, 
        });
    }
    $(document).ready(function () {
        // $('#schedule').val(moment(new Date($('#schedule').val())).format("MM/DD/YYYY HH:mm A"));
    });
    $(document).ready(function () {
        var setContentHeight = function () {
            // reset height
            $RIGHT_COL.css('min-height', $(window).height());

            var bodyHeight = $BODY.outerHeight(),
                footerHeight = $BODY.hasClass('footer_fixed') ? -10 : $FOOTER.height(),
                leftColHeight = $LEFT_COL.eq(1).height() + $SIDEBAR_FOOTER.height(),
                contentHeight = bodyHeight < leftColHeight ? leftColHeight : bodyHeight;

            // normalize content
            contentHeight -= $NAV_MENU.height() + footerHeight;

            $RIGHT_COL.css('min-height', contentHeight);
        };

        $SIDEBAR_MENU.find('a[href="{{ env('APP_URL') }}/campaigns"]').parent('li').addClass('current-page').parents('ul').slideDown(function () {
            setContentHeight();
        }).parent().addClass('active');

        @if($campaign !== null)
        $("#add").text('Edit Event');
        @endif

        $('#show-etb').on('click', function() {
          $('.etb-wrapper').addClass('is-active')
          $('body').addClass('no-scroll');
        });
            
        $('#send_later').on('change', function () {
            sendLater();
        });
    
});//ready end
    </script>
    @endsection
</div>