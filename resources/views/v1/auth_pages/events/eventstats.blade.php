@php
    /**
     * Comment: Display of Event Statistics
     * Created: 5/6/2020
     *
     * @var $simple_header
     * @var $simple - simple pre-parsed event data with counts/sums from registration, regfinance,
     * @var $events - event data with counts/sums from registration, regfinance,
     *
     */

if(count($simple) > 15){
    $scroll = 1;
} else {
    $scroll = 0;
}

@endphp

@extends('v1.layouts.auth')

@section('content')
    @include('v1.parts.datatable', ['headers' => $simple_header, 'data' => $simple->toArray(), 'scroll' => $scroll])
@endsection

@section('scripts')
    @include('v1.parts.footer-datatable')
    @if($scroll)
        <script>
            $(document).ready(function () {
                $('#member_table').DataTable({
                    "fixedHeader": true,
                    //"order": [[0, "asc"]]
                });
            });
        </script>
    @endif
@endsection