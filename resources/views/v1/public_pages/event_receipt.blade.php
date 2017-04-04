<?php
/**
 * Comment: Event Receipt
 * Created: 3/26/2017
 */
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
                        {{ $loc->addr1 }} <i class="fa fa-circle fa-tiny-circle"></i> {{ $loc->city }}
                        , {{ $loc->state }} {{ $loc->zip }}
                    </div>
                    <br/>
                </div>
                <div class="col-md-3 col-sm-3">
                </div>
            </div>

            @if($rf->seats > 1)
                @for($i=$rf->regID-1;$i<=$rf->regID;$i++)
                    <?php
                    $reg = \App\Registration::find($i); $tcount++;
                    $person = \App\Person::find($reg->personID);
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

                                    @if(!($ticket->earlyBirdEndDate === null) && $ticket->earlyBirdEndDate->diffInSeconds($today)>0)
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

                                        @if($person->midName !== null)
                                            {{ $person->midName }}
                                        @endif
                                        {{ $person->lastName }}

                                        @if($person->suffix)
                                            {{ $person->suffix }}
                                        @endif
                                        [ {{ $person->login }} ]
                                        <br/>
                                        @if($person->compName)
                                            @if($person->title)
                                                {{ $person->title }}
                                            @else
                                                Employed
                                            @endif
                                            at {{ $person->compName }}
                                        @else
                                            {{ $person->title }}
                                        @endif
                                        @if($person->indName)
                                            in the {{ $person->indName }} industry <br/>
                                        @endif

                                        @if($person->affiliation)
                                            <br/>Affiliated with: {{ $person->affiliation }}
                                        @endif
                                    </td>
                                    <td colspan="2" style="text-align: left;">
                                        @if($reg->isFirstEvent)
                                            <b>First Event?</b> {{ $reg->isFirstEvent }}<br/>
                                        @endif

                                        <b>Add to Roster:</b> {{ $reg->canNetwork }}<br/>
                                        <b><a data-toggle="tooltip" title="Do you authorize PMI to submit your PDUs?">PDU
                                                Submission :</a></b> {{ $reg->isAuthPDU }}<br/>
                                        @if($reg->eventQuestion)
                                            <p><b>Speaker Questions:</b> {{ $reg->eventQuestion }}</p>
                                        @endif

                                        @if($reg->eventTopics)
                                            <p><b>Future Topics:</b><br/> {{ $reg->eventTopics }}</p>
                                        @endif

                                        @if($reg->cityState)
                                            <br/><b>Commuting From:</b> {{ $reg->cityState }}</br>
                                        @endif

                                        @if($reg->specialNeeds)
                                            <b>Special Needs:</b> {{ $reg->specialNeeds }}<br/>
                                        @endif

                                        @if($reg->allergenInfo)
                                            <b>Dietary Info:</b> {{ $reg->allergenInfo }}<br/>
                                            {{ $reg->eventNotes }}
                                        @elseif($reg->eventNotes)
                                            <b>Other Comments/Notes:</b> {{ $reg->eventNotes }}
                                        @endif

                                    </td>
                                </tr>
                            </table>
                        </div>

                    </div>
                @endfor
            @else
                <?php
                $reg = \App\Registration::find($rf->regID); $tcount++;
                $person = \App\Person::find($reg->personID);
                $ticket = \App\Ticket::find($reg->ticketID);
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

                                @if(!($ticket->earlyBirdEndDate === null) && $ticket->earlyBirdEndDate->diffInSeconds($today)>0)
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
                                        {{ $person->prefName }}
                                    @endif
                                    @if($person->midName)
                                        {{ $person->midName }}
                                    @endif
                                    {{ $person->lastName }}
                                    @if($person->suffix)
                                        {{ $person->suffix }}
                                    @endif
                                    [ {{ $person->login }}" ]
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
                                        <b>First Event?</b> {{ $reg->isFirstEvent ? 'Yes' : 'No' }}<br />
                                    @endif

                                    <b>Add to Roster:</b> {{ $reg->canNetwork ? 'Yes' : 'No' }}<br />
                                    <b>PDU Submission:</b> {{ $reg->isAuthPDU ? 'Yes' : 'No' }}<br />
                                    @if($reg->eventQuestion)
                                        <p><b>Speaker Questions:</b> {{ $reg->eventQuestion }}</p>
                                    @endif

                                    @if($reg->eventTopics)
                                         <p><b>Future Topics:</b><br/> {{ $reg->eventTopics }}</p>
                                    @endif

                                    @if($reg->cityState)
                                        <b>Commuting From:</b> {{ $reg->cityState }}<br />
                                    @endif

                                    @if($reg->specialNeeds)
                                        <b>Special Needs:</b> {{ $reg->specialNeeds }} <br />
                                    @endif

                                    @if($reg->allergenInfo)
                                        <b>Dietary Info:</b> {{ $reg->allergenInfo }}<br />
                                        @if($reg->eventNotes)
                                            {{ $reg->eventNotes }}<br />
                                        @endif
                                    @elseif($reg->eventNotes)
                                        <b>Other Comments/Notes:</b> {{ $reg->eventNotes }}<br />
                                    @endif

                                </td>
                            </tr>
                        </table>
                    </div>

                </div>

            @endif

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
        </div>
    </div>

    {{-- add links to ical, etc. --}}
    @include('v1.parts.end_content')
@endsection


@if(0)
    <table class="table borderless">
        <tr>
            <td style="text-align: center;"><img src="/images/outlook.jpg" height="55" /></td>
            <td style="text-align: center;"><img src="/images/google.jpg" height="45" /></td>
            <td style="text-align: center;"><img src="/images/yahoo.jpg" height="45" /></td>
            <td style="text-align: center;"><img src="/images/ical.jpg" height="45" /></td>
        </tr>
    </table>
@endif