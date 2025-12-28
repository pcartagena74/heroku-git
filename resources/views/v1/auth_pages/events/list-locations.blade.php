<?php
/**
 * Comment:
 * Created: 2/18/2017
 */
// , 'hidecol'=>$hidecol])

//$loc_headers  = ['#', 'Location Name', 'Street', 'Address Line #2', 'City', 'State', 'Zip', 'Admin'];
$loc_headers = ['#', trans('messages.fields.loc_name'), trans('messages.fields.street'), trans('messages.fields.addr2'),
    trans('messages.fields.city'), trans('messages.fields.state'), trans('messages.fields.zip'),
    trans('messages.fields.count'), trans('messages.headers.note'), trans('messages.actions.merge')];

$locs = [];
count($locations) > 15 ? $location_scroll = 1 : $location_scroll = 0;

$address_type = DB::select("select addrType as 'text', addrType as 'value' from `address-type`");
$state_list = DB::select("select abbrev as 'text', abbrev as 'value' from state");
$country_list = DB::select("select cntryID as 'value', cntryName as 'text' from countries");

foreach ($locations as $l) {
    $m = "<a href='" . env('APP_URL') . "/merge/l/$l->locID' data-toggle='tooltip' data-placement='top'
             title='" . trans('messages.tooltips.mr') . "' class='btn btn-xs btn-warning'>
             <i class='far fa-fw fa-code-branch'></i></a>";
    array_push($locs, [$l->locID, $l->locName, $l->addr1, $l->addr2, $l->city, $l->state, $l->zip, $l->cnt, $l->locNote, $m]);
}


?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.locations'), 'subheader' => '',
             'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @lang('messages.instructions.ev_loc')

    <div class="col-md-4 col-sm-9 col-xs-12">
        <button type="button" id="add_address" class="btn btn-sm btn-success"
                data-toggle="modal" data-target="#address_modal"> {{ trans_choice('messages.headers.add_loc', 1) }}
        </button>
    </div>

    @include('v1.parts.datatable', ['headers'=>$loc_headers, 'data'=>$locs, 'scroll'=>$location_scroll])

    @include('v1.parts.end_content')

@endsection

@section('scripts')
    @include('v1.parts.footer-datatable')
    <script nonce="{{ $cspScriptNonce }}">
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
                    editable: [[1, 'locName'], [2, 'addr1'], [3, 'addr2'], [4, 'city'], [5, 'state'], [6, 'zip'], [8, 'locNote']]
                },
                onFail: function (exception) {
                    console.log(exception);
                },
                onSuccess: function (exception) {
                    console.log(exception);
                }
            });
        });

        $(document).ready(function () {
            $('#datatable-fixed-header').DataTable({
                "fixedHeader": true,
                "order": [[1, "asc"]]
            });
        });

        $(document).ready(function () {
            var i = 2;
            var x;
            $('#add_row').click(function () {
                if (i <= 5) {
                    $('#delete_row').show();
                    $('#addr_submit').show();
                    x = "addr" + i + "_row";
                    $('#' + x).show();
                    i++;
                }
                if (i >= 3) {
                    $('#addr_submit').text("{{ trans_choice('messages.buttons.save_loc', 2) }}");
                }
                if (i == 6) {
                    $('#add_row').prop('disabled', true);
                }
            });
            $('#delete_row').click(function () {
                if (i >= 3) {
                    y = i - 1;
                    x = "addr" + y + "_row";
                    $('#' + x).hide();
                    i--;
                    $('#add_row').prop('disabled', false);
                }

                if (i <= 2) {
                    $('#addr_submit').text("{{ trans_choice('messages.buttons.save_loc', 1) }}");
                    $('#delete_row').hide();
                }
            });
        });
    </script>
    @include('v1.parts.menu-fix', array('path' => '/locations'))
@endsection

@section('modals')
    {{--
    @include('v1.modals.context_sensitive_issue')
    --}}
    <div class="modal fade" id="address_modal" tabindex="-1" role="dialog" aria-labelledby="address_label"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form name="addresses" method="post" action="/locations/create">
                    <div class="modal-header">
                        <h5 class="modal-title"
                            id="address_label"> {{ trans_choice('messages.headers.add_loc', 1) }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{ csrf_field() }}

                        @for($n=1; $n<=5; $n++)

                            <div class="col-xs-12 form-group"
                                 id="addr{{ $n }}_row"{!! ($n > 1) ? ' style="display:none;"' : '' !!}>
                                <div class="col-xs-4">
                                    @lang('messages.fields.loc_name')
                                </div>
                                <div class="col-xs-8">
                                    <input name="locName-{{ $n }}" type="text" class="form-control input-xs"
                                           placeholder="{{ trans('messages.fields.loc_name') }}">
                                </div>
                                <div class="col-xs-4">
                                    @lang('messages.profile.addr1&2')
                                </div>
                                <div class="col-xs-8">
                                    <input name="addr1-{{ $n }}" type="text"
                                           placeholder="{{ trans('messages.profile.addr1') }}"
                                           class="form-control input-xs">
                                    <input name="addr2-{{ $n }}" type="text"
                                           placeholder="{{ trans('messages.profile.addr2') }}"
                                           class="form-control input-xs">
                                </div>
                                <div class="col-xs-4">
                                    @lang('messages.profile.city'),
                                    @lang('messages.profile.state')
                                    @lang('messages.profile.zip')
                                </div>
                                <div class="col-xs-4">
                                    <input name="city-{{ $n }}" type="text"
                                           placeholder="{{ trans('messages.profile.city') }}"
                                           class="form-control input-xs">
                                </div>
                                <div class="col-xs-2">
                                    <select class="form-control input-xs" name="state-{{ $n }}">
                                        <option>...</option>
                                        @include('v1.parts.form-option-show', ['array' => $state_list])
                                    </select>
                                </div>
                                <div class="col-xs-2">
                                    <input name="zip-{{ $n }}" type="text" size="5"
                                           placeholder="{{ trans('messages.profile.zip') }}"
                                           class="form-control input-xs">
                                </div>
                                <div class="col-xs-4">
                                    @lang('messages.profile.country')
                                </div>
                                <div class="col-xs-8">
                                    <select class="form-control input-xs" name="cntryID-{{ $n }}">
                                        <option>...</option>
                                        @include('v1.parts.form-option-show', ['array' => $country_list])
                                    </select>
                                    <p></p>
                                </div>
                            </div>

                        @endfor

                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <button type="button" id="add_row"
                                    class="btn btn-sm btn-warning">@lang('messages.buttons.another')</button>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12" style="text-align: right">
                            <button type="button" style="display: none" id="delete_row" class="btn btn-sm btn-danger">
                                @lang('messages.buttons.delete')
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm"
                                data-dismiss="modal">@lang('messages.buttons.close')</button>
                        <button type="submit" id="addr_submit"
                                class="btn btn-sm btn-success">{{ trans_choice('messages.buttons.save_loc', 1) }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
