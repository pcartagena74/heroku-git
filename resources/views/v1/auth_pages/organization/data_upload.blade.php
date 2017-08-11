<?php
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

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
    @include('v1.parts.start_content', ['header' => 'Data Upload', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @if($count > 0)
        <p>Prior upload of {{ $count }} records, successful.</p>
    @endif

    Upload data by browsing to file and clicking the Upload button.<p>

    <form enctype='multipart/form-data' action='{{ env('APP_URL') }}/load_data' method='post'>
        {{ csrf_field() }}
        <div class="col-sm-6 col-xs-6">

            <label for="filename">File name to import:</label>
            <input size='50' type='file' name='filename' required>

        </div>

        <div class="col-sm-7 col-xs-7">
            <select id="dt" name='data_type' required>
                <option value='mbrdata'>Member Data</option>
                <option value='evtdata'>Event Data</option>
            </select><br/>
        </div>

        <div class="col-sm-8 col-xs-8">
            <select id="evt" name='eventID' style='display:none;'>
                <option value="">Select an event...</option>
                @foreach($events as $event)
                    <option value="{{ $event->eventID }}">{{ $event->eventID }}
                        : {{ $event->eventStartDate->format('n/Y') }}
                        -> {{ $event->eventName }}</option>
                @endforeach

            </select>

        </div>
        <div class="col-sm-9 col-xs-9">
            <br />
            <input type='submit' name='submit' value='Upload'><br/>
        </div>
    </form>

    @include('v1.parts.end_content')

@endsection

@section('scripts')
    <script src="//cdnjs.cloudflare.com/ajax/libs/dropzone/4.3.0/min/dropzone.min.js"></script>
    <script>
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
