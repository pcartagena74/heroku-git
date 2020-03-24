<?php
/**
 * Comment:
 * Created: 10/22/2017
 */
?>
@extends('v1.layouts.no-auth')

@section('content')
<div class="container body">
    <div class="main_container">
        <!-- page content -->
        <div class="col-md-12">
            <div class="col-middle">
                <div class="text-center text-center" style="text-align: center;">
                    <h1 class="error-number">
                        404
                    </h1>
                    <h2>
                        Sorry but we couldn't find this page
                    </h2>
                    This page you are looking for does not exist.
                    <a onclick="show()">
                        Report this?
                    </a>
                    <div class="mid_center" style="display:none">
                        <h3>
                            Report Issue
                        </h3>
                        {{ Form::open(['url' => url('reportissue'), 'method' => 'post']) }}
                        <div class="g-recaptcha" data-sitekey="{{env('RECAPTCHA_PUBLIC_KEY')}}">
                        </div>
                        {{-- Add Captcha --}}
                        <div class="col-xs-12 form-group pull-right top_search">
                            <div class="input-group">
                                <input class="form-control custom-control" id="subject" placeholder="Subject" required=""/>
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
                                    Go!
                                </span>
                            </div>
                        </div>
                        {{ Form::close() }}
                        <span class="error" id="error_member">
                        </span>
                        <span class="success" id="success">
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- /page content -->
    </div>
</div>
<script>
    function show(){
           $('.mid_center').toggle();
        }
    function submitIssue(){
        $('#error_subject').html('');
        $('#error_content').html('');
        $('#error_member').html('');
        $.ajax({
            url: "{{url('reportissue')}}",
            type: "post",
            dataType:'json',
            data: {'subject':$('#subject').val().trim(),'content':$('#message').val().trim()},
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
                   }
               } else {
                    $('#success').html(response.success);
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
