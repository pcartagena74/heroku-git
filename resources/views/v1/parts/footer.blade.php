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
            <a href="{{ env('APP_URL') }}"><img src="/images/mCentric_logo_blue.png" alt="m|Centric" style="height: 25px;" /></a>
        @else
            <img src="/images/mCentric_logo_blue.png" hspace="50" alt="m|Centric" style="height: 25px;" />
        @endif
    </div>
</footer>