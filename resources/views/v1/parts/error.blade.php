<?php
/**
 * Comment: A default error display bit
 * Created: 2/20/2017
 */
?>
<div class="form-group">
    @foreach (['danger', 'warning', 'success', 'info'] as $msg)
            <p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }}
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            </p>
    @endforeach
</div>
