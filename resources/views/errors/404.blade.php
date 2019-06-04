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
                        <h1 class="error-number">404</h1>
                        <h2>Sorry but we couldn't find this page</h2>
                        This page you are looking for does not exist. <a onclick="show()">Report this?</a>
                        <div class="mid_center" style="display:none">
                            <h3>Report Issue</h3>
                            {{ Form::open(['url' => env('APP_URL').'/reportissue', 'method' => 'post']) }}
                            <div class="g-recaptcha" data-sitekey="{{env('RECAPTCHA_PUBLIC_KEY')}}"></div>
                            {{-- Add Captcha --}}
                                <div class="col-xs-12 form-group pull-right top_search">
                                    <div class="input-group">
                                        <textarea rows="5" class="form-control custom-control" required style="resize:none;"
                                            placeholder="This does not work yet. Please describe the issue in detail."></textarea>
                                        <span class="input-group-addon btn btn-primary"
                                            style="border-radius: 0px 25px 25px 0px;">Go!</span>
                                    </div>
                                </div>
                            {{ Form::close() }}
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
    </script>
    <script src='https://www.google.com/recaptcha/api.js' async defer></script>
@stop
