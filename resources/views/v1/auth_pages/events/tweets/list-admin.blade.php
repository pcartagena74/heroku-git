<?php
/**
 * Comment: Admin view to approve Tweets
 * Created: 4/1/2017
 */
?>
<form action="/approve-tweets" method="post">
    {{ csrf_field() }}

    @foreach($tweets as $tweet)
        <div class="tweet row">
            <div class="col-xs-8">
                @include('v1.auth_pages.events.tweets.tweet-partial')
            </div>
            <div class="col-xs-4 approval">
                <label class="radio-inline">
                    <input
                            type="radio"
                            name="approval-status-{{ $tweet->id }}"
                            value="1"
                            @if($tweet->approved)
                            checked="checked"
                            @endif
                    >
                    Approved
                </label>
                <label class="radio-inline">
                    <input
                            type="radio"
                            name="approval-status-{{ $tweet->id }}"
                            value="0"
                            @unless($tweet->approved)
                            checked="checked"
                            @endif
                    >
                    Unapproved
                </label>
            </div>
        </div>
    @endforeach

    <div class="row">
        <div class="col-sm-12">
            <input type="submit" class="btn btn-primary" value="Approve Tweets">
        </div>
    </div>

</form>

{!! $tweets->links() !!}
