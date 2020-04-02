@php

/**
 * Comment: Create New Organization and associate it with a user.
 *
 * Created: 18/02/2020
 */

$topBits = '';  // remove this if this was set in the controller
$header = implode(" ", [trans('messages.nav.o_create')]);

@endphp
@extends('v1.layouts.auth', ['topBits' => $topBits])
@section('header')
{{--
<link href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" rel="stylesheet"/>
--}}
{{--
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet"/>
--}}
@endsection
<link href="{{ asset('vendor/file-manager/css/file-manager.css') }}" rel="stylesheet"/>
@section('content')
<iframe src="{{url('library-manager')}}" style="width: 100%; height: 500px; overflow: hidden; border: none;">
</iframe>
@endsection

@section('scripts')
<script src='{{asset("/vendor/laravel-filemanager/js/stand-alone-button.js")}}'>
</script>
{{--
<script src="{{ asset('vendor/file-manager/js/file-manager.js') }}">
</script>
--}}
<script type="text/javascript">
    $('#lfm').filemanager('file');
</script>
@endsection
@section('footer')
@endsection
