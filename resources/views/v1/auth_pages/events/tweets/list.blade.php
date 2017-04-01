<?php
/**
 * Comment:
 * Created: 4/1/2017
 */
?>
@foreach($tweets as $tweet)
    <div class="tweet">
        @include('v1.auth_pages.events.tweets.tweet-partial')
    </div>
@endforeach
