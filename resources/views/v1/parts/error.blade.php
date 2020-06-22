@php
/**
 * Comment: A default error display bit
 * Created: 2/20/2017
 *
 * This is the one I use most.  Usage in code is as follows:
 * Session::flash('alert-$X', 'The message to display...');
 *
 */
// Session::has('become')
$margin = 'margin-top:55px;';
if(Session::has('become')){
    $margin = '';
}
@endphp
{{--
<style>
    .error {color:red;}
</style>
--}}

    @foreach (['danger', 'warning', 'success', 'info'] as $msg)
        @if(Session::has('alert-'.$msg))
<div class="form-group" style="{{$margin}}">
    <div class="alert alert-{{ $msg }}">
        <a aria-label="close" class="close" data-dismiss="alert" href="#">
            ×
        </a>
        {!!  Session::get('alert-' . $msg) !!}
    </div>
</div>
@endif
    @endforeach
    @if(isset($errors))
    @if(count($errors))
    @php
    if(!empty($msg)){
        $margin = '';
    }
    @endphp
<div class="form-group" style="{{$margin}}">
    @foreach ($errors->all() as $e)
    <div class="alert alert-danger">
        <a aria-label="close" class="close" data-dismiss="alert" href="#">
            ×
        </a>
        {!! $e !!}
    </div>
</div>
@endforeach
    @endif
    @endif
