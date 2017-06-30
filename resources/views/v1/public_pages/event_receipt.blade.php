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

// $rf, $loc, $event, $org
$ticketLabel = $rf->registration->ticket->ticketLabel;

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

$adapter = new AwsS3Adapter($client, env('AWS_BUCKET'));
$s3fs = new Filesystem($adapter);
$ics = $s3fs->getAdapter()->getClient()->getObjectUrl(env('AWS_BUCKET'), $event_filename);

/* Links to share
http://twitter.com/share?text=I%20am%20going%20to%20this%20event%20April+2017+Chapter+Meeting+-+Leading+projects+in+the+digital+age&url=https://www.myeventguru.com/events/APR2017CM/code,qqu6IrJoPg/type,t/&via=myeventguru
http://www.facebook.com/dialog/feed?app_id=138870902790834&redirect_uri=https://www.myeventguru.com/events/APR2017CM/code,SjlMpA8qoY/type,f/&link=https://www.myeventguru.com/events/APR2017CM/code,SjlMpA8qoY/type,f/&description=April+2017+Chapter+Meeting+-+Leading+projects+in+the+digital+age&message=I+am+going+to+this+event.
http://www.linkedin.com/shareArticle?mini=true&url=https%3A%2F%2Fwww.myeventguru.com%2Fevents%2FAPR2017CM%2Fcode%2CY0YFBmUErN%2Ftype%2Cl%2F&title=April+2017+Chapter+Meeting+-+Leading+projects+in+the+digital+age&summary=I+am+going+to+this+event&source=MyEventGuru
an email url to a form
*/

?>
@extends('v1.layouts.no-auth')

@section('content')
    @include('v1.parts.start_content', ['header' => "Registration Receipt: $ticketLabel", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <div class="whole">

        <div style="float: right;" class="col-md-5 col-sm-5">
            <img style="opacity: .25;" src="/images/meeting.jpg" width="100%" height="90%">
        </div>
        <div class="left col-md-7 col-sm-7">
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
                    <b style="color:red;">Purchased on: </b> {{ $rf->createDate->format('n/j/Y') }} <b style="color:red;">at </b> {{ $rf->createDate->format('g:i A') }}
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
                                    #{{ $tcount }}</th>
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
                                    @if($rf->discountCode)
                                        <td style="text-align: left;">Early Bird, {{ $rf->discountCode }}</td>
                                    @else
                                        <td style="text-align: left;">Early Bird</td>
                                    @endif
                                @else
                                    @if($rf->discountCode)
                                        <td style="text-align: left;">{{ $rf->discountCode }}</td>
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
                                    @if($reg->isFirstEvent)
                                        <b>First Event?</b> {{ $reg->isFirstEvent ? 'Yes' : 'No' }}<br/>
                                    @endif

                                    <b>Add to Roster:</b> {{ $reg->canNetwork ? 'Yes' : 'No' }}<br/>
                                    <b>PDU Submission:</b> {{ $reg->isAuthPDU ? 'Yes' : 'No' }}<br/>
                                    @if($reg->eventQuestion)
                                        <p><b>Speaker Questions:</b> {{ $reg->eventQuestion }}</p>
                                    @endif

                                    @if($reg->eventTopics)
                                        <p><b>Future Topics:</b><br/> {{ $reg->eventTopics }}</p>
                                    @endif

                                    @if($reg->cityState)
                                        <b>Commuting From:</b> {{ $reg->cityState }}<br/>
                                    @endif

                                    @if($reg->specialNeeds)
                                        <b>Special Needs:</b> {{ $reg->specialNeeds }} <br/>
                                    @endif

                                    @if($reg->allergenInfo)
                                        <b>Dietary Info:</b> {{ $reg->allergenInfo }}<br/>
                                        @if($reg->eventNotes)
                                            {{ $reg->eventNotes }}<br/>
                                        @endif
                                    @elseif($reg->eventNotes)
                                        <b>Other Comments/Notes:</b> {{ $reg->eventNotes }}<br/>
                                    @endif

                                </td>
                            </tr>
                        </table>

                        @if($event->hasTracks > 0 && $needSessionPick == 1)
                            <table class="table table-bordered jambo_table table-striped">
                                <thead>
                                <tr>
                                    <th colspan="2" style="text-align: left;">
                                        Track Selection
                                    </th>
                                </tr>
                                </thead>
                                <tr>
                                    <th style="text-align:left;">Session Times</th>
                                    <th style="text-align:left;"> Selected Session</th>
                                </tr>
                                @for($j=1;$j<=$event->confDays;$j++)
                                    <?php
                                    $rs = RegSession::where([
                                        ['confDay', '=', $j],
                                        ['regID', '=', $reg->regID],
                                        ['personID', '=', $reg->personID],
                                        ['eventID', '=', $event->eventID]
                                    ])->orderBy('id')->get();
                                    ?>

                                    @foreach($rs as $z)
                                        @if($rs->first() == $z)
                                            <?php
                                            $s = EventSession::find($z->sessionID);
                                            $y = Ticket::find($s->ticketID);
                                            ?>
                                            <tr>
                                                <th style="text-align:center; color: yellow; background-color: #2a3f54;"
                                                    colspan="2">Day {{ $j }}:
                                                    {{ $y->ticketLabel  }}
                                                </th>
                                            </tr>
                                        @endif
                                        <?php
                                        $s = EventSession::with('track')->where('sessionID', $z->sessionID)->first();
                                        ?>
                                        <tr>
                                            <td rowspan="1" style="text-align:left; width:33%;">
                                                <nobr> {{ $s->start->format('g:i A') }} </nobr>
                                                &dash;
                                                <nobr> {{ $s->end->format('g:i A') }} </nobr>
                                            </td>
                                            <td colspan="1" style="text-align:left; min-width:150px; width: 67%;">
                                                <b>{{ $s->track->trackName }}</b><br />
                                                {{ $s->sessionName }} <br/>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endfor
                            </table>

                        @endif
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

            @if(1)
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
            @endif

        </div>
    </div>

    {{-- add links to ical, etc. --}}
    @include('v1.parts.end_content')
@endsection


