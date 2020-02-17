<?php
/**
 * Comment: A default error display bit
 * Created: 2/20/2017
 *
 * This is the one I use most.  Usage in code is as follows:
 * Session::flash('alert-$X', 'The message to display...');
 *
 */
?>
{{--
<style>
    .error {color:red;}
</style>
--}}
<div class="form-group">
    @foreach (['danger', 'warning', 'success', 'info'] as $msg)
        @if(Session::has('alert-'.$msg))
    <div class="alert alert-{{ $msg }}">
        <a aria-label="close" class="close" data-dismiss="alert" href="#">
            ×
        </a>
        {!!  Session::get('alert-' . $msg) !!}
    </div>
    @endif
    @endforeach
    @if(isset($errors))
    @if(count($errors))
        @foreach ($errors->all() as $e)
    <div class="alert alert-danger">
        <a aria-label="close" class="close" data-dismiss="alert" href="#">
            ×
        </a>
        {!! $e !!}
    </div>
    @endforeach
    @endif
    @endif
</div>
