@php 
/**
 * Comment: Display the form to store or update a new campaign
 * Created: 9/18/2017
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

    @include('v1.parts.start_content', ['header' => ' Campaign Message',
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
        <div aria-labelledby="headingOne" class="panel-collapse collapse in" id="collapseOne" role="tabpanel">
            <div class="panel-body">
                @if(!empty($campaign))
                <div class="form-group col-sm-12 col-xs-12">
                    {!! Form::text('name', $campaign->title, array('class' => 'form-control input-sm', 'placeholder' => 'Campaign title','id'=>'campaign_title')) !!}
                </div>
                @endif
                <div class="form-group col-sm-6 col-xs-12">
                    @if($campaign == null)
                            {!! Form::text('from_name', $org->orgName, array('class' => 'form-control input-sm', 'placeholder' => 'From Name','id'=>'from_name')) !!}
                        @else
                            {!! Form::text('from_name', $campaign->fromName, array('class' => 'form-control input-sm', 'placeholder' => 'From Name','id'=>'from_name')) !!}
                        @endif
                </div>
                <div class="form-group col-sm-6 col-xs-12">
                    @if($campaign == null)
                            {!! Form::text('from_email', $org->adminEmail, array('class' => 'form-control input-sm', 'placeholder' => 'From Email','id'=>'from_email')) !!}
                        @else
                            {!! Form::text('from_email', $campaign->fromEmail, array('class' => 'form-control input-sm', 'placeholder' => 'From Email','id'=>'from_email')) !!}
                        @endif
                </div>
                <div class="form-group col-sm-12 col-xs-12">
                    @if($campaign == null)
                            {!! Form::text('subject', '', array('class' => 'form-control input-sm', 'placeholder' => 'Subject Line','id'=>'subject')) !!}
                        @else
                            {!! Form::text('subject', $campaign->subject, array('class' => 'form-control input-sm', 'placeholder' => 'Subject Line','id'=>'subject')) !!}
                        @endif
                </div>
                <div class="form-group col-sm-12 col-xs-12">
                    @if($campaign == null)
                            {!! Form::text('preheader', '', array('class' => 'form-control input-sm', 'placeholder' => 'Preheader Line','id'=>'preheader')) !!}
                        @else
                            {!! Form::text('preheader', $campaign->preheader, array('class' => 'form-control input-sm', 'placeholder' => 'Preheader Line','id'=>'preheader')) !!}
                        @endif
                </div>
                <div class="form-group col-sm-12 col-xs-12 clear-fix">
                    {{ trans('messages.fields.camp_email_list') }}
                     @if($campaign == null)
                        {!! Form::select('email_list', $list_dp, null, array('class' => 'form-control input-sm','id'=>'email_list')) !!}
                    @else
                        {!! Form::select('email_list', $list_dp, $campaign->emailListID, array('class' => 'form-control input-sm','id'=>'email_list')) !!}
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
        <a class="panel-heading" href="javascript:void(0)" id="headingFour">
            <i class="panel-title" id="show-etb">
                @if(empty($campaign))
                    {{ trans('messages.fields.camp_create_email_template') }}
                @else
                <img class="img-thumbnail" height="100px" src="{{getEmailTemplateThumbnailURL($campaign)}}" width="70px">
                    {!! $campaign->title !!} {{ trans('messages.fields.camp_edit_email_template') }}
                </img>
                @endif
            </i>
        </a>
        @if(empty($disable))
        <div class="panel-collapse">
            <div class="panel-body">
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
            </div>
        </div>
        @endif
    </div>
    {!! Form::close() !!}
</div>
@include('v1.parts.end_content')
    {{-- Test Email Div --}}
<div>
    @include('v1.parts.start_content', ['header' => 'Test Emails',
                 'subheader' => '', 'w1' => '3', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <div id="test-email">
        <div class="form-group">
            {!! Form::label('email1', 'Enter up to 5 email addresses:') !!}
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
            Add Another
        </a>
        <p>
        </p>
        <div class="form-group">
            {!! Form::label('note', 'Personal Note') !!}
            {!! Form::textarea('note', '', array('class' => 'form-control', 'rows' => '4','id'=>'text_note',
                'placeholder' => 'Enter a note that will appear at the top of the test message.')) !!}
        </div>
        <div class="form-group">
            {!! Form::button('Send Test Message', array('class' => 'btn btn-primary btn-sm', 'name' => 'clicked','onclick'=>'sendTestEmail(this)')) !!}
        </div>
        <div class="form-group" id="response">
        </div>
        @include('v1.parts.end_content')
    </div>
</div>
@include('v1.parts.start_content', ['header' => 'Campaign Scheduling',
             'subheader' => '', 'w1' => '3', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
<div class="form-group">
    <div class="col-sm-3">
        {!! Form::label('send', 'Send Now', array('class' => 'control-label')) !!}
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
        {!! Form::label('send', 'Send Later', array('class' => 'control-label')) !!}
    </div>
    @if(!empty($campaign))
    @if(!empty($campaign->sendDate) && empty($campaign->scheduleDate))
    <div class="row">
        <div class="col-sm-12">
            Campaign was sent on {{$campaign->sendDate}}
        </div>
    </div>
    @endif
    @if(!empty($campaign->sendDate) && !empty($campaign->scheduleDate) && $campaign->scheduleDate <= $current_datetime)
    <div class="row">
        <div class="col-sm-12">
            Campaign was sent on {{$campaign->scheduleDate}}
        </div>
    </div>
    @endif
    @if(!empty($campaign->sendDate) && !empty($campaign->scheduleDate) && $campaign->scheduleDate > $current_datetime)
    <div class="row">
        <div class="col-sm-12">
            Campaign is scheduled for {{$campaign->scheduleDate}}, thou you can change it.
        </div>
    </div>
    @endif
    @endif
</div>
<p>
</p>
<div id="schedule" style="display: none;">
    {!! Form::label('schedule', 'Release Date') !!}
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
    {!! Form::button('Send Now', array('class' => 'btn btn-success btn-sm', 'name' => 'clicked', 'id'=>'send_now', 'onclick' => 'sendCampaign()',$disable)) !!}
</div>
@include('v1.parts.end_content')

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
            locale: {
                format: 'M/D/Y h:mm A'
            },
            minDate: moment(),
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
            $("#send_now").text('Schedule');
        } else {
            $("#schedule").hide();
            $("#send_now").text('Send Now');
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
    function sendCampaign() {
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
