@php
/**
 * Comment: created by Mufaddal to have locale and form
 * Created: 2/28/2020
 */
@endphp

@extends('v1.layouts.no-auth')

@section('content')
<div class="container body">
    <div class="main_container">
        <!-- page content -->
        <div class="col-md-12">
            <div class="col-middle">
                <div class="text-center text-center" style="text-align: center;">
                    <h1 class="error-number">
                        {{ $code }}
                    </h1>
                    <h2>
                        {!! $description !!}
                    </h2>
                    @auth
                        <a onclick="show()" style="cursor: pointer;" id="report_this">
                            {{ trans('messages.page_generic_exception.report_this') }}
                        </a>
                    </div>
                    <div class="mid_center" style="display:none">
                        <h3>
                            {{ trans('messages.page_generic_exception.report_issue') }}
                        </h3>
                        {{ Form::open(['url' => url('reportissue'), 'method' => 'post']) }}
                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_PUBLIC_KEY')  }}" id="feedback-recaptcha">
                        </div>
                        <span class="error" id="error_captcha">
                        </span>
                        {{-- Add Captcha --}}
                        <div class="col-xs-12 form-group pull-right top_search">
                            <div class="input-group">
                                <input class="form-control custom-control" id="subject" placeholder="{{trans('messages.page_generic_exception.place_subject')}}" required=""/>
                                <span class="input-group-addon btn btn-primary" style="border-radius: 0px 25px 25px 0px;" readonly>
                                    <i aria-hidden="true" class="fa fa-pencil">
                                    </i>
                                </span>
                                <span class="error" id="error_subject">
                                </span>
                            </div>
                        </div>
                        <div class="col-xs-12 form-group pull-right top_search">
                            <div class="input-group">
                                <textarea class="form-control custom-control" id="message" placeholder="Please describe the issue in detail." required="" rows="5" style="resize:none;">
                                </textarea>
                                <span class="error" id="error_content">
                                </span>
                                <span class="input-group-addon btn btn-primary" onclick="submitIssue()" style="border-radius: 0px 25px 25px 0px;">
                                    {{ trans('messages.page_generic_exception.btn_go') }}
                                </span>
                            </div>
                        </div>
                        {{ Form::close() }}
                        <span class="error" id="error_member">
                        </span>
                    </div>
                    <span class="success" id="success">
                    </span>
                    @endauth
                </div>
            </div>
        </div>
        <!-- /page content -->
    </div>
</div>
<script>
    function show(){
        $('#error_subject').html('');
        $('#error_content').html('');
        $('#error_member').html('');
        $('#error_captcha').html('');
           $('.mid_center').toggle();
        }
    function submitIssue(){
        $('#error_subject').html('');
        $('#error_content').html('');
        $('#error_member').html('');
        $('#error_captcha').html('');
        $.ajax({
            url: "{{url('reportissue')}}",
            type: "post",
            dataType:'json',
            data: {
                'subject':$('#subject').val().trim(),
                'content':$('#message').val().trim(),
                'g-recaptcha': $("#g-recaptcha-response").val()
               },
            success: function (response) {
               if(response.error){
                    if(response.error.subject){
                        $('#error_subject').html(response.error.subject[0]);
                    }
                    if(response.error.content){
                        $('#error_content').html(response.error.content[0]);
                    }
                   if(response.error.member) {
                        $('#error_member').html(response.error.member);
                   }if(response.error['g-recaptcha']) {
                        $('#error_captcha').html(response.error['g-recaptcha']);
                   }
               } else {
                    $('#success').html(response.success);
                    $('#report_this').hide();
                    $('.mid_center').toggle();

               }
            },
            error: function(jqXHR, textStatus, errorThrown) {
               console.log(textStatus, errorThrown);
            }
        });
    }
</script>
<script async="" defer="" src="https://www.google.com/recaptcha/api.js">
</script>
@stop
