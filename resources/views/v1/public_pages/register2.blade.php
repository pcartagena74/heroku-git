<?php
/**
 * Comment: Confirmation screen and launcher of Braintree Paypal stuff
 * Created: 3/12/2017
 */

$tcount = 0;
$today = Carbon\Carbon::now();
?>
@extends('v1.layouts.no-auth')


@section('content')
    @include('v1.parts.start_content', ['header' => "Registration Confirmation", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
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
                    <p></p>

                    @if($rf->cost > 0)
                    <form action="/complete_registration/{{ $rf->regID }}" method="POST">
                        {{ csrf_field() }}
                        <script
                                src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                                data-key="{{ env('STRIPE_KEY') }}"
                                data-amount="{{ $rf->cost*100 }}"
                                data-label="Pay Now by Credit Card"
                                data-email="{{ $person->login }}"
                                data-name="{{ $event->org->orgName }} (mCentric)"
                                data-description="Event Registration"
                                data-zip-code="true"
                                data-image="https://s3.amazonaws.com/stripe-uploads/acct_19zQbHCzTucS72R2merchant-icon-1490128809088-mCentric_square.png"
                                data-locale="auto">
                        </script>
                    </form>
                    @endif
                    <form action="/complete_registration/{{ $rf->regID }}" method="POST">
                        {{ csrf_field() }}
                    <button type="submit" class="btn btn-success btn-sm">&nbsp;<b>Pay by Cash/Check at Door</b></button>
                    </form>
                </div>
            </div>

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
                                    {{ $person->prefix }}
                                    {{ $person->firstName }}
                                    @if($person->prefName)
                                        ({{ $person->prefName }})
                                    @endif
                                    {{ $person->midName }}
                                    {{ $person->lastName }}
                                    {{ $person->suffix }}
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

            <div class="myrow col-md-12 col-sm-12">
                <div class="col-md-2 col-sm-2" style="text-align:center;">
                    <h1 class="fa fa-5x fa-dollar"></h1>
                </div>
                <div class="col-md-3 col-sm-3 col-sm-offset-7 col-md-offset-7">
                    <table class="table table-striped table-condensed jambo_table">
                        <thead>
                        <tr>
                            <th style="text-align: center;">Total</th>
                        </tr>
                        </thead>
                        <tr>
                            <td style="text-align: center;"><b><i class="fa fa-dollar"></i> {{ number_format($rf->cost, 2, '.', ',') }}</b></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('v1.parts.end_content')
@endsection


@section('scripts')
    <script src="https://www.google.com/recaptcha/api.js"></script>
@endsection
