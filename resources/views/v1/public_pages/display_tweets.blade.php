<?php
/**
 * Comment: Page to Display Streaming Tweets for an Event
 * Created: 4/1/2017
 */

$today = Carbon\Carbon::now();
?>
@extends('v1.layouts.no-auth')

@section('content')
    @include('v1.parts.start_content', ['header' => "$event->eventName Streaming Twitter Feed", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">

                <div class="tweet-list">
                    @if(Auth::check())
                        @include('v1.auth_pages.events.tweets.list-admin')
                    @else
                        @include('v1.auth_pages.events.tweets.list')
                    @endif
                </div>

            </div>
        </div>
    </div>

    @include('v1.parts.end_content')

@endsection

@section('scripts')

@endsection
