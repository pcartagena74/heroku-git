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
<style> .error {color:red;} </style>
--}}
<div class="form-group">
    @foreach (['danger', 'warning', 'success', 'info'] as $msg)
        @if(Session::has('alert-'.$msg))
            <p class="alert alert-{{ $msg }}">{!!  Session::get('alert-' . $msg) !!}
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            </p>
        @endif
    @endforeach
    @if(count($errors))
        @foreach ($errors->all() as $e)
            <p class="alert alert-danger">{{ $e }}
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            </p>
        @endforeach
    @endif
</div>
