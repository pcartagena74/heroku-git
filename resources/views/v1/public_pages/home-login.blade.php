<?php
// include in all forms:   {{ csrf_field() }}
?>
@extends('v1.layouts.no-auth_no-nav')
@section('content')
<div class="container" style="padding-top: 20px;">
    <h4>mCentric was created with specific customization for organizations like Project Management
        Institute<sup>&reg;</sup> (PMI) chapters in mind.</h4>
    <h4>Whether your organization holds events, needs to coordinate content, or wants to measure member engagement,
        mCentric can help.</h4>
    <div class="row">
        <div class="col-md-4 column_text">
            <h2>Marketing</h2>
            <p>mCentric aggregates the data that you keep about your current and prospective members. Segmenting your
                members based on the traits that are evident in your data allow for better targeting of your
                campaigns.</p>
            <p>Need to see the list of members that are expiring this month?  Next month?</p>
            <p><a class="btn btn-default" href="/details#marketing" role="button">View details &raquo;</a></p>
        </div>
        <div class="col-md-4 column_text">
            <h2>Mailings</h2>
            <p>With better segmentation capability and targeting, use mCentric's integrated email capabilities to
                execute specific campaigns and maintain contact with your constituents. </p>
            <p>Why should maintaining lists of members be so difficult?</p>
            <p><a class="btn btn-default" href="/details#mailings" role="button">View details &raquo;</a></p>
        </div>
        <div class="col-md-4 column_text">
            <h2>Meetings</h2>
            <p>Need to advertise meetings or events regardless of entrance fees? mCentric can help you setup, advertise,
                and sell tickets to your events. </p><p>If you're holding a no-fee event and still need these
                services, mCentric can accommodate no-fee events that will not impact your bottom line.</p>
            <p><a class="btn btn-default" href="/details#meetings" role="button">View details &raquo;</a></p>
        </div>
    </div>
</div>
@stop

@section('scripts')
@stop