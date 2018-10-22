<?php
/**
 * Comment:
 * Created: 2/18/2017
 */
// , 'hidecol'=>$hidecol])

//$loc_headers  = ['#', 'Location Name', 'Street', 'Address Line #2', 'City', 'State', 'Zip', 'Admin'];
$loc_headers = ['#', trans('messages.fields.loc_name'), trans('messages.fields.street'), trans('messages.fields.addr2'),
                trans('messages.fields.city'), trans('messages.fields.state'), trans('messages.fields.zip'),
                trans('messages.fields.count')];

count($locations) > 15 ? $location_scroll = 1 : $location_scroll = 0;
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.locations'), 'subheader' => '',
             'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @lang('messages.instructions.ev_loc')
    @include('v1.parts.datatable', ['headers'=>$loc_headers, 'data'=>$locations, 'scroll'=>$location_scroll])

    @include('v1.parts.end_content')

@endsection

@section('scripts')
    @include('v1.parts.footer-datatable')
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#datatable-fixed-header').Tabledit({
                type: 'post',
                rowIdentifier: 'locID',
                hideIdentifier: 'true',
                editButton: false,
                deleteButton: true,
                restoreButton: false,
                eventType: 'click',
                url: '{{ env('APP_URL') }}/location/update',
                columns: {
                    identifier: [0, 'locID'],
                    editable: [[1, 'locName'], [2, 'addr1'], [3, 'addr2'], [4, 'city'], [5, 'state'], [6, 'zip']]
                },
                onFail: function (exception) {
                    console.log(exception);
                },
                onSuccess: function (exception) {
                    console.log(exception);
                }
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            $('#datatable-fixed-header').DataTable({
                "fixedHeader": true,
                "order": [[1, "asc"]]
            });
        });
    </script>
@endsection

