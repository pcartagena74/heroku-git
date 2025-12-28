@php
    /**
     * Comment: display file upload page
     * Created: 4/29/2017
     */
    $topBits = '';
    if(isset($blah)){
        $count = $blah;
    } else {
        $count = 0;
    }

@endphp
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
    @include('v1.parts.start_content', ['header' => trans('messages.admin.upload.header'), 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @if($count > 0)
        <p>@lang('messages.admin.upload.count', $count)</p>
    @endif

    <p>@lang('messages.admin.upload.upload')</p>

    <form enctype='multipart/form-data' action='{{ env('APP_URL') }}/load_data' method='post'>
        {{ csrf_field() }}
        <div class="col-sm-6 col-xs-6 form-group">
            <label for="filename">{!! trans('messages.admin.upload.filename') !!}:</label>
            <input size='50' type='file' name='filename' required class="form-control">
        </div>

        <div class="col-sm-7 col-xs-7 form-group">
            <select id="dt" name='data_type' required class="form-control">
                <option value='mbrdata'>{!! trans('messages.fields.member') !!} {!! trans('messages.fields.data') !!}</option>
                <option value='evtdata'>{!! trans('messages.fields.event') !!} {!! trans('messages.fields.data') !!}</option>
            </select><br/>
        </div>

        <div class="col-sm-8 col-xs-8 form-group">
            <select id="evt" name='eventID' style='display:none;' class="form-control">
                <option value="">@lang('messages.admin.upload.select')</option>
                @foreach($events as $event)
                    <option value="{{ $event->eventID }}">{{ $event->eventID }}
                        : {{ $event->eventStartDate->format('n/Y') }}
                        -> {{ $event->eventName }} ({{ $event->registrations_count }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-sm-9 col-xs-9">
            <br/>
            <input type='submit' name='submit' value='{!! trans('messages.nav.o_upload') !!}'><br/>
        </div>
    </form>

    @include('v1.parts.end_content')

@endsection

@section('scripts')
    <script src="//cdnjs.cloudflare.com/ajax/libs/dropzone/4.3.0/min/dropzone.min.js"></script>
    <script nonce="{{ $cspScriptNonce }}">
        $(location).ready(function () {
            $('#dt').on('change', function () {
                var dt = $('#dt').val();
                if (dt == 'evtdata') {
                    $('#evt').show();
                    $('#evt').required = true;
                } else {
                    $('#evt').hide();
                    $('#evt').required = false;
                }
            });
        });
    </script>
@endsection
