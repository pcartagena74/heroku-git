<?php
/**
 * Comment: A default error display bit
 * Created: 2/20/2017
 */
?>
<div class="form-group">
    <ul class="form-control">

        @foreach($errors->all() as $error)

            <li class="form-control-item">{!! $error !!}</li>
        @endforeach

    </ul>
</div>
