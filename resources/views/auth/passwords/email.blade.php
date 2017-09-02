<?php

$e = request()->e;

?>
@extends('v1.layouts.no-auth-forms')

@section('header')
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link href="//maxcdn.bootstrapcdn.com/bootswatch/3.3.7/cerulean/bootstrap.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="container">
        <nav class="col-md-12 col-sm-12 col-xs-12 navbar navbar-inverse navbar-fixed-top">
            <div class="container">
                <div class="col-md-4 col-sm-4 col-xs-12" style="vertical-align: top;;">
                    <a class="navbar-brand" href="#">
                        <img style="height: 25px; vertical-align: top;" src="/images/mCentric_logo.png" alt="m|Centric"/></a>
                </div>
                <div id="navbar" class="navbar-collapse collapse col-md-6 col-sm-6 col-xs-12"
                     style="display:table-cell; vertical-align:top">
                </div><!--/.navbar-collapse -->
                <div class="col-md-12 col-sm-12 col-xs-12 navbar-inverse"><span id="err"></span></div>
            </div>
        </nav>
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading"><b>Reset Password</b></div>
                    <div class="panel-body">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form class="form-horizontal" role="form" method="POST" action="{{ route('password.email') }}">
                            {{ csrf_field() }}

                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                <label for="email" class="col-md-4 control-label">E-Mail Address</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control" name="email"
                                           value="{{ $e or old('email') }}" required>

                                    @if ($errors->has('email'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        Send Password Reset Link
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
