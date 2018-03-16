<?php
/**
 * Comment: Event Display with Tabs to see Session Information
 * Created: 5/5/2017
 */

use App\EventSession;
use App\Ticket;
use GrahamCampbell\Flysystem\Facades\Flysystem;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Aws\S3\S3Client;
use League\Flysystem\Filesystem;

$category = DB::table('event-category')->where([
    ['orgID', $event->orgID],
    ['catID', $event->catID]
])->select('catTXT')->first();

// -----------------
// Early Bird-ism
// 1. Get today's date
// 2. Compare to early bird dates per ticket
// 3. Calculate new price and display

$today = Carbon\Carbon::now();

if ($event->isSymmetric) {
    $columns = ($event->hasTracks * 2) + 1;
    $width = (integer)85 / $event->hasTracks;
    $mw = (integer)90 / $event->hasTracks;
} else {
    $columns = $event->hasTracks * 3;
    $width = (integer)80 / $event->hasTracks;
    $mw = (integer)85 / $event->hasTracks;
}

// This is a rejiggering of the order by which tracks/sessions could be displayed.
// I don't think I ever made use of it.
//
if (!$event->isSymmetric) {
    $mda = array('days' => $event->confDays, 'sym' => $event->isSymmetric, 'tracks' => count($tracks));
    for ($d = 1; $d <= $event->confDays; $d++) {
        $t = 0;
        ${'d' . $d} = array();
        foreach ($tracks as $track) {
            $t++;
            ${'t' . $t} = array();
            for ($x = 1; $x <= 5; $x++) {
                $es = EventSession::where([
                    ['trackID', '=', $track->trackID],
                    ['order', '=', $x],
                    ['confDay', '=', $d]
                ])->first();
                if ($es !== null) {
                    ${'t' . $t} = array_add(${'t' . $t}, $x, $es);
                }
            }
            ${'d' . $d} = array_add(${'d' . $d}, 't' . $t, ${'t' . $t});
        }
        $mda = array_add($mda, 'd' . $d, ${'d' . $d});
    }
}

$client = new S3Client([
    'credentials' => [
        'key' => env('AWS_KEY'),
        'secret' => env('AWS_SECRET')
    ],
    'region' => env('AWS_REGION'),
    'version' => 'latest',
]);

$adapter = new AwsS3Adapter($client, env('AWS_BUCKET3'));
$s3fs = new Filesystem($adapter);
$logo = $s3fs->getAdapter()->getClient()->getObjectUrl(env('AWS_BUCKET3'), $orgLogoPath->orgPath . "/" . $orgLogoPath->orgLogo);

$soldout = 0;
?>
@extends('v1.layouts.no-auth')

@if($event->isActive)
@section('content')
    @include('v1.parts.not-table_header')
    <style>
        .popover {
            max-width: 50%;
        }
    </style>
    @include('v1.parts.start_content', ['header' => "$event->eventName", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    @include('v1.parts.start_content', ['header' => 'Event Detail', 'subheader' => '', 'w1' => '9', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    {!! Form::open(['url' => env('APP_URL').'/regstep1/'.$event->eventID, 'method' => 'post', 'id' => 'start_registration']) !!}
    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
        <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">{!! $event->eventDescription !!}</div>
        <div class="form-group has-feedback col-md-12 col-sm-12 col-xs-12">
            Category: {{ $category->catTXT }}</div>

        @if($event->earlyBirdDate !== null && $event->earlyBirdDate->gte($today))
            <div class="col-md-12 col-sm-12 col-xs-12" style="display:flex;">
                <div class="col-md-2 col-sm-2 col-xs-2">
                    <img src="{{ env('APP_URL') }}/images/earlybird.jpg" style="float:right; width:75px;">
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6"
                     style="margin-top: auto; word-break: break-all;">
                    <h2><span style="color:red;">Act Now!</span> Early Bird Pricing in Effect</h2>
                </div>
            </div>
        @endif
        <div class="col-md-12 col-sm-12 col-xs-12">
            <ul id="myTab" class="nav nav-tabs bar_tabs nav-justified" role="tablist">
                <li class="active"><a href="#tab_content1" id="ticketing-tab" data-toggle="tab"
                                      aria-expanded="true"><b>Event Ticketing</b></a></li>
                <li class=""><a href="#tab_content2" id="sessions-tab" data-toggle="tab"
                                aria-expanded="false"><b>Event Sessions</b></a></li>
            </ul>
            <div id="tab-content" class="tab-content">
                <div class="tab-pane active" id="tab_content1" aria-labelledby="ticketing-tab">
                    <br/>

                    <div id="not" class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
                        <table id="datatable" class="table table-striped jambo_table">
                            <thead id="cf">
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
                                        $b_tkts = DB::table('event-tickets')
                                            ->join('bundle-ticket', function ($join) use ($bundle) {
                                                $join->on('bundle-ticket.ticketID', '=', 'event-tickets.ticketID')
                                                    ->where('bundle-ticket.bundleID', '=', $bundle->ticketID);
                                            })->where([
                                                ['event-tickets.eventID', $event->eventID],
                                                ['event-tickets.isaBundle', 0],
                                            ])->select('event-tickets.ticketID', 'event-tickets.ticketLabel', 'bundle-ticket.ticketID',
                                                'event-tickets.maxAttendees', 'event-tickets.regCount')->get();
                                        // $b_tkts = DB::select($sql);
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
                                        <input type="radio" name="ticketID" value="{{ $ticket->ticketID }}">
                                    </td>
                                    <td data-title="Ticket">{{ $ticket->ticketLabel }}
                                        @if($ticket->maxAttendees > 0 && $ticket->regCount >= $ticket->maxAttendees)
                                            <br/><b class="red">This ticket is sold out. Add yourself to the wait
                                                list.</b>
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
                        <div class="col-md-9 col-sm-9 col-xs-6" style="text-align: right"><input
                                    id="discount_code" name="discount_code" type="text"
                                    placeholder="  Enter discount code"/></div>
                        <div class="col-md-3 col-sm-3 col-xs-6"><a class="btn btn-xs btn-primary"
                                                                   id="btn-validate">Validate</a></div>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-12" style="text-align: left; vertical-align: top;">
                        <img alt="Visa Logo" src="{{ env('APP_URL') }}/images/visa.png"><img
                                alt="MasterCard Logo" src="{{ env('APP_URL') }}/images/mastercard.png">
                        <button type="submit" class="btn btn-success btn-sm" id="purchase"
                                style="height: 32px;"><b>Purchase Ticket(s)</b></button>
                    </div>

                    &nbsp;<br/>
                    <SUP style="color: red">*</SUP> Member pricing is applied automatically when you are 1)
                    logged in and 2) have a PMI ID associated with your account.<br/>
                    <SUP style='color: red'>**</SUP>Bundles include multiple tickets so you do not have to
                    manually purchase multiple tickets.

                    @if($errors->all())
                        @include('v1.parts.error')
                    @endif
                </div>
                <div class="tab-pane fade" id="tab_content2" aria-labelledby="sessions-tab">
                    <br/>

                    @if($event->confDays != 0)
                        <div id="not" class="col-sm-12 col-md-12 col-xs-12">

                            <p>Here are this event's {{ count($tracks) }} tracks and its sessions from which attendees
                                can choose to participate.<br/>
                                This does not show any full-group keynotes, lunch or evening sessions.</p>
                            <table class="table table-bordered table-striped table-condensed table-responsive">
                                <thead class="cf">
                                <tr>
                                    @foreach($tracks as $track)

                                        @if($tracks->first() == $track || !$event->isSymmetric)
                                            <th style="text-align:left;">Session Times</th>
                                        @endif
                                        <th colspan="2" style="text-align:center;"> {{ $track->trackName }} </th>
                                    @endforeach
                                </tr>
                                </thead>
                                <tbody>

                                @for($i=1;$i<=$event->confDays;$i++)
                                    <tr>
                                        <?php
                                        $z = EventSession::where([
                                            ['confDay', '=', $i],
                                            ['eventID', '=', $event->eventID]
                                        ])->first();
                                        $y = Ticket::find($z->ticketID);
                                        ?>
                                        <th style="text-align:center; color: yellow; background-color: #2a3f54;"
                                            colspan="{{ $columns }}">Day {{ $i }}:
                                            {{ $y->ticketLabel  }}
                                        </th>
                                    </tr>

                                    @for($x=1;$x<=5;$x++)
                                        <?php
                                        // Check to see if there are any events for $x (this row)
                                        $check = EventSession::where([
                                            ['eventID', $event->eventID],
                                            ['confDay', $i],
                                            ['order', $x]
                                        ])->first();

                                        // As long as there are any sessions, the row will be displayed
                                        ?>
                                        @if($check !== null)
                                            <tr>
                                                @foreach($tracks as $track)
                                                    <?php
                                                    $s = EventSession::where([
                                                        ['trackID', $track->trackID],
                                                        ['eventID', $event->eventID],
                                                        ['confDay', $i],
                                                        ['order', $x]
                                                    ])->first();
                                                    ?>
                                                    @if($s !== null)
                                                        @if($tracks->first() == $track || !$event->isSymmetric)
                                                            <td data-title="Times" rowspan="3" style="text-align:left;">
                                                                <nobr> {{ $s->start->format('g:i A') }} </nobr>
                                                                &dash;
                                                                <nobr> {{ $s->end->format('g:i A') }} </nobr>
                                                            </td>
                                                        @endif
                                                        <td data-title="Session" colspan="2"
                                                            style="text-align:left; min-width:150px;
                                                                    width: {{ $width }}%; max-width: {{ $mw }}%;">
                                                            <b>{{ $s->sessionName }}</b>
                                                            @if($s->sessionAbstract !== null)
                                                            <a tabindex="0" class="btn btn-xs btn-primary pull-right"
                                                               data-html="true" data-toggle="popover"
                                                               data-trigger="focus"
                                                               data-placement="left" title="{!! $s->sessionName !!}"
                                                               data-content="{!! $s->sessionAbstract !!}">Abstract</a><br/>
                                                            @endif
                                                        </td>
                                                    @else
                                                        <td rowspan="3" colspan="{{ $columns }}"
                                                            style="text-align:left;">&nbsp;
                                                        </td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                        @endif

<?php
                                        // Check to see if there are any events for $x (this row)
                                        $check = EventSession::where([
                                            ['eventID', $event->eventID],
                                            ['confDay', $i],
                                            ['order', $x]
                                        ])->first();

                                        // As long as there are any sessions, the row will be displayed
?>
                                        @if($check !== null)
                                            <tr>
                                                @foreach($tracks as $track)
<?php
                                                    $s = EventSession::where([
                                                        ['trackID', $track->trackID],
                                                        ['eventID', $event->eventID],
                                                        ['confDay', $i],
                                                        ['order', $x]
                                                    ])->first();
?>
                                                    @if($s !== null && $s->sessionSpeakers !== null)
                                                        <td data-title="Speaker" colspan="2" style="text-align:left;">
                                                            <b>Session Speaker(s)</b><br/>
                                                            {{ $s->sessionSpeakers or "tbd" }}
                                                        </td>
                                                    @else
                                                        &nbsp;
                                                    @endif
                                                @endforeach
                                            </tr>
                                        @endif

<?php
                                        // Check to see if there are any events for $x (this row)
                                        $check = EventSession::where([
                                            ['eventID', $event->eventID],
                                            ['confDay', $i],
                                            ['order', $x]
                                        ])->first();

                                        // As long as there are any sessions, the row will be displayed
?>
                                        @if($check !== null)
                                            <tr>
                                                @foreach($tracks as $track)
<?php
                                                    $s = EventSession::where([
                                                        ['trackID', $track->trackID],
                                                        ['eventID', $event->eventID],
                                                        ['confDay', $i],
                                                        ['order', $x]
                                                    ])->first();
?>
                                                    @if($s !== null && $s->leadAmt+$s->stratAmt+$s->techAmt > 0 && $s->maxAttendees > 0)
                                                        <td data-title="Credit" style="text-align:left;">
                                                            @if($s->leadAmt > 0)
                                                                <b>{{ $s->leadAmt }}
                                                                    Leadership
                                                                    {{ $s->event->org->creditLabel }}<?php if ($s->leadAmt != 1) {
                                                                        echo('s');
                                                                    } ?></b><br/>
                                                            @endif
                                                            @if($s->stratAmt > 0)
                                                                <b>{{ $s->stratAmt }}
                                                                    Strategy
                                                                    {{ $s->event->org->creditLabel }}<?php if ($s->stratAmt != 1) {
                                                                        echo('s');
                                                                    } ?></b><br/>
                                                            @endif
                                                            @if($s->techAmt > 0)
                                                                <b>{{ $s->leadAmt }}
                                                                    Technical Skills
                                                                    {{ $s->event->org->creditLabel }}<?php if ($s->techAmt != 1) {
                                                                        echo('s');
                                                                    } ?></b><br/>
                                                            @endif
                                                        </td>
                                                        <td data-title="Limit" style="text-align:left;">
                                                            <b> Attendee Limit: </b> {{ $s->maxAttendees == 0 ? 'N/A' : $s->maxAttendees }}
                                                        </td>
                                                    @else

                                                    @endif
                                                @endforeach
                                            </tr>
                                        @endif
                                    @endfor
                                @endfor
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Date &amp; Time', 'subheader' => '', 'w1' => '3', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
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

    @include('v1.parts.start_content', ['header' => 'Location', 'subheader' => '', 'w1' => '3', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <div class="col-md-12 col-sm-12 col-xs-12">
        @if($event_loc->isVirtual)
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

    @include('v1.parts.start_content', ['header' => 'Organizer Information', 'subheader' => '', 'w1' => '3', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
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
        $('a[data-toggle="tab"]').click(function (e) {
            $(this).tab('show');
        });
        // e.target
        //e.relatedTarget // previous active tab
    </script>
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
                        $('#status_msg').html('');
                        $('#status_msg').fadeIn(0);
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
        ;
    </script>
    <script>
        $('[data-toggle="popover"]').popover({
            container: 'body',
            placement: 'top'
        });
    </script>

@endsection
@else
@section('content')
    This event is inactive.
@endsection
@endif
