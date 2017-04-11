<?php
/**
 * Comment: Confirmation screen post and Stripe Payment Processing
 * Created: 3/12/2017
 */

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
                            <button type="submit" class="btn btn-success btn-sm">&nbsp;<b>{{ $rf->cost > 0 ? 'Pay by Cash/Check at Door' : 'Complete Registration' }}</b>
                        </button>
                    </form>
                </div>
            </div>

            @for($i=$rf->regID-($rf->seats-1);$i<=$rf->regID;$i++)
                <?php
                $reg = \App\Registration::find($i); $tcount++;
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
                                        <a id="prefix-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                           data-value="{{ $person->prefix }}" data-url="/profile/{{ $person->personID }}"></a>
                                    @endif
                                    @if($reg->membership == 'Non-Member')
                                    <a id="firstName-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                       data-value="{{ $person->firstName }}" data-url="/profile/{{ $person->personID }}"></a>
                                    @else
                                        {{ $person->firstName }}
                                    @endif
                                    @if($person->prefName)
                                        (<a id="prefName-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                            data-value="{{ $person->prefName }}"
                                            data-url="/profile/{{ $person->personID }}"></a>)
                                    @endif
                                    @if($person->midName)
                                        <a id="midName-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                           data-value="{{ $person->midName }}"
                                           data-url="/profile/{{ $person->personID }}"></a>
                                    @endif
                                    @if($reg->membership == 'Non-Member')
                                    <a id="lastName-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                       data-value="{{ $person->lastName }}" data-url="/profile/{{ $person->personID }}"></a>
                                    @else
                                        {{ $person->lastName }}
                                    @endif
                                    @if($person->suffix)
                                        <a id="suffix-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                           data-value="{{ $person->suffix }}" data-url="/profile/{{ $person->personID }}"></a>
                                    @endif
                                    <nobr>[ <a id="login-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                               data-value="{{ $person->login }}" data-url="/profile/{{ $person->personID }}"></a> ]</nobr>
                                    <br/>
                                    @if($person->compName)
                                        @if($person->title)
                                            <a id="title-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                               data-value="{{ $person->title }}"
                                               data-url="/profile/{{ $person->personID }}"></a>
                                        @else
                                            Employed
                                        @endif
                                        at <a id="compName-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                              data-value="{{ $person->compName }}"
                                              data-url="/profile/{{ $person->personID }}"></a>
                                    @else
                                        @if($person->title !== null)
                                            <a id="title-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                               data-value="{{ $person->title }}"
                                               data-url="/profile/{{ $person->personID }}"></a>
                                        @elseif($person->indName !== null)
                                            Employed
                                        @endif
                                    @endif
                                    @if($person->indName !== null)
                                        in the <a id="indName-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                                  data-value="{{ $person->indName }}"
                                                  data-url="/profile/{{ $person->personID }}"></a> industry <br/>
                                    @endif

                                    @if($person->affiliation)
                                        <br/>Affiliated with: <a id="affiliation-{{ $tcount }}"
                                                                 data-pk="{{ $person->personID }}"
                                                                 data-value="{{ $person->affiliation }}"
                                                                 data-url="/profile/{{ $person->personID }}"></a>
                                    @endif
                                </td>
                                <td colspan="2" style="text-align: left;">
                                    @if($reg->isFirstEvent)
                                        <b>First Event?</b> <a id="firstEvent-{{ $tcount }}"
                                                               data-pk="{{ $reg->regID }}"
                                                               data-value="{{ $reg->isFirstEvent }}"
                                                               data-url="/reg_verify/{{ $reg->regID }}"></a><br/>
                                    @endif

                                    <b>Add to Roster:</b> <a id="canNetwork-{{ $tcount }}"
                                                             data-pk="{{ $reg->regID }}"
                                                             data-value="{{ $reg->canNetwork }}"
                                                             data-url="/reg_verify/{{ $reg->regID }}"></a><br/>
                                    <b><a data-toggle="tooltip" title="Do you authorize PMI to submit your PDUs?">PDU
                                            Submission :</a></b> <a id="isAuthPDU-{{ $tcount }}"
                                                                    data-pk="{{ $reg->regID }}"
                                                                    data-value="{{ $reg->isAuthPDU }}"
                                                                    data-url="/reg_verify/{{ $reg->regID }}"></a><br/>
                                    @if($reg->eventQuestion)
                                        <p><b>Speaker Questions:</b> <a id="eventQuestion-{{ $tcount }}"
                                                                        data-pk="{{ $reg->regID }}"
                                                                        data-value="{{ $reg->eventQuestion }}"
                                                                        data-url="/reg_verify/{{ $reg->regID }}"></a></p>
                                    @endif

                                    @if($reg->eventTopics)
                                        <p><b>Future Topics:</b><br/> <a id="eventTopics-{{ $tcount }}"
                                                                         data-pk="{{ $reg->regID }}"
                                                                         data-value="{{ $reg->eventTopics }}"
                                                                         data-url="/reg_verify/{{ $reg->regID }}"></a></p>
                                    @endif

                                    @if($reg->cityState)
                                        <br/><b>Commuting From:</b> <a id="cityState-{{ $tcount }}"
                                                                       data-pk="{{ $reg->regID }}"
                                                                       data-value="{{ $reg->cityState }}"
                                                                       data-url="/reg_verify/{{ $reg->regID }}"></a></br>
                                    @endif

                                    @if($reg->specialNeeds)
                                        <b>Special Needs:</b> <a id="specialNeeds-{{ $tcount }}"
                                                                 data-pk="{{ $reg->regID }}"
                                                                 data-value="{{ $reg->specialNeeds }}"
                                                                 data-url="/reg_verify/{{ $reg->regID }}"></a><br/>
                                    @endif

                                    @if($reg->allergenInfo)
                                        <b>Dietary Info:</b> <a id="allergenInfo-{{ $tcount }}"
                                                                data-pk="{{ $reg->regID }}"
                                                                data-value="{{ $reg->allergenInfo }}"
                                                                data-url="/reg_verify/{{ $reg->regID }}"></a><br/>
                                        @if($reg->eventNotes)
                                        <a id="eventNotes-{{ $tcount }}" data-pk="{{ $reg->regID }}"
                                           data-value="{{ $reg->eventNotes }}"
                                           data-url="/reg_verify/{{ $reg->regID }}"></a>
                                        @endif
                                    @elseif($reg->eventNotes)
                                        <b>Other Comments/Notes:</b> <a id="eventNotes-{{ $tcount }}"
                                                                        data-pk="{{ $reg->regID }}"
                                                                        data-value="{{ $reg->eventNotes }}"
                                                                        data-url="/reg_verify/{{ $reg->regID }}"></a>
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
                        <button type="submit" class="btn btn-success btn-sm">&nbsp;<b>{{ $rf->cost > 0 ? 'Pay by Cash/Check at Door' : 'Complete Registration' }}</b>
                        </button>
                    </form>
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
    @include('v1.parts.end_content')
@endsection


@section('scripts')
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('[data-toggle="tooltip"]').tooltip({'placement': 'top'});
            $.fn.editable.defaults.mode = 'inline';
            $.fn.editable.defaults.params = function (params) {
                params._token = $("meta[name=token]").attr("content");
                return params;
            };
            @for($i=1;$i<=$rf->seats;$i++)
            $('#prefix-{{ $i }}').editable({
                type: 'select',
                autotext: 'auto',
                source: [
                    <?php
                    foreach($prefixes as $row) {
                        $string .= "{ value: '" . $row->prefix . "' , text: '" . $row->prefix . "' },\n";
                    } ?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });
            $("#firstName-{{ $i }}").editable({type: 'text'});
            $("#midName-{{ $i }}").editable({type: 'text'});
            $("#lastName-{{ $i }}").editable({type: 'text'});
            $("#prefName-{{ $i }}").editable({type: 'text'});
            $("#suffix-{{ $i }}").editable({type: 'text'});

            $('#indName-{{ $i }}').editable({
                type: 'select',
                source: [
                    <?php
                    foreach($industries as $row) {
                        $string .= "{ value: '" . $row->industryName . "' , text: '" . $row->industryName . "' },";
                    } ?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });

            $("#compName-{{ $i }}").editable({type: 'text'});
            $("#title-{{ $i }}").editable({type: 'text', emptytext: 'Title'});
            $("#login-{{ $i }}").editable({type: 'text'});

            $('#affiliation-{{ $i }}').editable({
                type: 'checklist',
                source: [
                    <?php
                    for($j = 1; $j <= count($affiliation_array); $j++) {
                        $string .= "{ value: '" . $affiliation_array[$j] . "' , text: '" . $affiliation_array[$j] . "' },";
                    } ?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });

            $("#eventQuestion-{{ $i }}").editable({type: 'text'});
            $("#eventTopics-{{ $i }}").editable({type: 'text'});
            $("#cityState-{{ $i }}").editable({type: 'text'});
            $("#specialNeeds-{{ $i }}").editable({type: 'text'});
            $("#eventNotes-{{ $i }}").editable({type: 'text'});

            $("#firstEvent-{{ $i }}").editable({
                type: 'select',
                source: [
                    {value: '0', text: 'No'},
                    {value: '1', text: 'Yes'}
                ]
            });

            $("#canNetwork-{{ $i }}").editable({
                type: 'select',
                source: [
                    {value: '0', text: 'No'},
                    {value: '1', text: 'Yes'}
                ]
            });

            $("#isAuthPDU-{{ $i }}").editable({
                type: 'select',
                source: [
                    {value: '0', text: 'No'},
                    {value: '1', text: 'Yes'}
                ]
            });

            $("#allergenInfo-{{ $i }}").editable({
                type: 'checklist',
                source: [
                    <?php
                    foreach($allergen_array as $x) {
                        $string .= "{ value: '" . $x . "' , text: '" . $x . "' },";
                    } ?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });

            @endfor
        });
    </script>
@endsection
