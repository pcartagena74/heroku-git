<?php
/**
 * Comment: Shows the Event Defaults for an Organization
 * Created: 3/11/2017
 */

$discount_headers = ['#', 'Discount Code', 'Discount Percent', 'Flat Amount'];

$topBits = '';

$currentPerson = App\Models\Person::find(auth()->user()->id);
$currentOrg    = $currentPerson->defaultOrg;
?>

@extends('v1.layouts.auth', ['topBits' => $topBits])

@if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('event-management'))
    || Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))

@section('content')

    <div class="col-xs-12">
        <div class="col-xs-6">
            @include('v1.parts.event_buttons', ['event' => $event])
        </div>
    </div>

    @include('v1.parts.start_content',
            ['header' => trans('messages.fields.event'). " " . trans('messages.fields.discs').": " . $event->eventName,
             'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <div class="col-md-10 col-sm-10 col-xs-12">
        @if(count($discount_codes) == 0)
            @lang('messages.instructions.no_org_disc')
            <p>&nbsp;</p>
        @endif
        @lang('messages.instructions.ev_discounts')
    <br>&nbsp;<br>
    </div>
    <div class="col-md-2 col-sm-2 col-xs-12">
        <button type="button" id="add_discount" class="btn btn-sm btn-success" data-toggle="modal"
                data-target="#discount_modal">@lang('messages.buttons.add_disc')
        </button>
    </div>

    <?php
    // @include('v1.parts.datatable', ['headers'=>$discount_headers, 'data'=>$discount_codes, 'scroll'=>0])
    ?>
    <table class="table table-bordered table-striped table-condensed">
        <thead>
        <tr>
            <th style="text-align: left;">#</th>
            <th style="text-align: left;">@lang('messages.headers.disc_code')</th>
            <th style="text-align: left;">@lang('messages.headers.disc_percent')</th>
            <th style="text-align: left;">@lang('messages.headers.disc_amt')</th>
        </tr>
        </thead>
        <tbody>
        @foreach($discount_codes as $dCode)
            <tr>
                <td style="text-align: left;"> {!! Form::open(['url'=>env('APP_URL').'/eventdiscount/'.$dCode->discountID.'/delete','method'=>'DELETE','id'=>"formConfirm-$dCode->discountID",
        'class'=>'form-horizontal', 'role'=>'form', 'onsubmit' => 'return confirm("' .  trans('messages.tooltips.sure') .'")']) !!}
                    <input type="hidden" name="pk" value="{{ $dCode->discountID }}">
                    <input type="hidden" name="function" value="delete">
                    <button class="btn btn-danger btn-sm">
                        @lang('messages.symbols.trash')
                    </button> {!! Form::close() !!} </td>
                <td style="text-align: left;">
                    <a data-pk="{{ $dCode->discountID }}" id="discountCODE{{ $dCode->discountID }}"
                       data-value="{{ $dCode->discountCODE }}" data-url="{{ env('APP_URL') }}/eventdiscounts/{{ $dCode->discountID }}"
                       data-type="text" data-placement="top"></a>
                </td>
                <td style="text-align: left;">
                    <a data-pk="{{ $dCode->discountID }}" id="percent{{ $dCode->discountID }}"
                       data-value="{{ $dCode->percent }}" data-url="{{ env('APP_URL') }}/eventdiscounts/{{ $dCode->discountID }}"
                       data-type="text" data-placement="top"></a> <i class="far fa-percent"></i>
                </td>
                <td style="text-align: left;"><i class="far fa-dollar-sign"></i>
                    <a data-pk="{{ $dCode->discountID }}" id="flatAmt{{ $dCode->discountID }}"
                       data-value="{{ $dCode->flatAmt }}" data-url="{{ env('APP_URL') }}/eventdiscounts/{{ $dCode->discountID }}"
                       data-type="text" data-placement="top"></a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="col-md-4 col-sm-9 col-xs-12">
        @if(count($discount_codes) == 0)
            {!! Form::open(array('url' => '/eventdiscountfix/'.$event->eventID)) !!}
            {!! Form::submit(trans('messages.buttons.add_def_disc'), array('class' => 'btn btn-sm btn-warning', 'id' => 'add_defaults')) !!}
            {!! Form::close() !!}
        @endif
            <button type="button" id="add_discount" class="btn btn-sm btn-success" data-toggle="modal"
                    data-target="#discount_modal">@lang('messages.buttons.add_disc')
            </button>
    </div>
    <div class="col-md-4 col-sm-9 col-xs-12" style="text-align: center"></div>
    <div class="col-md-4 col-sm-9 col-xs-12" style="text-align: right"></div>
    @include('v1.parts.end_content')

@endsection

@section('modals')
    @include('v1.modals.context_sensitive_issue')
    <div class="modal fade" id="discount_modal" tabindex="-1" role="dialog" aria-labelledby="discount_label"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                {!! Form::open(['url' => env('APP_URL').'/eventdiscount', 'method' => 'post']) !!}
                <div class="modal-header">
                    <h5 class="modal-title" id="discount_label">@lang('messages.headers.add_disc')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                        <input type="hidden" name="personID" value="{{ $current_person->personID }}">
                        <input type="hidden" name="eventID" value="{{ $event->eventID }}">
                        <table id="new_discounts" class="table table-striped">
                            <thead>
                            <tr>
                                <th style="width: 50%">@lang('messages.headers.disc_code')</th>
                                <th style="width: 25%">@lang('messages.headers.disc_percent')</th>
                                <th style="width: 25%">@lang('messages.headers.disc_amt')</th>
                            </tr>
                            </thead>
                            <tbody>

                            @for($n=1; $n<=5; $n++)

                                <tr id="disc{{ $n }}_row"<?php if($n > 1) echo(' style="display:none"'); ?>>
                                    <td>
                                        {!! Form::text('discountCode'.$n, '', array('id' => 'discountCode'.$n, 'class' => 'form-control input-sm', 'placeholder' => 'Enter code')) !!}
                                    </td>
                                    <td>
                                        {!! Form::number('percent'.$n, '', array('id' => 'percent'.$n, 'class' => 'form-control input-sm', 'placeholder' => '0')) !!}
                                    </td>
                                    <td>
                                        {!! Form::number('flatAmt'.$n, '', array('id' => 'flatAmt'.$n, 'class' => 'form-control input-sm', 'placeholder' => '0')) !!}
                                    </td>
                                </tr>

                            @endfor

                            </tbody>
                        </table>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <button type="button" id="add_erow" class="btn btn-sm btn-warning">@lang('messages.buttons.another')</button>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12" style="text-align: right">
                            <button type="button" style="display: none" id="delete_erow" class="btn btn-sm btn-danger">
                                @lang('messages.buttons.delete')
                            </button>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">@lang('messages.buttons.close')</button>
                    <button type="submit" id="disc_submit" class="btn btn-sm btn-success">
                        {{ trans_choice('messages.buttons.save_disc', 1) }}
                    </button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

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

            $('[data-toggle="tooltip"]').tooltip({'placement': 'top'});
            //$.fn.editable.defaults.mode = 'inline';
            $.fn.editable.defaults.params = function (params) {
                params._token = $("meta[name=token]").attr("content");
                return params;
            };

            @foreach($discount_codes as $dCode)
            $('#discountCODE{{ $dCode->discountID }}').editable();
            $('#percent{{ $dCode->discountID }}').editable();
            $('#flatAmt{{ $dCode->discountID }}').editable();
            @endforeach

        });
    </script>
@include('v1.parts.menu-fix', array('path' => '/event/create', 'tag' => '#add',
         'newTxt' => trans('messages.fields.event') . " " . trans('messages.fields.disc'),'url_override'=>url('event/create')))
    <script>
        $(document).ready(function () {
            var i = 2;
            var x;
            $('#add_erow').click(function () {
                if (i <= 5) {
                    $('#delete_erow').show();
                    $('#email_submit').show();
                    x = "disc" + i + "_row";
                    $('#' + x).show();
                    i++;
                }
                if (i >= 3) {
                    $('#email_submit').text("{{ trans_choice('messages.buttons.save_disc', 2) }}");
                }
                if (i == 6) {
                    $('#add_erow').prop('disabled', true);
                }
            });
            $('#delete_erow').click(function () {
                if (i >= 3) {
                    y = i - 1;
                    x = "disc" + y + "_row";
                    $('#' + x).hide();
                    i--;
                    $('#add_erow').prop('disabled', false);
                }

                if (i <= 2) {
                    $('#email_submit').text("{{ trans_choice('messages.buttons.save_disc', 1) }}");
                    $('#delete_erow').hide();
                }
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            @for($i=1;$i<=5;$i++)
            $('#percent{{ $i }}').change(function () {
                if ($('#percent{{ $i }}').val() > 0) {
                    $('#flatAmt{{ $i }}').prop('disabled', true);
                } else {
                    $('#flatAmt{{ $i }}').prop('disabled', false);
                }
            });
            $('#flatAmt{{ $i }}').change(function () {
                if ($('#flatAmt{{ $i }}').val() > 0) {
                    $('#percent{{ $i }}').prop('disabled', true);
                } else {
                    $('#percent{{ $i }}').prop('disabled', false);
                }
            });
            @endfor
        });
    </script>
@endsection

@endif

