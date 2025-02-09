<?php
/**
 * Comment: This is the output of tickets for an event that would be
 *          available via API for display on non-mCentric sites
 * Created: 11/29/2017; Updated 10/13/2024 for Laravel 9.x
 * @var $event ;
 * @var $orgLogoPath ;
 */

use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use Aws\S3\S3Client;
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
// 3. Calculate new price

$today = Carbon\Carbon::now();

$logo_filename = $orgLogoPath->orgPath . "/" . $orgLogoPath->orgLogo;

try {
    if (Storage::disk('s3_media')->exists($logo_filename)) {
        $logo = Storage::disk('s3_media')->url($logo_filename);
    }
} catch (Exception $e) {
    $logo = '#';
}
?>
@extends('v1.layouts.no-auth_no-nav_simple')

@section('content')
    <form method="post" action="{{ env('APP_URL') }}/regstep1/{{ $event->eventID }}" id="start_registration"
          role="form">
        {{ csrf_field() }}
        <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">

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
                <thead>
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
                        <td>
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
                        <td><i class="fa fa-dollar"></i>
                            @if(($bundle->earlyBirdEndDate !== null) && $bundle->earlyBirdEndDate->gt($today))
                                <strike style="color:red;">{{ number_format($bundle->memberBasePrice, 2, '.', ',') }}</strike>
                                <br>
                                <i class="fa fa-dollar"></i>
                                {{ number_format($bundle->memberBasePrice - ( $bundle->memberBasePrice * $bundle->earlyBirdPercent / 100), 2, '.', ',') }}
                            @else
                                {{ number_format($bundle->memberBasePrice, 2, '.', ',') }}
                            @endif
                        </td>
                        <td><i class="fa fa-dollar"></i>
                            @if(($bundle->earlyBirdEndDate !== null) && $bundle->earlyBirdEndDate->gt($today))
                                <strike style="color:red;">{{ number_format($bundle->nonmbrBasePrice, 2, '.', ',') }}</strike>
                                <br>
                                <i class="fa fa-dollar"></i>
                                {{ number_format($bundle->nonmbrBasePrice - ( $bundle->nonmbrBasePrice * $bundle->earlyBirdPercent / 100), 2, '.', ',') }}
                            @else
                                {{ number_format($bundle->nonmbrBasePrice, 2, '.', ',') }}
                            @endif
                        </td>
                        <td>{{ $bundle->availabilityEndDate->format('n/j/Y g:i A') }}</td>
                    </tr>
                @endforeach
                @foreach($tickets as $ticket)
                    <tr>
                        <td style="text-align: center;">
                            <input type="radio" name="ticketID" value="{{ $ticket->ticketID }}">
                        </td>
                        <td>{{ $ticket->ticketLabel }}
                            @if($ticket->maxAttendees > 0 && $ticket->regCount >= $ticket->maxAttendees)
                                <br/><b class="red">This ticket is sold out. Add yourself to the wait list.</b>
                            @endif
                        </td>
                        <td><i class="fa fa-dollar"></i>
                            @if(($ticket->earlyBirdEndDate !== null) && $ticket->earlyBirdEndDate->gt($today))
                                <strike style="color:red;">{{ number_format($ticket->memberBasePrice, 2, '.', ',') }}</strike>
                                <br>
                                <i class="fa fa-dollar"></i>
                                {{ number_format($ticket->memberBasePrice - ( $ticket->memberBasePrice * $ticket->earlyBirdPercent / 100), 2, '.', ',') }}
                            @else
                                {{ number_format($ticket->memberBasePrice, 2, '.', ',') }}
                            @endif
                        </td>
                        <td>
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
                        <td>{{ $ticket->availabilityEndDate->format('n/j/Y g:i A') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td></td>
                    <td colspan="4">
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
