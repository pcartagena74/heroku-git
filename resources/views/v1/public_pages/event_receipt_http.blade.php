<?php
/**
 * Comment: Template for pages without authentication
 * Created: 2/2/2017
 */
//<script src='https://www.google.com/recaptcha/api.js'>
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        @include('v1.parts.header_meta')
        <title>
            mCentric
        </title>
        {{--
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
            --}}
            <link href="http://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/cerulean/bootstrap.min.css" rel="stylesheet"/>
            {{--
            <script crossorigin="anonymous" defer="" integrity="sha384-d84LGg2pm9KhR4mCAs3N29GQ4OYNy+K+FBHX8WhimHpPm86c839++MDABegrZ3gn" src="https://pro.fontawesome.com/releases/v5.0.13/js/all.js">
            </script>
            <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
            <script crossorigin="anonymous" defer="" integrity="sha384-d84LGg2pm9KhR4mCAs3N29GQ4OYNy+K+FBHX8WhimHpPm86c839++MDABegrZ3gn" src="https://pro.fontawesome.com/releases/v5.0.13/js/all.js">
            </script>
        </link>
    </head>
</html>
--}}
<link crossorigin="anonymous" href="http://pro.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-87DrmpqHRiY8hPLIr7ByqhPIywuSsjuQAfMXAE0sMUpY3BM7nXjf+mLIUSvhDArs" rel="stylesheet"/>
<script crossorigin="anonymous" src="http://kit.fontawesome.com/d28859cec2.js">
</script>
<link href="http://cdnjs.cloudflare.com/ajax/libs/gentelella/1.3.0/css/custom.min.css" rel="stylesheet"/>
<link href="{{ str_replace('https', 'http', env('APP_URL'))}}/css/jumbotron.css" rel="stylesheet"/>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js">
</script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js">
</script>
<!-- Google Tag Manager -->
<script>
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'http://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-K4MGRCX');
</script>
<!-- End Google Tag Manager -->
<body class="nav-md footer_fixed">
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe height="0" src="http://www.googletagmanager.com/ns.html?id=GTM-K4MGRCX" style="display:none;visibility:hidden" width="0">
        </iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
    <nav class="col-md-12 col-sm-12 col-xs-12 navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <div class="col-md-4 col-sm-4 col-xs-12" style="vertical-align: top;">
                <a class="navbar-brand" href="{{ str_replace('https', 'http', env('APP_URL')) }}/">
                    <img alt="m|Centric" src="{{ str_replace('https', 'http', env('APP_URL')) }}/images/mCentric_logo.png" style="height: 25px; vertical-align: top;"/>
                </a>
            </div>
        </div>
    </nav>
    <div class="container body col-md-12 col-sm-12 col-xs-12">
        <div class="main_container bit">
            @include('v1.parts.error')

    @php
/**
 * Comment: Event Receipt
 * Created: 3/26/17 and updated on 10/25/2019
 *
 * Literal COPY of group_receipt.blade.php
 */

use App\RegSession;
use App\EventSession;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use App\Registration;
use App\Person;
use App\Ticket;

$tcount = 0;
$today = Carbon\Carbon::now();

// For discount comparison.  Need to see if $today > ticket->earlyBirdEndDate
// Also need to show the "Early Bird" based on whether it was true when purchased -- BUT also need catch
// the potential continued purchases after the end (or change from At Door to credit but allowing it)
$compareDate = $today;

$string = '';

$allergens = DB::table('allergens')->select('allergen', 'allergen')->get();
$allergen_array = $allergens->pluck('allergen', 'allergen')->toArray();

if($event->eventTypeID == 5){ // This is a regional event so do that instead
    $chapters = DB::table('organization')->where('orgID', $event->orgID)->select('regionChapters')->first();
    $array    = explode(',', $chapters->regionChapters);
} else {
    $chapters = DB::table('organization')->where('orgID', $event->orgID)->select('nearbyChapters')->first();
    $array    = explode(',', $chapters->nearbyChapters);
}

$i = 0;
foreach($array as $chap) {
    $i++; $chap = trim($chap);
    $affiliation_array[$i] = $chap;
}

// must use $etype->etName to get the text embedded here
$etype = DB::table('org-event_types')->where('etID', $event->eventTypeID)->select('etName')->first();
// etName is now the value of $etype post et_translate function
$etype = et_translate($etype->etName);

// Find out how to embed the correct TZ in front of this since not using UTC
$est = $event->eventStartDate->format('Ymd\THis'); // $event->eventTimeZone;
$eet = $event->eventEndDate->format('Ymd\THis'); // $event->eventTimeZone;

$dur = sprintf("%02d", $event->eventEndDate->diffInHours($event->eventStartDate)) . "00";

$event_url = trans('messages.email_txt.for_det_visit') . ": " . env('APP_URL') . "/events/$event->slug";
$yahoo_url =
    "http://calendar.yahoo.com/?v=60&TITLE;=$event->eventName&DESC;=$org->orgName $etype&ST;=$est&DUR;=$dur&URL;=$event_url&in;_loc=$loc->locName&in;_st=$loc->addr1 $loc->addr2&in;_csz=$loc->city, $loc->state $loc->zip";
$google_url =
    "http://www.google.com/calendar/event?action=TEMPLATE&text;=$org->orgName $etype&dates;=$est/$eet&name;=$event->eventName&details;=$event_url&location;=$loc->locName $loc->addr1 $loc->addr2 $loc->city, $loc->state $loc->zip";
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
http://twitter.com/share?text=I%20am%20going%20to%20this%20event%20April+2017+Chapter+Meeting+-+Leading+projects+in+the+digital+age&url;=http://www.myeventguru.com/events/APR2017CM/code,qqu6IrJoPg/type,t/&via;=myeventguru
http://www.facebook.com/dialog/feed?app_id=138870902790834&redirect;_uri=http://www.myeventguru.com/events/APR2017CM/code,SjlMpA8qoY/type,f/&link;=http://www.myeventguru.com/events/APR2017CM/code,SjlMpA8qoY/type,f/&description;=April+2017+Chapter+Meeting+-+Leading+projects+in+the+digital+age&message;=I+am+going+to+this+event.
http://www.linkedin.com/shareArticle?mini=true&url;=https%3A%2F%2Fwww.myeventguru.com%2Fevents%2FAPR2017CM%2Fcode%2CY0YFBmUErN%2Ftype%2Cl%2F&title;=April+2017+Chapter+Meeting+-+Leading+projects+in+the+digital+age&summary;=I+am+going+to+this+event&source;=MyEventGuru
an email url to a form
*/

$header = trans('messages.headers.reg') . " ";
if($rf->pmtRecd){
    $header .= trans('messages.headers.receipt');
} else {
    $header .= trans('messages.headers.invoice');
}
// To track whether there were any parts of the registration canceled/refunded
$deletion = 0;
@endphp


    @include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
            <div class="whole">
                {{--
                <div class="left col-md-7 col-sm-7">
                    --}}
                    <div class="myrow col-md-12 col-sm-12">
                        <div class="col-md-2 col-sm-2" style="text-align:center;">
                            <h1 class="far fa-5x fa-calendar-alt">
                            </h1>
                        </div>
                        <div class="col-md-7 col-sm-7">
                            <h2>
                                <b>
                                    {{ $event->eventName }}
                                </b>
                            </h2>
                            <div style="margin-left: 10px;">
                                {{ $event->eventStartDate->format('n/j/Y g:i A') }}
                    - {{ $event->eventEndDate->format('n/j/Y g:i A') }}
                                <br>
                                    {{ $loc->locName }}
                                    <br>
                                        {{ $loc->addr1 }}
                                        <i class="fas fa-circle fa-xs">
                                        </i>
                                        {{ $loc->city }},
                    {{ $loc->state }} {{ $loc->zip }}
                                    </br>
                                </br>
                            </div>
                            <br/>
                            <b style="color:red;">
                                @lang('messages.headers.purchased'):
                            </b>
                            {{ $rf->createDate->format('n/j/Y') }}
                            <b style="color:red;">
                                @lang('messages.headers.at')
                            </b>
                            {{ $rf->createDate->format('g:i A') }}
                @if($rf->cost > 0 && $rf->pmtRecd == 0)
                            <h1 style="color:red;">
                                @lang('messages.headers.bal_due')
                            </h1>
                            @endif
                        </div>
                        <div class="col-md-3 col-sm-3">
                        </div>
                    </div>
                    @foreach($rf->registrations as $reg)
        @php
            $tcount++;
            $person = Person::find($reg->personID);
            $ticket = Ticket::find($reg->ticketID);
            @endphp
                    <div class="myrow col-md-12 col-sm-12">
                        <div class="col-md-2 col-sm-2" style="text-align:center;">
                            @if($reg->deleted_at)
                            <h1 class="fas fa-5x fa-user red">
                            </h1>
                            @else
                            <h1 class="fas fa-5x fa-user">
                            </h1>
                            @endif
                        </div>
                        <div class="col-md-10 col-sm-10">
                            <table class="table jambo_table table-bordered table-condensed table-striped">
                                <tr>
                                    <thead>
                                        <th colspan="4" style="text-align: left;">
                                            {{ strtoupper($reg->membership) }}
                                {{ strtoupper(__('messages.fields.ticket')) }}:
                                #{{ $tcount }}
                                @if($reg->deleted_at)
                                            <span class="yellow">
                                                @if($reg->subtotal > 0)
                                            {{ strtoupper(__('messages.headers.refunded')) }}
                                        @else
                                            {{ strtoupper(__('messages.headers.canceled')) }}
                                        @endif
                                            </span>
                                            @endif
                                            <span style="float: right;">
                                                @lang('messages.fields.reg_id')#:
                                    @if($reg->deleted_at)
                                        {{ $reg->regID }}
                                    @else
                                                <span style="color:yellow;">
                                                    {{ $reg->regID }}
                                                </span>
                                                @endif
                                            </span>
                                        </th>
                                    </thead>
                                </tr>
                                <tr>
                                    <th style="text-align: left; color:darkgreen;">
                                        @lang('messages.fields.ticket')
                                    </th>
                                    <th style="text-align: left; color:darkgreen;">
                                        @lang('messages.fields.oCost')
                                    </th>
                                    <th style="text-align: left; color:darkgreen;">
                                        {{ trans_choice('messages.fields.disc', 2) }}
                                    </th>
                                    <th style="text-align: left; color:darkgreen;">
                                        @lang('messages.fields.subtotal')
                                    </th>
                                </tr>
                                <tr>
                                    <td style="text-align: left;">
                                        {{ $ticket->ticketLabel }}
                                    </td>
                                    <td style="text-align: left;">
                                        <i class="far fa-dollar-sign">
                                        </i>
                                        @if($reg->membership == 'Member')
                                    {{ number_format($ticket->memberBasePrice, 2, ".", ",") }}
                                @else
                                    {{ number_format($ticket->nonmbrBasePrice, 2, ".", ",") }}
                                @endif
                                    </td>
                                    @if(($ticket->earlyBirdEndDate !== null) && $ticket->earlyBirdEndDate->gt($compareDate))
                                @if($reg->discountCode)
                                    <td style="text-align: left;">
                                        @lang('messages.headers.earlybird'), {{ $reg->discountCode }}
                                    </td>
                                    @else
                                    <td style="text-align: left;">
                                        @lang('messages.headers.earlybird')
                                    </td>
                                    @endif
                            @else
                                @if($reg->discountCode)
                                    <td style="text-align: left;">
                                        {{ $reg->discountCode }}
                                    </td>
                                    @else
                                    <td style="text-align: left;">
                                        --
                                    </td>
                                    @endif
                            @endif
                                    <td style="text-align: left;">
                                        <i class="far fa-dollar-sign">
                                        </i>
                                        {{ number_format($reg->subtotal, 2, ".", ",") }}
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2" style="width: 50%; text-align: left;">
                                        @lang('messages.headers.att_info')
                                    </th>
                                    <th colspan="2" style="width: 50%; text-align: left;">
                                        @lang('messages.headers.event_info')
                                    </th>
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
                                        <nobr>
                                            [ {{ $person->login }} ]
                                        </nobr>
                                        @if($person->orgperson->OrgStat1)
                                        <br/>
                                        {{ $org->OSN1 }}: {{ $person->orgperson->OrgStat1 }}
                                    @endif
                                        <br/>
                                        @if($person->compName)
                                    @if($person->title)
                                        {{ $person->title }}
                                    @else
                                        @lang('messages.headers.employed')
                                    @endif
                                    @lang('messages.headers.at') {{ $person->compName }}
                                @else
                                    @if($person->title !== null)
                                        {{ $person->title }}
                                    @elseif($person->indName !== null)
                                        @lang('messages.headers.employed')
                                    @endif
                                @endif
                                @if($person->indName !== null)
                                    @lang('messages.headers.inthe') {{ $person->indName }} @lang('messages.headers.ind')
                                        <br/>
                                        @endif

                                @if($person->affiliation)
                                        <br/>
                                        @lang('messages.headers.aff_with'): {{ $person->affiliation }}
                                @endif
                                    </td>
                                    <td colspan="2" style="text-align: left;">
                                        <b>
                                            @lang('messages.headers.roster_add'):
                                        </b>
                                        {{ $reg->canNetwork
                                                                            ? trans('messages.yesno_check.yes')
                                                                            : trans('messages.yesno_check.no') }}
                                        <br/>
                                        <b>
                                            @lang('messages.headers.certs'):
                                        </b>
                                        : {{ $person->certifications }}
                                        <br/>
                                        <b>
                                            @lang('messages.fields.pdu_sub'):
                                        </b>
                                        {{ $reg->isAuthPDU
                                                                            ? trans('messages.yesno_check.yes')
                                                                            : trans('messages.yesno_check.no') }}
                                        <br/>
                                        @if($reg->allergenInfo)
                                        <b>
                                            @lang('messages.fields.diet_info'):
                                        </b>
                                        {{ $reg->allergenInfo }}
                                        <br/>
                                        @if($reg->eventNotes)
                                        {{ $reg->eventNotes }}
                                        <br/>
                                        @endif
                                @endif
                                    </td>
                                </tr>
                            </table>
                            @if($reg->ticket->has_sessions())
                        @include('v1.parts.session_print', ['event' => $rf->event, 'ticket' => $reg->ticket, 'rf' => $rf, 'reg' => $reg])
                    @endif
                        </div>
                    </div>
                    @endforeach
                    <div class="myrow col-md-12 col-sm-12">
                        <div class="col-md-2 col-sm-2" style="text-align:center;">
                            <h1 class="far fa-5x fa-dollar-sign">
                            </h1>
                        </div>
                        <div class="col-md-7 col-sm-7">
                            @if($deletion)
                            <p class="red">
                                <b>
                                    @lang('messages.headers.note'):
                                </b>
                                @lang('messages.instructions.total_caveat')
                            </p>
                            @else
                     
                @endif
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <table class="table table-striped table-condensed jambo_table">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;">
                                            @lang('messages.fields.total')
                                        </th>
                                    </tr>
                                </thead>
                                <tr>
                                    <td style="text-align: center;">
                                        <b>
                                            <i class="far fa-dollar-sign">
                                            </i>
                                            {{ number_format($rf->cost, 2, '.', ',') }}
                                        </b>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <hr>
                        @if($event->postRegInfo)
                        <div class="col-sm-2">
                        </div>
                        <div class="col-sm-10">
                            @include('v1.parts.start_content', ['header' => trans('messages.fields.additional'), 'subheader' => '',
                         'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
                {!! $event->postRegInfo ?? '' !!}
                @include('v1.parts.end_content')
                        </div>
                        @endif
                        <hr>
                            <div class="col-sm-12">
                                <h4>
                                    @lang('messages.headers.add_to_cal')
                                </h4>
                                @if(0)
                                <table class="table borderless">
                                    <tr>
                                        <td style="text-align: center;">
                                            <a href="{{ $ics }}" target="_new">
                                                <h2 class="fal fa-4x fa-calendar-alt" style="color: gold;">
                                                </h2>
                                            </a>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="{{ $google_url }}" target="_new">
                                                <span class="far fa-stack fa-lg fa-2x">
                                                    <h1 class="far fa-4x fa-square fa-stack-2x" style="color: red;">
                                                    </h1>
                                                    <h1 class="far fa-1x fa-google fa-stack-1x" style="color: white;">
                                                    </h1>
                                                </span>
                                            </a>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="{{ $yahoo_url }}" target="_new">
                                                <span class="far fa-stack fa-lg fa-2x">
                                                    <h1 class="far fa-3x fa-square fa-stack-2x" style="color: rebeccapurple;">
                                                    </h1>
                                                    <h1 class="far fa-1x fa-yahoo fa-stack-1x" style="color: white;">
                                                    </h1>
                                                </span>
                                            </a>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="{{ $ics }}" target="_new">
                                                <span class="far fa-stack fa-lg fa-2x">
                                                    <h2 class="fal fa-3x fa-calendar-alt fa-stack-2x" style="color: red;">
                                                    </h2>
                                                    <h2 class="fa-stack-1x" style="text-align: center; color: black; margin-top:1em;">
                                                        ICS
                                                    </h2>
                                                </span>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                                @else
                                <table class="table borderless">
                                    <tr valign="middle">
                                        <td style="text-align: center;">
                                            <a href="{{ $ics }}" target="_new">
                                                <img height="50" src="{{ str_replace('https', 'http', env('APP_URL')) }}/images/outlook.jpg" width="50">
                                                </img>
                                            </a>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="{{ $google_url }}" target="_new">
                                                <img height="50" src="{{ str_replace('https', 'http', env('APP_URL')) }}/images/google.jpg" width="50">
                                                </img>
                                            </a>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="{{ $yahoo_url }}" target="_new">
                                                <img height="50" src="{{ str_replace('https', 'http', env('APP_URL')) }}/images/yahoo.jpg" width="50">
                                                </img>
                                            </a>
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="{{ $ics }}" target="_new">
                                                <img height="50" src="{{ str_replace('https', 'http', env('APP_URL')) }}/images/ical.jpg" width="50">
                                                </img>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                                @endif
                            </div>
                        </hr>
                    </hr>
                </div>
                @include('v1.parts.end_content')
            </div>
            {{--
    @include('v1.parts.footer_script')
--}}
            <p>
            </p>
            <footer>
                <div class="pull-right">
                    @if(!Auth::check())
                    <a href="{{ env('APP_URL') }}">
                        <img alt="mCentric" src="{{ str_replace('https', 'http', env('APP_URL')) }}/images/mCentric_logo_blue.png" style="height: 25px;"/>
                    </a>
                    @else
                    <img alt="mCentric" hspace="50" src="{{ str_replace('https', 'http', env('APP_URL')) }}/images/mCentric_logo_blue.png" style="height: 25px;"/>
                    @endif
                </div>
            </footer>
        </div>
    </div>
</body>
