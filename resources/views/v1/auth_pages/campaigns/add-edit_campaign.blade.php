<?php
/**
 * Comment: Display the form to store or update a new campaign
 * Created: 9/18/2017
 */

if(!isset($campaign)) {
    $campaign = '';
}

$topBits = '';  // remove this if this was set in the controller

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @if($campaign == null)
        {!! Form::open(array('url' => env('APP_URL')."/campaign", 'method' => 'post')) !!}
    @else
        {!! Form::model($campaign, array('url' => env('APP_URL')."/campaign", 'method' => 'post')) !!}
    @endif

    @include('v1.parts.start_content', ['header' => ' Campaign Message',
             'subheader' => '', 'w1' => '9', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <div class="accordion" id="accordion" role="tablist" aria-multiselectable="true">
        <div class="panel">
            <a class="panel-heading" role="tab" id="headingOne" data-toggle="collapse" data-parent="#accordion"
               href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                <i class="panel-title">Message Header</i>
            </a>
            <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">
                    <div class="form-group col-sm-6 col-xs-12">
                        @if($campaign == null)
                            {!! Form::text('from_name', $org->orgName, array('class' => 'form-control input-sm', 'placeholder' => 'From Name')) !!}
                        @else
                            {!! Form::text('from_name', $campaign->fromName, array('class' => 'form-control input-sm', 'placeholder' => 'From Name')) !!}
                        @endif
                    </div>
                    <div class="form-group col-sm-6 col-xs-12">
                        @if($campaign == null)
                            {!! Form::text('from_email', $org->adminEmail, array('class' => 'form-control input-sm', 'placeholder' => 'From Email')) !!}
                        @else
                            {!! Form::text('from_email', $campaign->fromEmail, array('class' => 'form-control input-sm', 'placeholder' => 'From Email')) !!}
                        @endif
                    </div>
                    <div class="form-group col-sm-12 col-xs-12">
                        @if($campaign == null)
                            {!! Form::text('subject', '', array('class' => 'form-control input-sm', 'placeholder' => 'Subject Line')) !!}
                        @else
                            {!! Form::text('subject', $campaign->subject, array('class' => 'form-control input-sm', 'placeholder' => 'Subject Line')) !!}
                        @endif
                    </div>
                    <div class="form-group col-sm-12 col-xs-12">
                        @if($campaign == null)
                            {!! Form::text('preheader', '', array('class' => 'form-control input-sm', 'placeholder' => 'Preheader Line')) !!}
                        @else
                            {!! Form::text('preheader', $campaign->preheader, array('class' => 'form-control input-sm', 'placeholder' => 'Preheader Line')) !!}
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="panel">
            <a class="panel-heading" role="tab" id="headingTwo" data-toggle="collapse" data-parent="#accordion"
               href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                <i class="panel-title">Message Body</i>
            </a>
            <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
                <div class="panel-body">
                    @if($campaign == null)
                        {!! Form::textarea('content', '', array('class' => 'form-control rich')) !!}
                    @else
                        {!! Form::textarea('content', $campaign->content, array('class' => 'form-control rich')) !!}
                    @endif
                </div>
            </div>
        </div>
        <div class="panel">
            <a class="panel-heading" role="tab" id="headingThree" data-toggle="collapse" data-parent="#accordion"
               href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                <i class="panel-title">Email List</i>
            </a>
            <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
                <div class="panel-body">
                    @if($campaign == null)
                    @else
                    @endif
                </div>
            </div>
        </div>
    </div>
    @include('v1.parts.end_content')
    {{-- Test Email Div --}}
    <div>
        @include('v1.parts.start_content', ['header' => 'Test Emails',
                 'subheader' => '', 'w1' => '3', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

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
        <a id="add_email" onclick="javascript:add_email();">Add Another</a>
        <p>
        <div class="form-group">
            {!! Form::label('note', 'Personal Note') !!}
            {!! Form::textarea('note', '', array('class' => 'form-control', 'rows' => '4',
                'placeholder' => 'Enter a note that will appear at the top of the test message.')) !!}
        </div>
        <div class="form-group">
            {!! Form::submit('Send Test Message', array('class' => 'btn btn-primary btn-sm', 'name' => 'clicked')) !!}
        </div>
        @include('v1.parts.end_content')
    </div>

    @include('v1.parts.start_content', ['header' => 'Campaign Scheduling',
             'subheader' => '', 'w1' => '3', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div class="form-group">
        @if($campaign == null)
            <div class="col-sm-3">{!! Form::label('send', 'Send Now', array('class' => 'control-label')) !!}</div>
            <div class="col-sm-5" style="text-align: center;">
                {!! Form::checkbox('send', '1', false, array('class' => 'js-switch')) !!}
            </div>
            <div class="col-sm-3">{!! Form::label('send', 'Send Later', array('class' => 'control-label')) !!}</div>
        @else
            <div class="col-sm-3"> {!! Form::label('send', 'Send Now', array('class' => 'control-label')) !!} </div>
            <div class="col-sm-5" style="text-align: center;">
                {!! Form::checkbox('send', '1', false, array('class' => 'js-switch')) !!}
            </div>
            <div class="col-sm-3">{!! Form::label('send', 'Send Later', array('class' => 'control-label')) !!}</div>
        @endif
    </div>
    <p>&nbsp;</p>
    <div id="schedule" style="display: none;">
        {!! Form::label('schedule', 'Release Date') !!}
        @if($campaign == null)
            <div class="form-group col-sm-12">
                {!! Form::text('schedule', '', array('class' => 'form-control input-sm has-feedback-left')) !!}
                <span class="fa fa-calendar form-control-feedback left" aria-hidden="true"></span>
            </div>
        @else
            <div class="form-group col-sm-12">
                {!! Form::text('schedule', '', array('class' => 'form-control has-feedback-left')) !!}
                <span class="fa fa-calendar form-control-feedback left" aria-hidden="true"></span>
            </div>
        @endif
    </div>
    <div class="form-group">
        {!! Form::submit('Send Now', array('class' => 'btn btn-success btn-sm', 'name' => 'clicked', 'id' => 'clicked')) !!}
    </div>
    @include('v1.parts.end_content')

    @if($campaign === null)
    @else
    @endif

    @if($campaign === null)
    @else
    @endif



@endsection

@section('scripts')
    @include('v1.parts.footer-tinymce2')
    @include('v1.parts.footer-daterangepicker', ['fieldname' => 'schedule', 'time' => 'true', 'single' => 'true'])
    <script>
        var x = 2;
        function add_email() {
            $('#email' + x).show();
            x += 1;
            if (x > 5) {
                $('#add_email').hide();
            }
        }
        $(document).ready(function () {
            $('#schedule').val(moment(new Date($('#schedule').val())).format("MM/DD/YYYY HH:mm A"));
        });
    </script>
    <script>
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
        });
    </script>
    <script>
        $('#send').on('change', function () {
            $("#schedule").toggle();
            if($("#clicked").val() == 'Send Now'){
                $("#clicked").val('Schedule');
            } else {
                $("#clicked").val('Send Now');
            }
        });
    </script>
    <script>
        $('form').submit(function () {
            $('#schedule').each(function () {
                $(this).val(moment(new Date($(this).val())).format("YYYY-MM-DD HH:mm:ss"))
            });
        });
    </script>
@endsection