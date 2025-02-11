@php
    /**
     * Comment: Event Receipt
     * Created: 3/26/17 and updated on 10/25/2019
     *
     * Literal COPY of group_receipt.blade.php
     * @var $rf: regFinance object
     * @var $event: event object
     */

    use App\Models\RegSession;
    use App\Models\EventSession;
    use League\Flysystem\Filesystem;
    use App\Models\Registration;
    use App\Models\Person;
    use App\Models\Ticket;

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

    $dur = sprintf("%02d", (int) $event->eventEndDate->diffInHours($event->eventStartDate)) . "00";

    $event_url = trans('messages.email_txt.for_det_visit') . ": " . env('APP_URL') . "/events/$event->slug";
    $yahoo_url =
        "https://calendar.yahoo.com/?v=60&TITLE=$event->eventName&DESC=$org->orgName $etype&ST=$est&DUR=$dur&URL=$event_url&in_loc=$loc->locName&in_st=$loc->addr1 $loc->addr2&in_csz=$loc->city, $loc->state $loc->zip";
    $google_url =
        "https://www.google.com/calendar/event?action=TEMPLATE&text=$org->orgName $etype&dates=$est/$eet&name=$event->eventName&details=$event_url&location=$loc->locName $loc->addr1 $loc->addr2 $loc->city, $loc->state $loc->zip";
    $event_filename = 'event_' . $event->eventID . '.ics';

    try {
        if(Storage::disk('events')->exists($event_filename)){
            $ics = Storage::disk('events')->url($event_filename);
        }
    } catch(Exception $e) {
        $ics = '';
    }

    /* Links to share
    http://twitter.com/share?text=I%20am%20going%20to%20this%20event%20April+2017+Chapter+Meeting+-+Leading+projects+in+the+digital+age&url=http://www.myeventguru.com/events/APR2017CM/code,qqu6IrJoPg/type,t/&via=myeventguru
    http://www.facebook.com/dialog/feed?app_id=138870902790834&redirect_uri=http://www.myeventguru.com/events/APR2017CM/code,SjlMpA8qoY/type,f/&link=http://www.myeventguru.com/events/APR2017CM/code,SjlMpA8qoY/type,f/&description=April+2017+Chapter+Meeting+-+Leading+projects+in+the+digital+age&message=I+am+going+to+this+event.
    http://www.linkedin.com/shareArticle?mini=true&url=https%3A%2F%2Fwww.myeventguru.com%2Fevents%2FAPR2017CM%2Fcode%2CY0YFBmUErN%2Ftype%2Cl%2F&title=April+2017+Chapter+Meeting+-+Leading+projects+in+the+digital+age&summary=I+am+going+to+this+event&source=MyEventGuru
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

@extends('v1.layouts.no-auth_simple')

@section('content')
    @include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <div class="whole">

        {{--        <div class="left col-md-7 col-sm-7">            --}}
        <div class="myrow col-md-12 col-sm-12">
            <div class="col-md-2 col-sm-2" style="text-align:center;">
                <h1 class="far fa-5x fa-calendar-alt"></h1>
            </div>
            <div class="col-md-7 col-sm-7">
                <h2><b>{{ $event->eventName }}</b></h2>
                <div style="margin-left: 10px;">
                    {{ $event->eventStartDate->format('n/j/Y g:i A') }}
                    - {{ $event->eventEndDate->format('n/j/Y g:i A') }}
                    <br>
                    {{ $loc->locName }}<br>
                    {{ $loc->addr1 }} <i class="fas fa-circle fa-xs"></i> {{ $loc->city }},
                    {{ $loc->state }} {{ $loc->zip }}
                </div>
                <br/>
                <b style="color:red;">@lang('messages.headers.purchased'): </b> {{ $rf->createDate->format('n/j/Y') }}
                <b style="color:red;">@lang('messages.headers.at') </b> {{ $rf->createDate->format('g:i A') }}
                @if($rf->cost > 0 && $rf->pmtRecd == 0)
                    <h1 style="color:red;">@lang('messages.headers.bal_due')</h1>
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
                        <h1 class="fas fa-5x fa-user red"></h1>
                    @else
                        <h1 class="fas fa-5x fa-user"></h1>
                    @endif
                </div>
                <div class="col-md-10 col-sm-10">
                    <table class="table jambo_table table-bordered table-condensed table-striped">
                        <tr>
                            <thead>
                            <th colspan="4" style="text-align: left;">{{ strtoupper($reg->membership) }}
                                {{ strtoupper(__('messages.fields.ticket')) }}:
                                #{{ $tcount }}
                                @if($reg->deleted_at)
                                    &nbsp; &nbsp; &nbsp; &nbsp;
                                    <span class="yellow">
                                    @if($reg->subtotal > 0)
                                            {{ strtoupper(__('messages.headers.refunded')) }}
                                        @else
                                            {{ strtoupper(__('messages.headers.canceled')) }}
                                        @endif
                                </span>
                                @endif

                                <span style="float: right;">@lang('messages.fields.reg_id')#:
                                    @if($reg->deleted_at)
                                        {{ $reg->regID }}
                                    @else
                                        <span style="color:yellow;">{{ $reg->regID }}</span>
                                    @endif
                            </span>
                            </th>
                            </thead>
                        </tr>
                        <tr>
                            <th style="text-align: left; color:darkgreen;">@lang('messages.fields.ticket')</th>
                            <th style="text-align: left; color:darkgreen;">@lang('messages.fields.oCost')</th>
                            <th style="text-align: left; color:darkgreen;">{{ trans_choice('messages.fields.disc', 2) }}</th>
                            <th style="text-align: left; color:darkgreen;">@lang('messages.fields.subtotal')</th>
                        </tr>
                        <tr>
                            <td style="text-align: left;">{{ $ticket->ticketLabel }}</td>

                            <td style="text-align: left;"><i class="far fa-dollar-sign"></i>
                                @if($reg->membership == 'Member')
                                    {{ number_format($ticket->memberBasePrice, 2, ".", ",") }}
                                @else
                                    {{ number_format($ticket->nonmbrBasePrice, 2, ".", ",") }}
                                @endif
                            </td>

                            @if(($ticket->earlyBirdEndDate !== null) && $ticket->earlyBirdEndDate->gt($compareDate))
                                @if($reg->discountCode)
                                    <td style="text-align: left;">@lang('messages.headers.earlybird')
                                        , {{ $reg->discountCode }}</td>
                                @else
                                    <td style="text-align: left;">@lang('messages.headers.earlybird')</td>
                                @endif
                            @else
                                @if($reg->discountCode)
                                    <td style="text-align: left;">{{ $reg->discountCode }}</td>
                                @else
                                    <td style="text-align: left;"> --</td>
                                @endif
                            @endif
                            <td style="text-align: left;"><i class="far fa-dollar-sign"></i>
                                {{ number_format($reg->subtotal, 2, ".", ",") }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2"
                                style="width: 50%; text-align: left;">@lang('messages.headers.att_info')</th>
                            <th colspan="2"
                                style="width: 50%; text-align: left;">@lang('messages.headers.event_info')</th>
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
                                    <br/>@lang('messages.headers.aff_with'): {{ $person->affiliation }}
                                @endif
                            </td>
                            <td colspan="2" style="text-align: left;">

                                <b>@lang('messages.headers.roster_add'):</b> {{ $reg->canNetwork
                                                                            ? trans('messages.yesno_check.yes')
                                                                            : trans('messages.yesno_check.no') }}<br/>

                                <b>@lang('messages.headers.certs'):</b>: {{ $person->certifications }} <br/>

                                <b>@lang('messages.fields.pdu_sub'):</b> {{ $reg->isAuthPDU
                                                                            ? trans('messages.yesno_check.yes')
                                                                            : trans('messages.yesno_check.no') }}<br/>
                                @if($reg->allergenInfo)
                                    <b>@lang('messages.fields.diet_info'):</b> {{ $reg->allergenInfo }}<br/>
                                    @if($reg->eventNotes)
                                        {{ $reg->eventNotes }}<br/>
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
                <h1 class="far fa-5x fa-dollar-sign"></h1>
            </div>
            <div class="col-md-7 col-sm-7">
                @if($deletion)
                    <p class="red"><b>@lang('messages.headers.note'):</b> @lang('messages.instructions.total_caveat')
                    </p>
                @else
                    &nbsp;
                @endif
            </div>
            <div class="col-md-3 col-sm-3">
                <table class="table table-striped table-condensed jambo_table">
                    <thead>
                    <tr>
                        <th style="text-align: center;">@lang('messages.fields.total')</th>
                    </tr>
                    </thead>
                    <tr>
                        <td style="text-align: center;">
                            <b><i class="far fa-dollar-sign"></i> {{ number_format($rf->cost, 2, '.', ',') }}</b>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <hr>
        @if($event->postRegInfo)
            <div class="col-sm-2">&nbsp;</div>
            <div class="col-sm-10">
                @include('v1.parts.start_content', ['header' => trans('messages.fields.additional'), 'subheader' => '',
                         'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
                {!! $event->postRegInfo ?? '' !!}
                @include('v1.parts.end_content')
            </div>
        @endif
        <hr>

        <div class="col-sm-12">
            <h4>@lang('messages.headers.add_to_cal')</h4>
            @if(0)
                <table class="table borderless">
                    <tr>
                        <td style="text-align: center;"><a target="_new" href="{{ $ics }}">
                                <h2 style="color: gold;" class="fal fa-4x fa-calendar-alt"></h2></a></td>
                        <td style="text-align: center;"><a target="_new" href="{{ $google_url }}">
    <span class="far fa-stack fa-lg fa-2x">
    <h1 style="color: red;" class="far fa-4x fa-square fa-stack-2x"></h1>
    <h1 style="color: white;" class="far fa-1x fa-google fa-stack-1x"></h1>
    </span>
                            </a></td>
                        <td style="text-align: center;"><a target="_new" href="{{ $yahoo_url }}">
    <span class="far fa-stack fa-lg fa-2x">
    <h1 style="color: rebeccapurple;" class="far fa-3x fa-square fa-stack-2x"></h1>
    <h1 style="color: white;" class="far fa-1x fa-yahoo fa-stack-1x"></h1>
    </span>
                            </a></td>
                        <td style="text-align: center;"><a target="_new" href="{{ $ics }}">
    <span class="far fa-stack fa-lg fa-2x">
    <h2 style="color: red;" class="fal fa-3x fa-calendar-alt fa-stack-2x"></h2>
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
        </div>
    </div>

    @include('v1.parts.end_content')
@endsection


