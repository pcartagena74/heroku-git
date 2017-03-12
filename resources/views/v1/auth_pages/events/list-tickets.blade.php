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


    @include('v1.parts.start_content', ['header' => 'Tickets for "' . $event->eventName . '"', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <p>If you need to create a "bundle" ticket, do so after adding all other non-bundle tickets.</p>

    <table id="ticket_table" class="table table-striped dataTable">
        <thead>
        <tr>
            <th style="width: 5%">#</th>
            <th style="width: 20%">Ticket Label</th>
            <th>Available Until</th>
            <th>Early Bird Date</th>
            <th>Early Bird Percent</th>
            <th>Member Price</th>
            <th>Non-Member Price</th>
            <th>Max Attendees</th>
        </tr>
        </thead>
        <tbody>

        @foreach($tickets as $ticket)
            <?php $tc++; ?>
            <tr>
                <td>
                    {!! Form::open(['url'=>'/ticket/'.$ticket->ticketID.'/delete','method'=>'DELETE','id'=>"formConfirm-$ticket->ticketID",
                            'class'=>'form-horizontal', 'role'=>'form', 'onsubmit' => 'return confirm("Are you sure?")']) !!}
                    <input type="hidden" name="pk" value="{{ $ticket->ticketID }}">
                    <input type="hidden" name="function" value="delete">
                    @if($tickets->first() == $ticket)
                        <button class="btn btn-danger btn-xs" data-toggle="tooltip"
                                title="Original ticket cannot be deleted." data-placement="top" disabled>
                            @else
                                <button class="btn btn-danger btn-xs" id="launchConfirm">
                                    @endif
                                    <i class="fa fa-trash"></i>
                                </button>
                        {!! Form::close() !!}
                </td>
                <td><a href="#" id="ticketLabel{{ $tc }}" data-value="{{ $ticket->ticketLabel }}"
                       data-url="{{ "/ticket/" . $ticket->ticketID }}"
                       data-pk="{{ $ticket->ticketID }}"></a>
                </td>
                <td><a href="#" id="availabilityEndDate{{ $tc }}" data-value="{{ $ticket->availabilityEndDate }}"
                       data-url="{{ "/ticket/" . $ticket->ticketID }}"
                       data-template="MMM D YYYY h:mm A" data-format="YYYY-MM-DD HH:mm"
                       data-viewformat="MMM D, YYYY h:mm A"
                       data-title="When should ticket sales end for this event?"
                       data-pk="{{ $ticket->ticketID }}"></a></td>
                <td><a href="#" id="earlyBirdEndDate{{ $tc }}" data-value="{{ $ticket->earlyBirdEndDate }}"
                       data-url="{{ "/ticket/" . $ticket->ticketID }}"
                       data-template="MMM D YYYY h:mm A" data-format="YYYY-MM-DD HH:mm"
                       data-viewformat="MMM D, YYYY h:mm A"
                       data-title="When should Early Bird pricing end for this event?"
                       data-pk="{{ $ticket->ticketID }}"></a></td>
                <td>
                    <a href="#" id="earlyBirdPercent{{ $tc }}" data-value="{{ $ticket->earlyBirdPercent }}"
                       data-url="{{ "/ticket/" . $ticket->ticketID }}"
                       data-pk="{{ $ticket->ticketID }}">{{ $ticket->earlyBirdPercent }}</a>
                </td>
                <td>
                    <i class="fa fa-dollar"></i>
                    <a href="#" id="memberBasePrice{{ $tc }}" data-value="{{ $ticket->memberBasePrice }}"
                       data-url="{{ "/ticket/" . $ticket->ticketID }}"
                       data-pk="{{ $ticket->ticketID }}">{{ $ticket->memberBasePrice }}</a>
                </td>
                <td>
                    <i class="fa fa-dollar"></i>
                    <a href="#" id="nonmbrBasePrice{{ $tc }}" data-value="{{ $ticket->nonmbrBasePrice }}"
                       data-url="{{ "/ticket/" . $ticket->ticketID }}"
                       data-pk="{{ $ticket->ticketID }}">{{ $ticket->nonmbrBasePrice }}</a>
                </td>
                <td><a href="#" id="maxAttendees{{ $tc }}" data-value="{{ $ticket->maxAttendees }}"
                       data-url="{{ "/ticket/" . $ticket->ticketID }}"
                       data-pk="{{ $ticket->ticketID }}">{{ $ticket->maxAttendees }}</a>
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>
    <br />
    <div class="col-md-4 col-sm-9 col-xs-12">
        <button type="button" id="add_ticket" class="btn btn-sm btn-success" data-toggle="modal"
                data-target="#ticket_modal">Add Tickets
        </button>
        <a href="/events" class="btn btn-default">Return to Event Listing</a>
    </div>
    <div class="col-md-4 col-sm-9 col-xs-12" style="text-align: center"></div>
    <div class="col-md-4 col-sm-9 col-xs-12" style="text-align: right"></div>
    @include('v1.parts.end_content')

    @if(count($bundles)>0)
        @include('v1.parts.start_content', ['header' => 'Bundle Tickets for "' . $event->eventName . '"', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

        <table id="ticket_table" class="table table-striped dataTable">
            <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 20%">Ticket Label</th>
                <th>Available Until</th>
                <th>Early Bird Date</th>
                <th>Early Bird Percent</th>
                <th>Member Price</th>
                <th>Non-Member Price</th>
            </tr>
            </thead>
            <tbody>

            @foreach($bundles as $ticket)
                <?php $bc++; ?>
                <tr>
                    <td>
                        {!! Form::open(['url'=>'/bundle/'.$ticket->ticketID.'/delete','method'=>'DELETE','id'=>"formConfirm-$ticket->ticketID",
                                'class'=>'form-horizontal', 'role'=>'form', 'onsubmit' => 'return confirm("Are you sure?")']) !!}

                        <button class="btn btn-danger btn-xs" id="launchConfirm">
                            <i class="fa fa-trash"></i></button>
                        <input type="hidden" name="pk" value="{{ $ticket->ticketID }}">
                        <input id="myDelete" type="submit" value="Go" class="hidden"/>
                        {!! Form::close() !!}
                    </td>
                    <td><a href="#" id="ticketLabel{{ $tc+$bc }}" data-value="{{ $ticket->ticketLabel }}"
                           data-url="{{ "/ticket/" . $ticket->ticketID }}"
                           data-pk="{{ $ticket->ticketID }}">{{ $ticket->ticketLabel }}</a>
                    </td>
                    <td><a href="#" id="availabilityEndDate{{ $tc+$bc }}"
                           data-url="{{ "/ticket/" . $ticket->ticketID }}"
                           data-value="{{ $ticket->availabilityEndDate }}"
                           data-template="MMM DD YYYY h:mm A" data-format="YYYY-MM-DD HH:mm"
                           data-viewformat="MMM D, YYYY h:mm A"
                           data-title="When should ticket sales end for this event?"
                           data-pk="{{ $ticket->ticketID }}"></a></td>
                    <td><a href="#" id="earlyBirdEndDate{{ $tc+$bc }}" data-value="{{ $ticket->earlyBirdEndDate }}"
                           data-template="MMM DD YYYY h:mm A" data-format="YYYY-MM-DD HH:mm"
                           data-viewformat="MMM D, YYYY h:mm A"
                           data-title="When should Early Bird pricing end for this event?"
                           data-pk="{{ $ticket->ticketID }}"></a></td>
                    <td>
                        <a href="#" id="earlyBirdPercent{{ $tc+$bc }}" data-value="{{ $ticket->earlyBirdPercent }}"
                           data-url="{{ "/ticket/" . $ticket->ticketID }}"
                           data-pk="{{ $ticket->ticketID }}">{{ $ticket->earlyBirdPercent }}</a>
                    </td>
                    <td>
                        <i class="fa fa-dollar"></i>
                        <a href="#" id="memberBasePrice{{ $tc+$bc }}" data-value="{{ $ticket->memberBasePrice }}"
                           data-url="{{ "/ticket/" . $ticket->ticketID }}"
                           data-pk="{{ $ticket->ticketID }}">{{ $ticket->memberBasePrice }}</a>
                    </td>
                    <td>
                        <i class="fa fa-dollar"></i>
                        <a href="#" id="nonmbrBasePrice{{ $tc+$bc }}" data-value="{{ $ticket->nonmbrBasePrice }}"
                           data-url="{{ "/ticket/" . $ticket->ticketID }}"
                           data-pk="{{ $ticket->ticketID }}">{{ $ticket->nonmbrBasePrice }}</a>
                    </td>
                </tr>

                <tr>
                    <td></td>
                    <th>Include?</th>
                    <th colspan="5">Ticket</th>
                </tr>

                <?php
                $sql = "SELECT et.ticketID, et.ticketLabel, bt.ticketID as 'bundleID'
                        FROM `event-tickets` et
                        LEFT JOIN `bundle-ticket` bt ON bt.bundleid=$ticket->ticketID AND bt.ticketID = et.ticketID
                        WHERE et.eventID=$event->eventID and isaBundle=0";
                $b_tkts = DB::select($sql);
                ?>
                @foreach($b_tkts as $tkt)
                    <?php $bi++; // $tkt->ticketID ?>
                    <tr>
                        <td></td>
                        <td><a id="eventID-{{ $tktIDs[$bi]=$tkt->ticketID }}" name="eventID-{{ $tkt->ticketID }}"
                               data-pk="{{ $ticket->ticketID }}" data-value="{{ $tkt->bundleID ? 1 : 0 }}"></a></td>
                        <td colspan="5">{{ $tkt->ticketLabel }}</td>
                    </tr>
                    @endforeach
                    @endforeach

            </tbody>
        </table>

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

            @for ($i = 1; $i <= $tc; $i++)

                $("#ticketLabel{{ $i }}").editable({type: 'text'});
                $("#availabilityEndDate{{ $i }}").editable({
                    type: 'combodate',
                    combodate: {
                        minYear: '{{ date("Y") }}',
                        maxYear: '{{ date("Y")+3 }}',
                        minuteStep: 15
                    }
                });
                $('#earlyBirdEndDate{{ $i }}').editable({
                    type: 'combodate',
                    combodate: {
                        minYear: '{{ date("Y") }}',
                        maxYear: '{{ date("Y")+3 }}',
                        minuteStep: 15
                    }
                });
                $('#earlyBirdPercent{{ $i }}').editable({type: 'text'});
                $('#memberBasePrice{{ $i }}').editable({type: 'text'});
                $('#nonmbrBasePrice{{ $i }}').editable({type: 'text'});
                $('#maxAttendees{{ $i }}').editable({type: 'text'});
                @endfor

                @for ($i = $tc + 1; $i <= $tc + $bc; $i++)

                $('#ticketLabel{{ $i }}').editable({type: 'text'});
                $('#availabilityEndDate{{ $i }}').editable({
                    type: 'combodate',
                    combodate: {
                        minYear: '{{ date("Y") }}',
                        maxYear: '{{ date("Y")+3 }}',
                        minuteStep: 15
                    }
                });
                $('#earlyBirdEndDate{{ $i }}').editable({
                    type: 'combodate',
                    combodate: {
                        minYear: '{{ date("Y") }}',
                        maxYear: '{{ date("Y")+3 }}',
                        minuteStep: 15
                    },
                    error: function(xhr, ajaxOptions, e) {
                        alert(xhr.status);
                        alert(e);
                    },
                    success: function(data) {
                        alert(data);
                    }
                });
                $('#earlyBirdPercent{{ $i }}').editable({type: 'text'});
                $('#memberBasePrice{{ $i }}').editable({type: 'text'});
                $('#nonmbrBasePrice{{ $i }}').editable({type: 'text'});

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
                        $('#tkt_submit').text("Save Tickets");
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
                        $('#tkt_submit').text("Save Ticket");
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
                $('#eventID-{{ $tktIDs[$i] }}').editable({
                type: 'select',
                url: {!! "'". "/bundle/$event->eventID". "'" !!},
                source: [{value: '0', text: 'No'}, {value: '1', text: 'Yes'}]
            });

            @endfor
        </script>
        <script>
            $(document).ready(function() {
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

                $("#add").text('Manage Event Tickets');
            });
        </script>

@endsection

@section('modals')

    <div class="modal fade" id="ticket_modal" tabindex="-1" role="dialog" aria-labelledby="ticket_label"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ticket_label">Add Additional Tickets</h5>
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
                                <tr id="tkt{{ $n }}_row"<?php if($n>1) echo(' style="display: none;"'); ?>>
                                    <td class="col-md-4 col-md-offset-4">
                                        <label class="control-label">Ticket Name</label>
                                        <input id='ticketLabel-{{ $n }}' name='ticketLabel-{{ $n }}' type='text'
                                               class='form-control input-sm'>
                                        <p>&nbsp;</p>
                                        <label>Max Attendees</label>
                                        <div class="input-group">
                                            <span class="input-group-addon" id="basic_user{{ $n }}"><i
                                                        class="fa fa-user"></i></span>
                                            <input name='maxAttendees-{{ $n }}' type='text' size="5" placeholder='0'
                                                   class='form-control input-sm col-md-1'
                                                   aria-describedby="basic_user{{ $n }}"></div>
                                        <br>
                                        <p><label class="control-label">Bundle</label>
                                            <input name='isaBundle-{{ $n }}' type='checkbox' value="1"
                                                   class='form-control input-sm js-switch'></p>
                                    </td>
                                    <td class="col-md-4 col-md-offset-4">
                                        <label>Available Until</label>
                                        <div class="input-group">
                                            <span class="input-group-addon" id="basic_cal"><i
                                                        class="fa fa-calendar"></i></span>
                                            <input name='availabilityEndDate-{{ $n }}' type='text'
                                                   value="{{ $event->eventStartDate }}" class='form-control input-sm'
                                                   required></div>
                                        <br/>
                                        <label>Early Bird End Date</label>
                                        <div class="input-group">
                                            <span class="input-group-addon" id="basic_cal2"><i
                                                        class="fa fa-calendar"></i></span>
                                            <input type="text" id="earlyBirdEndDate-{{ $n }}"
                                                   name="earlyBirdEndDate-{{ $n }}" class="form-control input-sm"
                                                   placeholder=""></div>
                                        <br/>
                                        <label>Early Bird Percent</label>
                                        <div class="input-group">
                                            <span class="input-group-addon" id="basic_cal2"><i
                                                        class="fa fa-percent"></i></span>
                                            <input type="text" id="earlyBirdPercent-{{ $n }}" value="{{ $default->earlyBirdPercent }}"
                                                   name="earlyBirdPercent-{{ $n }}" class="form-control input-sm"
                                                   placeholder=""></div>
                                    </td>
                                    <td class="col-md-4 col-md-offset-4">
                                        <label>Member Price</label>
                                        <div class="input-group">
                                            <span class="input-group-addon" id="basic_dollar{{ $n }}">$</span>
                                            <input name='memberBasePrice-{{ $n }}'
                                                   id='memberBasePrice-<?php echo($n); ?>' type='text' value='0.00'
                                                   class='form-control input-sm'
                                                   aria-describedby="basic_dollar<?php echo($n); ?>">
                                        </div>
                                        <br/>
                                        <label>Non-Member Price</label>
                                        <div class="input-group">
                                            <span class="input-group-addon" id="basic_dollar{{ $n }}2">$</span><input
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
                            <button type="button" id="add_row" class="btn btn-sm btn-warning">Add Another</button>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12" style="text-align: right">
                            <button type="button" style="display: none" id="delete_row" class="btn btn-sm btn-danger">
                                Delete
                            </button>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" id="tkt_submit" class="btn btn-sm btn-success">Save Ticket</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

@endsection
