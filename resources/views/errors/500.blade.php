<?php
/**
 * Comment:
 * Created: 10/22/2017
 */
?>
@extends('v1.layouts.no-auth_no-nav')

@section('content')
    <div class="container body">
        <div class="main_container">
            <!-- page content -->
            <div class="col-md-12">
                <div class="col-middle">
                    <div class="text-center text-center" style="text-align: center;">
                        <h1 class="error-number">500</h1>
                        <h2>Internal Server Error</h2>
                        We track these errors automatically, but if the problem persists feel free to contact us. <br />
                        In the meantime, try refreshing. <a href="#">Report this?</a>
                        <div class="mid_center">
                            <h3>Search</h3>
                            <form>
                                <div class="col-xs-12 form-group pull-right top_search">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Search for...">
                                        <span class="input-group-btn">
                                        <button class="btn btn-default" type="button">Go!</button>
                                        </span>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /page content -->
        </div>
    </div>
@stop
