<?php
/**
 * Comment: Event Receipt
 * Created: 3/26/2017
 */

use App\RegSession;
use App\EventSession;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use App\Registration;
use App\Person;
use App\Ticket;

$today = Carbon\Carbon::now();

$tcount = 0;
$today = Carbon\Carbon::now();
$string = '';

$allergens = DB::table('allergens')->select('allergen', 'allergen')->get();
$allergen_array = $allergens->pluck('allergen', 'allergen')->toArray();

$chapters = DB::table('organization')->where('orgID', $event->orgID)->select('nearbyChapters')->first();
$array = explode(',', $chapters->nearbyChapters);

$i = 0;
foreach($array as $chap) {
    $i++;
    $affiliation_array[$i] = $chap;
}

// must use $etype->etName to get the text embedded here
$etype = DB::table('org-event_types')->where('etID', $event->eventTypeID)->select('etName')->first();

// Find out how to embed the correct TZ in front of this since not using UTC
$est = $event->eventStartDate->format('Ymd\THis'); // $event->eventTimeZone;
$eet = $event->eventEndDate->format('Ymd\THis'); // $event->eventTimeZone;

$dur = sprintf("%02d", $event->eventEndDate->diffInHours($event->eventStartDate)) . "00";

$event_url = "For details, visit: " . env('APP_URL') . "/events/$event->slug";
$yahoo_url =
    "https://calendar.yahoo.com/?v=60&TITLE=$event->eventName&DESC=$org->orgName $etype->etName&ST=$est&DUR=$dur&URL=$event_url&in_loc=$loc->locName&in_st=$loc->addr1 $loc->addr2&in_csz=$loc->city, $loc->state $loc->zip";
$google_url =
    "https://www.google.com/calendar/event?action=TEMPLATE&text=$org->orgName $etype->etName&dates=$est/$eet&name=$event->eventName&details=$event_url&location=$loc->locName $loc->addr1 $loc->addr2 $loc->city, $loc->state $loc->zip";
$event_filename = 'event_' . $event->eventID . '.ics';

$client = new S3Client([
    'credentials' => [
        'key'    => env('AWS_KEY'),
        'secret' => env('AWS_SECRET')
    ],
    'region' => env('AWS_REGION'),
    'version' => 'latest',
]);

$adapter = new AwsS3Adapter($client, env('AWS_BUCKET1'));
$s3fs = new Filesystem($adapter);
$ics = $s3fs->getAdapter()->getClient()->getObjectUrl(env('AWS_BUCKET1'), $event_filename);

/* Links to share
http://twitter.com/share?text=I%20am%20going%20to%20this%20event%20April+2017+Chapter+Meeting+-+Leading+projects+in+the+digital+age&url=https://www.myeventguru.com/events/APR2017CM/code,qqu6IrJoPg/type,t/&via=myeventguru
http://www.facebook.com/dialog/feed?app_id=138870902790834&redirect_uri=https://www.myeventguru.com/events/APR2017CM/code,SjlMpA8qoY/type,f/&link=https://www.myeventguru.com/events/APR2017CM/code,SjlMpA8qoY/type,f/&description=April+2017+Chapter+Meeting+-+Leading+projects+in+the+digital+age&message=I+am+going+to+this+event.
http://www.linkedin.com/shareArticle?mini=true&url=https%3A%2F%2Fwww.myeventguru.com%2Fevents%2FAPR2017CM%2Fcode%2CY0YFBmUErN%2Ftype%2Cl%2F&title=April+2017+Chapter+Meeting+-+Leading+projects+in+the+digital+age&summary=I+am+going+to+this+event&source=MyEventGuru
an email url to a form
*/

if($rf->pmtRecd){
    $header = "Group Registration Receipt";
} else {
    $header = "Group Registration Invoice";
}
?>
@extends('v1.layouts.no-auth_simple')

@section('content')
    @include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <div class="whole">

{{--        <div class="left col-md-7 col-sm-7">            --}}
            <div class="myrow col-md-12 col-sm-12">
                <div class="col-md-2 col-sm-2" style="text-align:center;">
                    <h1 class="fa fa-5x fa-calendar"></h1>
                </div>
                <div class="col-md-7 col-sm-7">
                    <h2><b>{{ $event->eventName }}</b></h2>
                    <div style="margin-left: 10px;">
                        {{ $event->eventStartDate->format('n/j/Y g:i A') }}
                        - {{ $event->eventEndDate->format('n/j/Y g:i A') }}
                        <br>
                        {{ $loc->locName }}<br>
                        {{ $loc->addr1 }} <i class="fa fa-circle fa-tiny-circle"></i> {{ $loc->city }},
                        {{ $loc->state }} {{ $loc->zip }}
                    </div>
                    <br/>
                    <b style="color:red;">Purchased on: </b> {{ $rf->createDate->format('n/j/Y') }}
                    <b style="color:red;">at </b> {{ $rf->createDate->format('g:i A') }}
                    @if($rf->cost > 0 && $rf->pmtRecd == 0)
                        <h1 style="color:red;">Balance Due at Event</h1>
                    @endif
                </div>
                <div class="col-md-3 col-sm-3">
                </div>
            </div>

            @for($i=$rf->regID-($rf->seats-1);$i<=$rf->regID;$i++)
<?php
                $reg = Registration::find($i); $tcount++;
                $person = Person::find($reg->personID);
                $ticket = Ticket::find($reg->ticketID);
?>

                <div class="myrow col-md-12 col-sm-12">
                    <div class="col-md-2 col-sm-2" style="text-align:center;">
                        <h1 class="fa fa-5x fa-user"></h1>
                    </div>
                    <div class="col-md-10 col-sm-10">
                        <table class="table table-bordered table-condensed table-striped">
                            <tr>
                                <th colspan="4" style="text-align: left;">{{ strtoupper($reg->membership) }} TICKET:
                                    #{{ $tcount }}

                                    <span style="float: right;">Registration ID#: <span style="color:red;">{{ $reg->regID }}</span></span>
                                </th>
                            </tr>
                            <tr>
                                <th style="text-align: left; color:darkgreen;">Ticket</th>
                                <th style="text-align: left; color:darkgreen;">Original Cost</th>
                                <th style="text-align: left; color:darkgreen;">Discounts</th>
                                <th style="text-align: left; color:darkgreen;">Subtotal</th>
                            </tr>
                            <tr>
                                <td style="text-align: left;">{{ $ticket->ticketLabel }}</td>

                                <td style="text-align: left;"><i class="fa fa-dollar"></i>
                                    @if($reg->membership == 'Member')
                                        {{ number_format($ticket->memberBasePrice, 2, ".", ",") }}
                                    @else
                                        {{ number_format($ticket->nonmbrBasePrice, 2, ".", ",") }}
                                    @endif
                                </td>

                                @if(($ticket->earlyBirdEndDate !== null) && $ticket->earlyBirdEndDate->gt($today))
                                    @if($reg->discountCode)
                                        <td style="text-align: left;">Early Bird, {{ $reg->discountCode }}</td>
                                    @else
                                        <td style="text-align: left;">Early Bird</td>
                                    @endif
                                @else
                                    @if($reg->discountCode)
                                        <td style="text-align: left;">{{ $reg->discountCode }}</td>
                                    @else
                                        <td style="text-align: left;"> --</td>
                                    @endif
                                @endif
                                <td style="text-align: left;"><i class="fa fa-dollar"></i>
                                    {{ number_format($reg->subtotal, 2, ".", ",") }}
                                </td>
                            </tr>
                            <tr>
                                <th colspan="2" style="width: 50%; text-align: left;">Attendee Info</th>
                                <th colspan="2" style="width: 50%; text-align: left;">Event-Specific Info</th>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align: left;">
                                    @if($person->prefix)
                                        {{ $person->prefix }}
                                    @endif
                                    {{ $person->firstName }}
                                    @if($person->prefName)
                                        ({{ $person->prefName }})
                                    @endif
                                    @if($person->midName)
                                        {{ $person->midName }}
                                    @endif
                                    {{ $person->lastName }}
                                    @if($person->suffix)
                                        {{ $person->suffix }}
                                    @endif
                                    <nobr>[ {{ $person->login }} ]</nobr>
                                    <br/>
                                    @if($person->compName)
                                        @if($person->title)
                                            {{ $person->title }}
                                        @else
                                            Employed
                                        @endif
                                        at {{ $person->compName }}
                                    @else
                                        @if($person->title !== null)
                                            {{ $person->title }}
                                        @elseif($person->indName !== null)
                                            Employed
                                        @endif
                                    @endif
                                    @if($person->indName !== null)
                                        in the {{ $person->indName }} industry <br/>
                                    @endif

                                    @if($person->affiliation)
                                        <br/>Affiliated with: {{ $person->affiliation }}
                                    @endif
                                </td>
                                <td colspan="2" style="text-align: left;">

                                    <b>Add to Roster:</b> {{ $reg->canNetwork ? 'Yes' : 'No' }}<br/>
                                    <b>PDU Submission:</b> {{ $reg->isAuthPDU ? 'Yes' : 'No' }}<br/>

                                    @if($reg->allergenInfo)
                                        <b>Dietary Info:</b> {{ $reg->allergenInfo }}<br/>
                                        @if($reg->eventNotes)
                                            {{ $reg->eventNotes }}<br/>
                                        @endif
                                    @endif

                                </td>
                            </tr>
                        </table>

                    </div>

                </div>

            @endfor

            <div class="myrow col-md-12 col-sm-12">
                <div class="col-md-2 col-sm-2" style="text-align:center;">
                    <h1 class="fa fa-5x fa-dollar"></h1>
                </div>
                <div class="col-md-7 col-sm-7">
                    <p></p>
                </div>
                <div class="col-md-3 col-sm-3">
                    <table class="table table-striped table-condensed jambo_table">
                        <thead>
                        <tr>
                            <th style="text-align: center;">Total</th>
                        </tr>
                        </thead>
                        <tr>
                            <td style="text-align: center;"><b><i
                                            class="fa fa-dollar"></i> {{ number_format($rf->cost, 2, '.', ',') }}</b>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if(0)
                <table class="table borderless">
                    <tr>
                        <td style="text-align: center;"><a target="_new" href="{{ $ics }}">
                                <h2 style="color: gold;" class="fa fa-4x fa-calendar"></h2></a></td>
                        <td style="text-align: center;"><a target="_new" href="{{ $google_url }}">
                                <span class="fa fa-stack fa-lg fa-2x">
                                <h1 style="color: red;" class="fa fa-4x fa-square fa-stack-2x"></h1>
                                <h1 style="color: white;" class="fa fa-1x fa-google fa-stack-1x"></h1>
                                </span>
                            </a></td>
                        <td style="text-align: center;"><a target="_new" href="{{ $yahoo_url }}">
                                <span class="fa fa-stack fa-lg fa-2x">
                                <h1 style="color: rebeccapurple;" class="fa fa-3x fa-square fa-stack-2x"></h1>
                                <h1 style="color: white;" class="fa fa-1x fa-yahoo fa-stack-1x"></h1>
                                </span>
                            </a></td>
                        <td style="text-align: center;"><a target="_new" href="{{ $ics }}">
                                <span class="fa fa-stack fa-lg fa-2x">
                                <h2 style="color: red;" class="fa fa-3x fa-calendar-o fa-stack-2x"></h2>
                                <h2 class="fa-stack-1x" style="text-align: center; color: black; margin-top:1em;">ICS</h2>
                                </span>
                            </a></td>
                    </tr>
                </table>
            @else
                <table class="table borderless">
                    <tr valign="middle">
                        <td style="text-align: center;">
                            <a target="_new" href="{{ $ics }}">
                                <img height="50" width="50" src="{{ env('APP_URL') }}/images/outlook.jpg">
                            </a>
                        </td>
                        <td style="text-align: center;">
                            <a target="_new" href="{{ $google_url }}">
                                <img height="50" width="50" src="{{ env('APP_URL') }}/images/google.jpg">
                            </a>
                        </td>
                        <td style="text-align: center;">
                            <a target="_new" href="{{ $yahoo_url }}">
                                <img height="50" width="50" src="{{ env('APP_URL') }}/images/yahoo.jpg">
                            </a>
                        </td>
                        <td style="text-align: center;">
                            <a target="_new" href="{{ $ics }}">
                                <img height="50" width="50" src="{{ env('APP_URL') }}/images/ical.jpg">
                            </a>
                        </td>

                    </tr>
                </table>
            @endif

{{--        </div>  --}}
    </div>

    {{-- add links to ical, etc. --}}
    @include('v1.parts.end_content')
@endsection
