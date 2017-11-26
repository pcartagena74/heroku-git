<?php
/**
 * Comment:
 * Created: 7/18/2017
 */
$topBits = '';
$header = ['First Name', 'Last Name', 'Email'];
$data = [];

foreach($speakers as $speaker){
    array_push($data, [$speaker->firstName, $speaker->lastName, $speaker->login]);
}
?>

@extends('v1.layouts.auth')

@section('content')
    @include('v1.parts.start_content', ['header' => 'Speaker List',
    'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

    <b>Note:</b> to add speakers to this list, register the person to an event using a speaker discount code.
    <br />

    @include('v1.parts.datatable', ['headers' => $header, 'data' => $data, 'scroll' => 1])

    @include('v1.parts.end_content')

@endsection

@section('scripts')
@include('v1.parts.footer-datatable')
<script>
    $(document).ready(function() {
        $('#datatable-fixed-header').DataTable({
            "fixedHeader": true,
            "order": [[ 1, "asc" ]]
        });
    });
</script>
@endsection

@section('modals')
@endsection
