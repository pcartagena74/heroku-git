<?php
/**
 * Comment: Pricing Page
 * Created: 10/4/2017
 */
?>
@extends('v1.layouts.no-auth_no-nav')

@section('content')

    @include('v1.parts.start_content', ['header' => 'mCentric Pricing Sheet', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @include('v1.parts.start_content', ['header' => 'Organization &amp; Member Management', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
        <div class="col-md-6 col-xs-12">
        <h4>Options</h4>
        <ul>
            <li> Upload of "member" data (requires definition, any validation requirements, expiration data)
            <li> Upload of past event registration data
            <li> Setup of administrative users
        </ul>

        <h4>Features</h4>
        <ul>
            <li> List all persons (members or otherwise) in the database associated with your organization
            <li> Member Activity Reports
            <li> Person Record Merging (in the event duplication occurs)
        </ul>
    </div>
        <div class="col-md-6 col-xs-12">
        <h4>Variable Pricing</h4>
        <ul>
            <li> Pricing dependent upon volume of member and/or event data
            <li> Administrative user setup is included up to 10 users.
        </ul>
    </div>
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Event Management', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
        <div class="col-md-6 col-xs-12">
        <h4>Options</h4>
        <ul>
            <li> Online Check-In (Volunteer run or Self Check-In)
        </ul>

        <h4>Features</h4>
        <ul>
            <li> Individual Event Listing (with or without sessions)
            <li> Event Reporting
            <li> Group Registration [backend] (up to 15 at a time)
            <li> Multiple Ticket Types (with or without Bundling)
        </ul>
    </div>
        <div class="col-md-6 col-xs-12">
        <h4>Pricing</h4>
        <ul>
            <li> Pricing dependent upon ticket price as follows:
                <ul>
                    <li> Credit Card Fee: 2.9% of total + $0.30 per transaction
                    <li> Handling Fee: 2.9% of total + $0.30 per transaction (max $5)
                    <li> <b>Note:</b> Free events do not incur any organizational costs
                </ul>
            <li> Event Management functions and reporting are included in the per-event price.
        </ul>
    </div>
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Email Marketing', 'subheader' => 'Coming Very Soon', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
        <div class="col-md-6 col-xs-12">
        <h4>Features</h4>
        <ul>
            <li> Default Email Lists (Everyone, Current Members, Expired Members)
            <li> Ability to Create Lists based on Current/Past Event Registrations
            <li> Email Open Tracking &amp; URL Click Tracking (by Campaign/Individual)
        </ul>
    </div>
        <div class="col-md-6 col-xs-12">
        <h4>Pricing</h4>
        <ul>
            <li> No Additional Fee up to 4000 emails per month
            <li> Email Above No Fee Limit: $1 per 1000 emails per month
        </ul>
    </div>
    @include('v1.parts.end_content')

    @include('v1.parts.end_content')

@endsection

@section('scripts')
@endsection
