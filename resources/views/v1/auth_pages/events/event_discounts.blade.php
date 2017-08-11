<?php
/**
 * Comment: Shows the Event Defaults for an Organization
 * Created: 3/11/2017
 */

$discount_headers = ['#', 'Discount Code', 'Discount Percent', 'Flat Amount'];

$topBits = '';
?>

@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @include('v1.parts.start_content', ['header' => "Event Discounts: " . $event->eventName, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <div class="col-md-10 col-sm-10 col-xs-12">
    The <b style="color: red;">non-empty discount codes</b> here are active for this event.<br>
    Each code may have <b>EITHER</b> a Discount Percent or Amount.  If you give 1 a value, the other will be zeroed out.
    <br>&nbsp;<br>
    </div>
    <div class="col-md-2 col-sm-2 col-xs-12">
        <button type="button" id="add_discount" class="btn btn-sm btn-success" data-toggle="modal"
                data-target="#discount_modal">Add Discount
        </button>
    </div>

    <?php
    // @include('v1.parts.datatable', ['headers'=>$discount_headers, 'data'=>$discount_codes, 'scroll'=>0])
    ?>
    <table class="table table-bordered table-striped table-condensed">
        <thead>
        <tr>
            <th style="text-align: left;">#</th>
            <th style="text-align: left;">Discount Code</th>
            <th style="text-align: left;">Discount Percent</th>
            <th style="text-align: left;">Discount Amount</th>
        </tr>
        </thead>
        <tbody>
        @foreach($discount_codes as $dCode)
            <tr>
                <td style="text-align: left;"> {!! Form::open(['url'=>env('APP_URL').'/eventdiscount/'.$dCode->discountID.'/delete','method'=>'DELETE','id'=>"formConfirm-$dCode->discountID",
        'class'=>'form-horizontal', 'role'=>'form', 'onsubmit' => 'return confirm("Are you sure?")']) !!}
                    <input type="hidden" name="pk" value="{{ $dCode->discountID }}">
                    <input type="hidden" name="function" value="delete">
                    <button class="btn btn-danger btn-sm">
                        <i class="fa fa-trash"></i>
                    </button> {!! Form::close() !!} </td>
                <td style="text-align: left;">
                    <a data-pk="{{ $dCode->discountID }}" id="discountCODE{{ $dCode->discountID }}"
                       data-value="{{ $dCode->discountCODE }}" data-url="{{ env('APP_URL') }}/eventdiscounts/{{ $dCode->discountID }}"
                       data-type="text" data-placement="top"></a>
                </td>
                <td style="text-align: left;">
                    <a data-pk="{{ $dCode->discountID }}" id="percent{{ $dCode->discountID }}"
                       data-value="{{ $dCode->percent }}" data-url="{{ env('APP_URL') }}/eventdiscounts/{{ $dCode->discountID }}"
                       data-type="text" data-placement="top"></a> <i class="fa fa-percent"></i>
                </td>
                <td style="text-align: left;"><i class="fa fa-dollar"></i>
                    <a data-pk="{{ $dCode->discountID }}" id="flatAmt{{ $dCode->discountID }}"
                       data-value="{{ $dCode->flatAmt }}" data-url="{{ env('APP_URL') }}/eventdiscounts/{{ $dCode->discountID }}"
                       data-type="text" data-placement="top"></a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="col-md-4 col-sm-9 col-xs-12">
        <button type="button" id="add_discount" class="btn btn-sm btn-success" data-toggle="modal"
                data-target="#discount_modal">Add Discount
        </button>
    </div>
    <div class="col-md-4 col-sm-9 col-xs-12" style="text-align: center"></div>
    <div class="col-md-4 col-sm-9 col-xs-12" style="text-align: right"></div>
    @include('v1.parts.end_content')

@endsection

@section('modals')

    <div class="modal fade" id="discount_modal" tabindex="-1" role="dialog" aria-labelledby="discount_label"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="discount_label">Add Additional Discount</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form name="discounts" method="post" action="/eventdiscount">
                        {{ csrf_field() }}
                        <input type="hidden" name="personID" value="{{ $current_person->personID }}">
                        <input type="hidden" name="eventID" value="{{ $event->eventID }}">
                        <table id="new_discounts" class="table table-striped">
                            <thead>
                            <tr>
                                <th style="width: 10%">Discount Code</th>
                                <th style="width: 20%">Percent</th>
                                <th style="width: 20%">Dollar Amount</th>
                            </tr>
                            </thead>
                            <tbody>

                            @for($n=1; $n<=5; $n++)

                                <tr id="disc{{ $n }}_row"<?php if($n > 1) echo(' style="display:none"'); ?>>
                                    <td><input type='text' id='discountCode{{ $n }}'
                                               name='discountCode{{ $n }}'></input></td>
                                    <td><input type='text' id='percent{{ $n }}' name='percent{{ $n }}'></input>
                                    <td><input type='text' id='flatAmt{{ $n }}' name='flatAmt{{ $n }}'></input>
                                </tr>

                            @endfor

                            </tbody>
                        </table>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <button type="button" id="add_erow" class="btn btn-sm btn-warning">Add Another</button>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12" style="text-align: right">
                            <button type="button" style="display: none" id="delete_erow" class="btn btn-sm btn-danger">
                                Delete
                            </button>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" id="disc_submit" class="btn btn-sm btn-success">Save Discount</button>
                    </form>
                </div>
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
    <script>
        $(document).ready(function () {
            var setContentHeight = function () {
                // reset height
                $RIGHT_COL.css('min-height', $(window).height());

                var bodyHeight = $BODY.outerHeight(),
                    footerHeight = $BODY.hasClass('footer_fixed') ? -10 : $FOOTER.height(),
                    leftColHeight = $LEFT_COL.eq(1).height() + $SIDEBAR_FOOTER.height(),
                    contentHeight = bodyHeight < leftColHeight ? leftColHeight : bodyHeight;

                // normalize content
                contentHeight -= $NAV_MENU.height() + footerHeight;

                $RIGHT_COL.css('min-height', contentHeight);
            };

            $SIDEBAR_MENU.find('a[href="/event/create"]').parent('li').addClass('current-page').parents('ul').slideDown(function () {
                setContentHeight();
            }).parent().addClass('active');

            @if($event->eventID !== null)
            $("#add").text('Edit Event Discounts');
            @endif
        });
    </script>
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
                    $('#email_submit').text("Save Discounts");
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
                    $('#email_submit').text("Save Discount");
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
