@php
    /**
     * Comment:
     * Created: 2/2/2017
     *
     * @var $event: event ojbect
     * @var $org: org object
     */

    use League\Flysystem\Filesystem;

    $category = DB::table('event-category')->where([
        ['orgID', $event->orgID],
        ['catID', $event->catID]
    ])->select('catTXT')->first();

    //$string = $event->eventStartDate->format('n/j/Y g:i A');
    //dd($string);

    // -----------------
    // Early Bird-ism
    // 1. Get today's date
    // 2. Compare to early bird dates per ticket
    // 3. Calculate new pri

    $today = Carbon\Carbon::now();

        $logo_filename = $org->orgPath . "/" . $org->orgLogo;

        try {
            if ($org->orgLogo !== null) {
                if (Storage::disk('s3_media')->exists($logo_filename)) {
                    $logo = Storage::disk('s3_media')->url($logo_filename);
                }
            }
        } catch (Exception $e) {
            $logo = '';
        }

@endphp

@extends('v1.layouts.no-auth')

@if($event->ok_to_display())
    @section('content')
        @include('v1.parts.start_content', ['header' => "$event->eventName", 'subheader' => '',
            'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
        @include('v1.parts.start_content', ['header' => 'Event Detail', 'subheader' => '',
            'w1' => '8', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

        <form method="post" action="{{ env('APP_URL') }}/regstep1/{{ $event->eventID }}" id="start_registration"
              role="form">
            {{ csrf_field() }}
            <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
                <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">{!! $event->eventDescription !!}</div>
                <div class="form-group has-feedback col-md-12 col-sm-12 col-xs-12">
                    Category: {{ $category->catTXT }}</div>

                <div id="not" class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">

                    @if($event->earlyBirdDate !== null && $event->earlyBirdDate->gte($today))
                        <div class="col-md-12 col-sm-12 col-xs-12" style="display:flex;">
                            <div class="col-md-2 col-sm-2 col-xs-2">
                                <img src="{{ env('APP_URL') }}/images/earlybird.jpg" style="float:right; width:75px;">
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-6" style="margin-top: auto; word-break: break-all;">
                                <h2><span style="color:red;">Act Now!</span> Early Bird Pricing in Effect</h2>
                            </div>
                        </div>
                    @endif
                    <table id="datatable" class="table table-striped jambo_table">
                        <thead class="cf">
                        <tr>
                            <th style="width: 40%" colspan="2">Ticket</th>
                            <th style="width: 20%">PMI Member Cost<SUP style="color: red">*</SUP></th>
                            <th style="width: 20%">Non-Member Cost</th>
                            <th style="width: 20%">Available Until</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($bundles as $bundle)
                            <tr>
                                <td style="text-align: center;">
                                    <div class="form-group">
                                        <input type="radio" name="ticketID"
                                               value="{{ $bundle->ticketID }}"
                                               required data-error="Please select an option.">
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </td>
                                <td data-title="Ticket">
                                    {{ $bundle->ticketLabel }}<SUP style='color: red'>**</SUP>

                                        <?php
//                                        $sql = "SELECT et.ticketID, et.ticketLabel, bt.ticketID as 'bundleID'
//                                                FROM `event-tickets` et
//                                                LEFT JOIN `bundle-ticket` bt ON bt.bundleid=$bundle->ticketID
//                                                    AND bt.ticketID = et.ticketID
//                                                WHERE et.eventID=$event->eventID and isaBundle=0";
                                        $b_tkts = DB::table('event-tickets')
                                            ->join('bundle-ticket', function ($join) use ($bundle) {
                                                $join->on('bundle-ticket.ticketID', '=', 'event-tickets.ticketID')
                                                    ->where('bundle-ticket.bundleID', '=', $bundle->ticketID);
                                            })->where([
                                                ['event-tickets.eventID', $event->eventID],
                                                ['event-tickets.isaBundle', 0],
                                            ])->select('event-tickets.ticketID', 'event-tickets.ticketLabel', 'bundle-ticket.ticketID',
                                                'event-tickets.maxAttendees', 'event-tickets.regCount')->get();
                                        ?>
                                    <ul>
                                        @foreach($b_tkts as $tkt)
                                                <?php
                                                if ($tkt->maxAttendees > 0 && $tkt->regCount >= $tkt->maxAttendees) {
                                                    $soldout = 1;
                                                } else {
                                                    $soldout = 0;
                                                }
                                                ?>
                                            <li>
                                                {{ $tkt->ticketLabel }}
                                            </li>
                                        @endforeach
                                    </ul>
                                    @if($soldout)
                                        <b class="red">This ticket is sold out. Add yourself to the wait list.</b>
                                    @endif
                                </td>
                                <td data-title="Member Price"><i class="fa fa-dollar"></i>
                                    @if(($bundle->earlyBirdEndDate !== null) && $bundle->earlyBirdEndDate->gt($today))
                                        <strike style="color:red;">{{ number_format($bundle->memberBasePrice, 2, '.', ',') }}</strike>
                                        <br>
                                        <i class="fa fa-dollar"></i>
                                        {{ number_format($bundle->memberBasePrice - ( $bundle->memberBasePrice * $bundle->earlyBirdPercent / 100), 2, '.', ',') }}
                                    @else
                                        {{ number_format($bundle->memberBasePrice, 2, '.', ',') }}
                                    @endif
                                </td>
                                <td data-title="Non-Member Price"><i class="fa fa-dollar"></i>
                                    @if(($bundle->earlyBirdEndDate !== null) && $bundle->earlyBirdEndDate->gt($today))
                                        <strike style="color:red;">{{ number_format($bundle->nonmbrBasePrice, 2, '.', ',') }}</strike>
                                        <br>
                                        <i class="fa fa-dollar"></i>
                                        {{ number_format($bundle->nonmbrBasePrice - ( $bundle->nonmbrBasePrice * $bundle->earlyBirdPercent / 100), 2, '.', ',') }}
                                    @else
                                        {{ number_format($bundle->nonmbrBasePrice, 2, '.', ',') }}
                                    @endif
                                </td>
                                <td data-title="Available Until">{{ $bundle->availabilityEndDate->format('n/j/Y g:i A') }}</td>
                            </tr>
                        @endforeach
                        @foreach($tickets as $ticket)
                            <tr>
                                <td style="text-align: center;">
                                    <input type="radio" name="ticketID" value="{{ $ticket->ticketID }}"
                                           required data-error="Please select an option.">
                                    <div class="help-block with-errors"></div>
                                </td>
                                <td data-title="Ticket">{{ $ticket->ticketLabel }}
                                    @if($ticket->maxAttendees > 0 && $ticket->regCount >= $ticket->maxAttendees)
                                        <br/><b class="red">This ticket is sold out. Add yourself to the wait list.</b>
                                    @endif
                                </td>
                                <td data-title="Member Price"><i class="fa fa-dollar"></i>
                                    @if(($ticket->earlyBirdEndDate !== null) && $ticket->earlyBirdEndDate->gt($today))
                                        <strike style="color:red;">{{ number_format($ticket->memberBasePrice, 2, '.', ',') }}</strike>
                                        <br>
                                        <i class="fa fa-dollar"></i>
                                        {{ number_format($ticket->memberBasePrice - ( $ticket->memberBasePrice * $ticket->earlyBirdPercent / 100), 2, '.', ',') }}
                                    @else
                                        {{ number_format($ticket->memberBasePrice, 2, '.', ',') }}
                                    @endif
                                </td>
                                <td data-title="Non-Member Price">
                                    <i class="fa fa-dollar"></i>
                                    @if(($ticket->earlyBirdEndDate !== null) && $ticket->earlyBirdEndDate->gt($today))
                                        <strike style="color:red;">{{ number_format($ticket->nonmbrBasePrice, 2, '.', ',') }}</strike>
                                        <br>
                                        <i class="fa fa-dollar"></i>
                                        {{ number_format($ticket->nonmbrBasePrice - ( $ticket->nonmbrBasePrice * $ticket->earlyBirdPercent / 100), 2, '.', ',') }}
                                    @else
                                        {{ number_format($ticket->nonmbrBasePrice, 2, '.', ',') }}
                                    @endif
                                </td>
                                <td data-title="Available Until">{{ $ticket->availabilityEndDate->format('n/j/Y g:i A') }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="5">
                                <div class="form-group">
                                    <input type="number" pattern="[1-5]" name="quantity"
                                           placeholder="  Quantity" required
                                           data-error="Please enter a value from 1 - 5.">
                                    <div class="help-block with-errors"></div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12" id="status_msg"></div>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="col-md-9 col-sm-9 col-xs-12" style="text-align: right"><input
                                id="discount_code" name="discount_code" type="text"
                                placeholder="  Enter discount code"/></div>
                    <div class="col-md-3 col-sm-3 col-xs-12"><a class="btn btn-xs btn-primary"
                                                                id="btn-validate">Validate</a></div>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-12" style="text-align: left; vertical-align: top;">
                    <img alt="Visa Logo" src="{{ env('APP_URL') }}/images/visa.png"><img alt="MasterCard Logo"
                                                                                         src="{{ env('APP_URL') }}/images/mastercard.png">
                    <button type="submit" class="btn btn-success btn-sm" id="purchase"
                            style="height: 32px;"><b>Purchase Ticket(s)</b></button>
                </div>
            </div>
        </form>
        &nbsp;<br/>
        <SUP style="color: red">*</SUP> Member pricing is applied automatically when you are 1)
        logged in and 2) have a PMI ID associated with your account.<br/>
        <SUP style='color: red'>**</SUP>Bundles include multiple tickets so you do not have to manually purchase multiple tickets.
        @if($errors->all())
            @include('v1.parts.error')
        @endif
        @include('v1.parts.end_content')

        @include('v1.parts.start_content', ['header' => 'Date &amp; Time', 'subheader' => '', 'w1' => '4', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
        <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
            <table class="table" style="border: none;">
                <tr style="border: none;">
                    <td style="text-align: right; border: none;"><h4>From:</h4></td>
                    <td style="border: none;">
                        <nobr>
                            <h4>{{ $event->eventStartDate->format('n/j/Y') }}</h4>
                        </nobr>
                        <nobr>
                            <h4>{{ $event->eventStartDate->format('g:i A') }}</h4>
                        </nobr>
                    </td>
                </tr>
                <tr style="border: none;">
                    <td style="text-align: right; border: none;"><h4>To:</h4></td>
                    <td style="border: none;">
                        <nobr>
                            <h4>{{ $event->eventEndDate->format('n/j/Y') }}</h4>
                        </nobr>
                        <nobr>
                            <h4>{{ $event->eventEndDate->format('g:i A') }}</h4>
                        </nobr>
                    </td>

                </tr>
            </table>
        </div>
        @include('v1.parts.end_content')

        @include('v1.parts.start_content', ['header' => 'Location', 'subheader' => '', 'w1' => '4', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
        <div class="col-md-12 col-sm-12 col-xs-12">
            @if($event_loc->isVirtual == 1)
                {{ $event_loc->locName }}<br>
            @else
                <div id="map_canvas" class="col-md-12 col-sm-12 col-xs-12" style="padding:15px;">
                    <iframe class="col-md-12 col-sm-12 col-xs-12" frameborder="0" scrolling="no"
                            marginheight="0" marginwidth="0"
                            src="https://maps.google.it/maps?q={{ $event_loc->addr1 }} {{ $event_loc->city }}, {{ $event_loc->state }} {{ $event_loc->zip }}&output=embed"></iframe>
                </div>
                {{ $event_loc->locName }}<br>
                {{ $event_loc->addr1 }}<br>{!! $event_loc->addr2 !!}
                @if($event_loc->addr2)
                    <br>
                @endif
                {{ $event_loc->city }}, {{ $event_loc->state }} {{ $event_loc->zip }}<br>
            @endif
        </div>
        @include('v1.parts.end_content')

        @include('v1.parts.start_content', ['header' => 'Organizer Information', 'subheader' => '', 'w1' => '4', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
        <p><img src="{{ $logo }}" alt="{!! $currentOrg->orgName !!} Logo"></p>
        {{ $event->contactOrg }}<br>
        {{ $event->contactEmail }}<br>
        <br>
        @include('v1.parts.end_content')

        @if($event->eventInfo)
            @include('v1.parts.start_content', ['header' => 'Additional Information', 'subheader' => '', 'w1' => '8', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
            {!! $event->eventInfo !!}
            @if(isset($tags))
                <p>Add Event Tags</p>
            @endif
            @include('v1.parts.end_content')
        @endif
        @include('v1.parts.end_content')

    @endsection

    @section('scripts')
        <script>
            $('#btn-validate').on('click', function (e) {
                e.preventDefault();
                validateCode({{ $event->eventID }});
            });
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        </script>
        <script>
            function validateCode(eventID) {
                var codeValue = $("#discount_code").val();
                if (FieldIsEmpty(codeValue)) {
                    var message = '<span><i class="fa fa-warning fa-2x text-warning mid_align">&nbsp;</i>Enter a discount code.</span>';
                    $('#status_msg').html(message).fadeIn(500).fadeOut(3000);

                } else {
                    $.ajax({
                        type: 'POST',
                        cache: false,
                        async: true,
                        url: '{{ env('APP_URL') }}/discount/' + eventID,
                        dataType: 'json',
                        data: {
                            event_id: eventID,
                            discount_code: codeValue
                        },
                        beforeSend: function () {
                            $('#status_msg').html('').fadeIn(0);
                        },
                        success: function (data) {
                            console.log(data);
                            var result = eval(data);
                            $('#status_msg').html(result.message).fadeIn(0);
                        },
                        error: function (data) {
                            console.log(data);
                            var result = eval(data);
                            $('#status_msg').html(result.message).fadeIn(0);
                        }
                    });
                }
            }
        </script>
    @endsection
@else
    @section('content')
        This event is no longer active.
    @endsection
@endif
