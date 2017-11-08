<?php
/**
 * Comment:
 * Created: 2/2/2017
 */
?>
<p>&nbsp</p>
<footer>
    <div class="pull-right">
        @if(!Auth::check())
            <a href="{{ env('APP_URL') }}"><img src="{{ env('APP_URL') }}/images/mCentric_logo_blue.png" alt="mCentric" style="height: 25px;" /></a>
        @else
            <img src="{{ env('APP_URL') }}/images/mCentric_logo_blue.png" hspace="50" alt="mCentric" style="height: 25px;" />
        @endif
    </div>
</footer>