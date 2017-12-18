<?php
/**
 * Comment:
 * Created: 2/18/2017
 */
// , 'hidecol'=>$hidecol])

//$loc_headers  = ['#', 'Location Name', 'Street', 'Address Line #2', 'City', 'State', 'Zip', 'Admin'];
$loc_headers = ['#', 'Location Name', 'Street', 'Address Line #2', 'City', 'State', 'Zip', 'Count'];
//$hidecol['1'] = 1;
count($locations) > 15 ? $location_scroll = 1 : $location_scroll = 0;
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @include('v1.parts.start_content', ['header' => 'Event Locations Available in Add/Edit Event Form', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    This is a listing of locations used for all of the events that have been entered.
    <ul>
        <li><b>Note:</b> The 'Count' column shows the number of past or present events that reference this
            location.<br/>
            Deleting a location with associated events will <b class="red">NOT</b> cause any data issues.
        </li>
        <li>To edit any field, click on the table cell, edit the value and hit enter.</li>
        <li>To delete any address (completely), click on the trash can icon, and confirm when prompted. <br/>
            The event will no longer be listed in the add/edit event form.
        </li>
    </ul>
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

