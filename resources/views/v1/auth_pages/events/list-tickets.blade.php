<?php
/**
 * Comment: This is the display page for Ticket Adding, Editing and Deleting for an Individual Event
 * Created: 2/9/2017
 */

// {!! Form::open(['url'=>'/ticket/'.$ticket->ticketID.'/delete','method'=>'DELETE','class'=>'form-horizontal', 'role'=>'form','onsubmit' => 'return confirm("Are you sure?")'])!!}

// <form method="post" action="{{ "/ticket/" . $ticket->ticketID . "/delete" }}">
use App\Org;
use App\Person;

$tc = 0; $bc = 0;  $bi = 0;

$default = Org::find($event->orgID);

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    <div class="col-xs-12">
        <div class="col-xs-6">
            @include('v1.parts.event_buttons', ['event' => $event])
        </div>
    </div>

    @include('v1.parts.start_content', ['header' => trans('messages.headers.tkt4') . '"'. $event->eventName . '"',
             'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <p>@lang('messages.instructions.ev_defaults')<br>
    <b style="color:red;">@lang('messages.headers.note'): </b> @lang('messages.instructions.early_values') </p>
    <div class="col-sm-8 col-md-8 col-xs-12">
        <table class="table table-bordered table-striped table-condensed">
            <tr>
                <th style="text-align: left;">
                    @lang('messages.headers.earlybird') @lang('messages.headers.end') {{ trans_choice('messages.headers.date', 1) }}
                    @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.eb_enddate')])
                </th>
                <th style="text-align: left;">@lang('messages.headers.earlybird') @lang('messages.headers.percent') @lang('messages.fields.disc')</th>
                <th style="text-align: left;">@lang('messages.headers.preventRefunds')</th>
                <th style="text-align: left;">@lang('messages.headers.accept_cash')
                    @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.accept_cash')])
                </th>
            </tr>
            <tr>
                <td style="text-align: left;"><a id="earlyBirdDate" data-value="{{ $event->earlyBirdDate }}"
                                                 data-url="{{ env('APP_URL') }}/eventajax/{{ $event->eventID }}"
                                                 data-template="MMM D YYYY h:mm A" data-format="YYYY-MM-DD HH:mm"
                                                 data-viewformat="MMM D, YYYY h:mm A"
                                                 data-pk="{{ $event->eventID }}"></a></td>
                <td style="text-align: left;"><a id="earlyDiscount" data-value="{{ $event->earlyDiscount }}"
                                                 data-url="{{ env('APP_URL') }}/eventajax/{{ $event->eventID }}"
                                                 data-pk="{{ $event->eventID }}"></a></td>
                <td style="text-align: left;"><a id="isNonRefundable" data-value="{{ $event->isNonRefundable }}"
                                                 data-url="{{ env('APP_URL') }}/eventajax/{{ $event->eventID }}"
                                                 data-pk="{{ $event->eventID }}"></a></td>
                <td style="text-align: left;"><a id="acceptsCash" data-value="{{ $event->acceptsCash }}"
                                                 data-url="{{ env('APP_URL') }}/eventajax/{{ $event->eventID }}"
                                                 data-pk="{{ $event->eventID }}"></a></td>
            </tr>
        </table>
    </div>
    <div class="col-sm-4 col-md-4">&nbsp;</div>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>
        @lang('messages.instructions.bundle_setup')<br/>
        <b style="color:red;">@lang('messages.headers.note'):</b> @lang('messages.instructions.tkt_setup')
    </p>

    <table id="ticket_table" class="table table-striped dataTable">
        <thead>
        <tr>
            <th style="width: 5%">#</th>
            <th style="width: 20%">
                @lang('messages.headers.label')
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.tkt_label')])
            </th>
            <th style="width: 20%">
                @lang('messages.fields.availability')
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.tkt_avail')])
            </th>
            {{--
            <th>Early Bird Date</th>
            <th>Early Bird Percent</th>
            --}}
            <th>@lang('messages.fields.memprice')</th>
            <th>@lang('messages.fields.nonprice')</th>
            <th style="width: 10%">
                @lang('messages.headers.max')
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.max_set')])
            </th>
            <th style="width: 10%">
                @lang('messages.headers.suppress')?
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.suppress')])
            </th>
            <th style="width: 20%">
                @lang('messages.headers.no_discount')?
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.no_discount')])
            </th>
        </tr>
        </thead>
        <tbody>

        @foreach($tickets as $ticket)
            <?php $tc++; ?>
            <tr>
                <td>
                    @if($tickets->first() == $ticket)
                        <a class="btn btn-danger btn-xs" data-toggle="tooltip"
                                title="{{ trans('messages.tooltips.tkt_no_del') }}"
                                data-placement="right" disabled>
                            @lang('messages.symbols.trash')
                        </a>
                    @else
                        {!! Form::open(['url'=>env('APP_URL').'/ticket/'.$ticket->ticketID.'/delete','method'=>'DELETE','id'=>"formConfirm-$ticket->ticketID",
                                'class'=>'form-horizontal', 'role'=>'form']) !!}
                        <input type="hidden" name="pk" value="{{ $ticket->ticketID }}">
                        <input type="hidden" name="function" value="delete">
                        <button class="btn btn-danger btn-xs" id="launchConfirm" onclick="return confirm('{{ trans('messages.tooltips.sure') }}');">
                            @lang('messages.symbols.trash')
                        </button>
                        {!! Form::close() !!}
                    @endif
                </td>
                <td><a href="#" id="ticketLabel-{{ $tc }}" data-value="{{ $ticket->ticketLabel }}"
                       data-url="{{ env('APP_URL') }}{{ "/ticket/" . $ticket->ticketID }}"
                       data-pk="{{ $ticket->ticketID }}"></a>
                </td>
                <td><a href="#" id="availabilityEndDate-{{ $tc }}" data-value="{{ $ticket->availabilityEndDate }}"
                       data-url="{{ env('APP_URL') }}{{ "/ticket/" . $ticket->ticketID }}"
                       data-template="MMM D YYYY h:mm A" data-format="YYYY-MM-DD HH:mm"
                       data-viewformat="MMM D, YYYY h:mm A"
                       data-title="{{ trans('messages.tooltips.sales_end') }}"
                       data-pk="{{ $ticket->ticketID }}"></a></td>
                <td>
                    @lang('messages.symbols.cur')
                    <a href="#" id="memberBasePrice-{{ $tc }}" data-value="{{ $ticket->memberBasePrice }}"
                       data-url="{{ env('APP_URL') }}/ticket/{{ $ticket->ticketID }}"
                       data-pk="{{ $ticket->ticketID }}">{{ $ticket->memberBasePrice }}</a>
                </td>
                <td>
                    @lang('messages.symbols.cur')
                    <a href="#" id="nonmbrBasePrice-{{ $tc }}" data-value="{{ $ticket->nonmbrBasePrice }}"
                       data-url="{{ env('APP_URL') }}/ticket/{{ $ticket->ticketID }}"
                       data-pk="{{ $ticket->ticketID }}">{{ $ticket->nonmbrBasePrice }}</a>
                </td>
                <td><a href="#" id="maxAttendees-{{ $tc }}" data-value="{{ $ticket->maxAttendees }}"
                       data-url="{{ env('APP_URL') }}/ticket/{{ $ticket->ticketID }}"
                       data-pk="{{ $ticket->ticketID }}">{{ $ticket->maxAttendees }}</a>
                </td>
                <td>
                    @if($tickets->first() == $ticket && count($tickets) == 1)
                        @lang('messages.yesno_check.no')
                    @else
                        <a href="#" id="isSuppressed-{{ $tc }}" data-value="{{ $ticket->isSuppressed }}"
                           data-url="{{ env('APP_URL') }}/ticket/{{ $ticket->ticketID }}"
                           data-pk="{{ $ticket->ticketID }}"></a>
                    @endif
                </td>
                <td>
                    <a href="#" id="isDiscountExempt-{{ $tc }}" data-value="{{ $ticket->isDiscountExempt }}"
                       data-url="{{ env('APP_URL') }}/ticket/{{ $ticket->ticketID }}"
                       data-pk="{{ $ticket->ticketID }}"></a>
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
    <br/>
    <div class="col-md-4 col-sm-6 col-xs-12">
        <button type="button" id="add_ticket" class="btn btn-sm btn-success" data-toggle="modal"
                data-target="#ticket_modal">@lang('messages.buttons.add_tkt')
        </button>
        <a href="{{ env('APP_URL') }}/manage_events" class="btn btn-default">@lang('messages.buttons.return')</a>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12" style="text-align: left;">
        @if($event->hasTracks > 0)
            <a href="{{ env('APP_URL') }}/tracks/{{ $event->eventID }}" class="btn btn-default btn-primary">@lang('messages.buttons.t&s_edit')</a>
        @endif
    </div>
    @include('v1.parts.end_content')

    @if(count($bundles)>0)
        @include('v1.parts.start_content',
                 ['header' => trans_choice('messages.headers.bundles', count($bundles)) . '"' . $event->eventName . '"',
                  'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

        <table id="ticket_table" class="table table-striped dataTable">
            <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 20%">@lang('messages.headers.label')</th>
                <th>@lang('messages.fields.availability')</th>
                <th>@lang('messages.fields.memprice')</th>
                <th>@lang('messages.fields.nonprice')</th>
            </tr>
            </thead>
            <tbody>

            @foreach($bundles as $ticket)
                <?php $bc++; ?>
                <tr>
                    <td>
                        {!! Form::open(['url'=>env('APP_URL').'/bundle/'.$ticket->ticketID.'/delete','method'=>'DELETE','id'=>"formConfirm-$ticket->ticketID",
                                'class'=>'form-horizontal', 'role'=>'form']) !!}

                        <button class="btn btn-danger btn-xs" id="launchConfirm" onclick="return confirm('{{ trans('messages.tooltips.sure') }}');">
                            @lang('messages.symbols.trash')
                        </button>
                        <input type="hidden" name="pk" value="{{ $ticket->ticketID }}">
                        <input id="myDelete" type="submit" value="Go" class="hidden"/>
                        {!! Form::close() !!}
                    </td>
                    <td><a href="#" id="ticketLabel-{{ $tc+$bc }}" data-value="{{ $ticket->ticketLabel }}"
                           data-url="{{ env('APP_URL') }}/ticket/{{ $ticket->ticketID }}"
                           data-pk="{{ $ticket->ticketID }}">{{ $ticket->ticketLabel }}</a>
                    </td>
                    <td><a href="#" id="availabilityEndDate-{{ $tc+$bc }}"
                           data-url="{{ env('APP_URL') }}/ticket/{{ $ticket->ticketID }}"
                           data-value="{{ $ticket->availabilityEndDate }}"
                           data-template="MMM DD YYYY h:mm A" data-format="YYYY-MM-DD HH:mm"
                           data-viewformat="MMM D, YYYY h:mm A"
                           data-title="{{ trans('messages.tooltips.sales_end') }}"
                           data-pk="{{ $ticket->ticketID }}"></a></td>
            <td>
                @lang('messages.symbols.cur')
                <a href="#" id="memberBasePrice-{{ $tc+$bc }}" data-value="{{ $ticket->memberBasePrice }}"
                   data-url="{{ env('APP_URL') }}/ticket/{{ $ticket->ticketID }}"
                   data-pk="{{ $ticket->ticketID }}">{{ $ticket->memberBasePrice }}</a>
            </td>
            <td>
                @lang('messages.symbols.cur')
                <a href="#" id="nonmbrBasePrice-{{ $tc+$bc }}" data-value="{{ $ticket->nonmbrBasePrice }}"
                   data-url="{{ env('APP_URL') }}/ticket/{{ $ticket->ticketID }}"
                   data-pk="{{ $ticket->ticketID }}">{{ $ticket->nonmbrBasePrice }}</a>
            </td>
        </tr>

        <tr>
            <td></td>
            <th>
                @lang('messages.headers.include')?
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.bundle_include')])
            </th>
            <th colspan="5">@lang('messages.fields.ticket')</th>
        </tr>

<?php
        $sql = "SELECT et.ticketID, et.ticketLabel, bt.ticketID as 'bundleID'
                FROM `event-tickets` et
                LEFT JOIN `bundle-ticket` bt ON bt.bundleid=$ticket->ticketID AND bt.ticketID = et.ticketID
                WHERE et.eventID=$event->eventID and isaBundle=0";
        $b_tkts = DB::select($sql);
?>
        @foreach($b_tkts as $tkt)
<?php $bi++; $bid[$bi] = $ticket->ticketID; // $tkt->ticketID ?>
            <tr>
                <td></td>
                <td><a id="eventID-{{ $ticket->ticketID }}-{{ $tktIDs[$bi]=$tkt->ticketID }}"
                       name="eventID-{{ $ticket->ticketID }}-{{ $tkt->ticketID }}"
                       data-pk="{{ $ticket->ticketID }}"
                       data-value="{{ $tkt->bundleID ? 1 : 0 }}"></a></td>
                <td colspan="5">{{ $tkt->ticketLabel }}</td>
            </tr>
        @endforeach
    @endforeach

    </tbody>
</table>
<br/>
<div class="col-md-4 col-sm-6 col-xs-12">
    <button type="button" id="add_ticket" class="btn btn-sm btn-success" data-toggle="modal"
            data-target="#ticket_modal">@lang('messages.buttons.add_tkt')
    </button>
    <a href="/events" class="btn btn-default">@lang('messages.buttons.return')</a>

</div>
<div class="col-md-4 col-sm-6 col-xs-12" style="text-align: left;">
    @if($event->hasTracks > 0)
    <a href="/tracks/{{ $event->eventID }}" class="btn btn-default btn-primary">@lang('messages.buttons.t&s_edit')</a>
    @endif
</div>

@include('v1.parts.end_content')
@endif

@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.fn.editable.defaults.mode = 'popup';

        $("#earlyBirdDate").editable({
            type: 'combodate',
            placement: 'right',
            combodate: {
                minYear: '{{ date("Y") }}',
                maxYear: '{{ date("Y")+3 }}',
                minuteStep: 15
            },
            success: function () {
                window.location = '{{ env("APP_URL") . "/event-tickets/" . $event->eventID }}';
            }
        });
        $("#earlyDiscount").editable({
            type: 'text',
            success: function () {
                window.location = "{{ env('APP_URL') . '/event-tickets/' . $event->eventID }}";
            }
        });
        $("#isNonRefundable").editable({
            type: 'select',
            source: [
                { value: 0, text: '{{ trans('messages.yesno_check.no') }}'},
                { value: 1, text: '{{ trans('messages.yesno_check.yes') }}'}
            ]
        });

        $("#acceptsCash").editable({
            type: 'select',
            source: [
                { value: 0, text: '{{ trans('messages.yesno_check.no') }}'},
                { value: 1, text: '{{ trans('messages.yesno_check.yes') }}'}
            ]
        });

    @for ($i = 1; $i <= $tc; $i++)

        $("#ticketLabel-{{ $i }}").editable({type: 'text'});
        $("#availabilityEndDate-{{ $i }}").editable({
            type: 'combodate',
            combodate: {
                minYear: '{{ date("Y") }}',
                maxYear: '{{ date("Y")+3 }}',
                minuteStep: 15
            }
        });

        $('#memberBasePrice-{{ $i }}').editable({type: 'text'});
        $('#nonmbrBasePrice-{{ $i }}').editable({type: 'text'});
        $('#maxAttendees-{{ $i }}').editable({type: 'text'});

        $('#isSuppressed-{{ $i }}').editable({
            type: 'select',
            source: [
                { value: 0, text: '{{ trans('messages.yesno_check.no') }}'},
                { value: 1, text: '{{ trans('messages.yesno_check.yes') }}'}
            ]
        });

        $('#isDiscountExempt-{{ $i }}').editable({
            type: 'select',
            source: [
                { value: 0, text: '{{ trans('messages.yesno_check.no') }}'},
                { value: 1, text: '{{ trans('messages.yesno_check.yes') }}'}
            ]
        });

        @endfor

        @for ($i = $tc + 1; $i <= $tc + $bc; $i++)

        $("#ticketLabel-{{ $i }}").editable({type: 'text'});
        $('#availabilityEndDate-{{ $i }}').editable({
            type: 'combodate',
            combodate: {
                minYear: '{{ date("Y") }}',
                maxYear: '{{ date("Y")+3 }}',
                minuteStep: 15
            }
        });
        $('#earlyBirdEndDate-{{ $i }}').editable({
            type: 'combodate',
            combodate: {
                minYear: '{{ date("Y") }}',
                maxYear: '{{ date("Y")+3 }}',
                minuteStep: 15
            },
            error: function (xhr, ajaxOptions, e) {
            },
            success: function (data) {
                //alert(data);
            }
        });
        $('#earlyBirdPercent-{{ $i }}').editable({type: 'text'});
        $('#memberBasePrice-{{ $i }}').editable({type: 'text'});
        $('#nonmbrBasePrice-{{ $i }}').editable({type: 'text'});
        @endfor
    });
</script>

<script>
    $(document).ready(function () {
        var i = 2;
        var x;
        $('#add_row').click(function () {
            if (i <= 5) {
                $('#delete_row').show();
                $('#tkt_submit').show();
                x = "tkt" + i + "_row";
                $('#' + x).show();
                i++;
            }
            if (i >= 3) {
                $('#tkt_submit').text("{{ trans_choice('messages.buttons.save_tkt', 2) }}");
            }
            if (i == 6) {
                $('#add_row').prop('disabled', true);
            }
        });
        $('#delete_row').click(function () {
            if (i >= 3) {
                y = i - 1;
                x = "tkt" + y + "_row";
                $('#' + x).hide();
                i--;
                $('#add_row').prop('disabled', false);
            }

            if (i <= 2) {
                $('#tkt_submit').text("{{ trans_choice('messages.buttons.save_tkt', 1) }}");
                $('#delete_row').hide();
            }
        });
    });
</script>

@for($i=1; $i=0; $i++)
@include('v1.parts.footer-daterangepicker', ['fieldname' => 'availabilityEndDate'. $i, 'time' => 'true', 'single' => 'true'])
@endfor

<script>
    @for ($i = 1; $i <= $bi; $i++)
        $("#eventID-{{ $bid[$i] }}-{{ $tktIDs[$i] }}").editable({
            type: 'select',
            source: [{value: '0', text: 'No'}, {value: '1', text: 'Yes'}],
            url: "{{ env('APP_URL') }}/bundle/{{ $event->eventID }}",
            error: function (xhr, ajaxOptions, e) {
                console.log(xhr);
                console.log(ajaxOptions);
                console.log(e);
            },
            success: function (data) {
                //console.log(data);
            }
        });
    @endfor
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

        $SIDEBAR_MENU.find('a[href="{{ env('APP_URL') }}/event/create"]').parent('li').addClass('current-page').parents('ul').slideDown(function () {
            setContentHeight();
        }).parent().addClass('active');

        $("#add").text('{{ trans('messages.nav.ev_mt') }}');
    });
</script>

@endsection

@section('modals')
<div class="modal fade" id="ticket_modal" tabindex="-1" role="dialog" aria-labelledby="ticket_label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ticket_label">@lang('messages.buttons.add_tkt')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body form-group">
                <form id="ticket_form" name="tickets" method="post" action="/tickets/create">
                    {{ csrf_field() }}
                    <input type="hidden" name="eventID" value="{{ $event->eventID }}">
                    <table id="new_tickets" class="table table-striped table-bordered">
                        @for($n=1;$n<=5;$n++)
                        <tr id="tkt{{ $n }}_row"<?php if($n > 1) echo(' style="display: none;"'); ?>>
                            <td class="col-md-4 col-md-offset-4">
                                <label class="control-label">@lang('messages.headers.label')</label>
                                <input id='ticketLabel-{{ $n }}' name='ticketLabel-{{ $n }}' type='text'
                                       class='form-control input-sm'>
                                <p>&nbsp;</p>
                                <label>@lang('messages.headers.max')</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic_user{{ $n }}"><i
                                                class="fas fa-user"></i></span>
                                    <input name='maxAttendees-{{ $n }}' type='text' size="5" placeholder='0'
                                           class='form-control input-sm col-md-1'
                                           aria-describedby="basic_user{{ $n }}"></div>
                                <br>
                                <p><label class="control-label">@lang('messages.headers.bundle')</label>
                                    <input name='isaBundle-{{ $n }}' type='checkbox' value="1"
                                           class='form-control input-sm js-switch'></p>
                            </td>
                            <td class="col-md-4 col-md-offset-4">
                                <label>@lang('messages.fields.availability')</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic_cal"><i
                                                class="far fa-calendar"></i></span>
                                    <input name='availabilityEndDate-{{ $n }}' type='text'
                                           value="{{ $event->eventStartDate }}" class='form-control input-sm'
                                           required></div>
                                <br/>
                                <label>@lang('messages.headers.earlybird') @lang('messages.headers.end') {{ trans_choice('messages.headers.date', 1) }}</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic_cal2"><i
                                                class="far fa-calendar"></i></span>
                                    <input type="text" id="earlyBirdEndDate-{{ $n }}"
                                           value="{{ $event->earlyBirdDate }}"
                                           name="earlyBirdEndDate-{{ $n }}" class="form-control input-sm"
                                           placeholder=""></div>
                                <br/>
                                <label>@lang('messages.headers.earlybird') @lang('messages.headers.percent')</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic_cal2"><i
                                                class="far fa-percent"></i></span>
                                    <input type="text" id="earlyBirdPercent-{{ $n }}"
                                           value="{{ $default->earlyBirdPercent }}"
                                           name="earlyBirdPercent-{{ $n }}" class="form-control input-sm"
                                           placeholder=""></div>
                            </td>
                            <td class="col-md-4 col-md-offset-4">
                                <label>@lang('messages.fields.memprice')</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic_dollar{{ $n }}">@lang('messages.symbols.cur')</span>
                                    <input name='memberBasePrice-{{ $n }}'
                                           id='memberBasePrice-{{ $n }}' type='text' value='0.00'
                                           class='form-control input-sm'
                                           aria-describedby="basic_dollar{{ $n }}">
                                </div>
                                <br/>
                                <label>@lang('messages.fields.nonprice')</label>
                                <div class="input-group">
                                    <span class="input-group-addon" id="basic_dollar{{ $n }}2">@lang('messages.symbols.cur')</span><input
                                            name='nonmbrBasePrice-{{ $n }}' id='nonmbrBasePrice-{{ $n }}'
                                            type='text' value='0.00'
                                            class='form-control input-sm'
                                            aria-describedby="basic_dollar{{ $n }}2">
                                </div>
                            </td>
                        </tr>
                        @endfor

                    </table>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <button type="button" id="add_row" class="btn btn-sm btn-warning">@lang('messages.buttons.another')</button>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-12" style="text-align: right">
                        <button type="button" style="display: none" id="delete_row"
                                class="btn btn-sm btn-danger">
                            Delete
                        </button>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">@lang('messages.buttons.close')</button>
                <button type="submit" id="tkt_submit" class="btn btn-sm btn-success">{{ trans_choice('messages.buttons.save_tkt', 1) }}</button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
