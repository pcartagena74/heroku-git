@php
/**
 * Comment: Confirmation screen post and Stripe Payment Processing for Group Registration
 * Created: 8/21/2017
 *
 * @var $event, $quantity, $org, $loc, $rf
 */

use App\Models\EventSession;
use App\Ticket;
use App\Registration;
use App\Person;

$tcount = 0;
$today = Carbon\Carbon::now();
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

if($event->isSymmetric && $event->hasTracks) {
    $columns = ($event->hasTracks * 2) + 1;
    $width   = number_format(85 / $event->hasTracks, 0, '', '');
    $mw      = number_format(90 / $event->hasTracks, 0, '', '');
} elseif($event->hastTracks) {
    $columns = $event->hasTracks * 3;
    $width   = number_format(80 / $event->hasTracks, 0, '', '');
    $mw      = number_format(85 / $event->hasTracks, 0, '', '');
}
@endphp

@extends('v1.layouts.auth')


@section('content')
    @include('v1.parts.start_content', ['header' => "Group Registration Confirmation", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    {!! Form::open(['url' => env('APP_URL').'/group_reg2/'.$rf->regID, 'method' => 'patch', 'id' => 'complete_registration', 'data-toggle' => 'validator']) !!}

    <div class="whole">

        {{--        <div class="left col-md-7 col-sm-7">            --}}
        <div class="myrow col-md-12 col-sm-12">

            <div class="col-md-2 col-sm-2" style="text-align:center;">
                <h1 class="far fa-5x fa-calendar-alt"></h1>
            </div>
            <div class="col-md-6 col-sm-6">
                <h2><b>{{ $event->eventName }}</b></h2>
                <div style="margin-left: 10px;">
                    @include('v1.parts.location_display', ['loc' => $loc, 'event' => $event, 'time' => 1])
                </div>
                <br/>
            </div>
            <div class="col-md-4 col-sm-4" style="text-align: right;">
                <p></p>

                @if($rf->cost > 0)
                    <button id="payment" type="submit" data-toggle="modal" data-target="#stripe_modal"
                            class="card btn btn-primary btn-md">
                        <b>Pay Now by Credit Card</b>
                    </button>
                @endif

                <button id='nocard' type="submit" class="btn btn-success btn-sm">&nbsp;
                    <b>{{ $rf->cost > 0 ? 'Pay by Cash/Check at Door' : 'Complete Registration' }}</b>
                </button>

            </div>
        </div>

        {{--
        @for($i=$rf->regID-($rf->seats-1);$i<=$rf->regID;$i++)
            $reg = Registration::find($i);
        --}}
        @foreach($rf->registrations as $reg)
<?php
            $tcount++;
            $person = Person::find($reg->personID);
            $ticket = Ticket::find($reg->ticketID);
?>

            <div class="myrow col-md-12 col-sm-12">
                <div class="col-md-2 col-sm-2" style="text-align:center;">
                    <h1 class="fas fa-5x fa-user"></h1>
                </div>
                <div class="col-md-10 col-sm-10">
                    <table class="table table-bordered table-condensed table-striped jambo_table">
                        <thead>
                        <tr>
                            <th colspan="4" style="text-align: left;">{{ strtoupper($reg->membership) }} TICKET:
                                #{{ $tcount }}</th>
                        </tr>
                        </thead>
                        <tr>
                            <th style="text-align: left; color:darkgreen;">Ticket</th>
                            <th style="text-align: left; color:darkgreen;">Original Cost</th>
                            <th style="text-align: left; color:darkgreen;">Discounts</th>
                            <th style="text-align: left; color:darkgreen;">Subtotal</th>
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
                            <td style="text-align: left;"><i class="far fa-dollar-sign"></i>
                                {{ number_format($reg->subtotal, 2, ".", ",") }}
                            </td>
                        </tr>
                        <tr>
                            <th colspan="4" style="width: 50%; text-align: left;">Attendee Info</th>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: left;">
                                @if($person->prefix)
                                    <a id="prefix-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                       data-value="{{ $person->prefix }}"
                                       data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a>
                                @endif
                                @if($reg->membership == 'Non-Member')
                                    <a id="firstName-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                       data-value="{{ $person->firstName }}"
                                       data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a>
                                @else
                                    {{ $person->firstName }}
                                @endif
                                @if($person->prefName)
                                    (<a id="prefName-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                        data-value="{{ $person->prefName }}"
                                        data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a>)
                                @endif
                                @if($person->midName)
                                    <a id="midName-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                       data-value="{{ $person->midName }}"
                                       data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a>
                                @endif
                                @if($reg->membership == 'Non-Member')
                                    <a id="lastName-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                       data-value="{{ $person->lastName }}"
                                       data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a>
                                @else
                                    {{ $person->lastName }}
                                @endif
                                @if($person->suffix)
                                    <a id="suffix-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                       data-value="{{ $person->suffix }}"
                                       data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a>
                                @endif
                                <nobr>[ <a id="login-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                           data-value="{{ $person->login }}"
                                           data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a> ]
                                </nobr>
                                <br/>
                                    @if($event->eventTypeID==5)
                                        <a id="chapterRole-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                           data-value="{{ $person->chapterRole }}"
                                           data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a>
                                        with: PMI
                                        <a id="affiliation-{{ $tcount }}"
                                           data-pk="{{ $person->personID }}"
                                           data-value="{{ $person->affiliation }}"
                                           data-url="{{ env('APP_URL') }}/reg_verify/{{ $person->personID }}"></a>
                                    @else
                                        @if($person->compName)
                                            @if($person->title)
                                                <a id="title-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                                   data-value="{{ $person->title }}"
                                                   data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a>
                                            @else
                                                Employed
                                            @endif
                                            at <a id="compName-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                                  data-value="{{ $person->compName }}"
                                                  data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a>
                                        @else
                                            @if($person->title !== null)
                                                <a id="title-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                                   data-value="{{ $person->title }}"
                                                   data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a>
                                            @else($person->indName !== null)
                                                Employed
                                            @endif
                                            @if($person->indName !== null)
                                                in the <a id="indName-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                                          data-value="{{ $person->indName }}"
                                                          data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a> industry <br/>
                                            @endif
                                        @endif
                                        @if($person->affiliation)
                                            <br/>Affiliated with: <a id="affiliation-{{ $tcount }}"
                                                                     data-pk="{{ $person->personID }}"
                                                                     data-value="{{ $person->affiliation }}"
                                                                     data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a>
                                        @endif
                                    @endif
                            </td>
                            <td colspan="2" style="text-align: left;">
                                <b>Add to Roster:</b> <a id="canNetwork-{{ $tcount }}"
                                                         data-pk="{{ $reg->regID }}"
                                                         data-value="{{ $reg->canNetwork }}"
                                                         data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a><br/>
                                <b><a data-toggle="tooltip" title="Do you authorize PMI to submit your PDUs?">PDU
                                        Submission :</a></b> <a id="isAuthPDU-{{ $tcount }}"
                                                                data-pk="{{ $reg->regID }}"
                                                                data-value="{{ $reg->isAuthPDU }}"
                                                                data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a><br/>
                            </td>
                        </tr>
                    </table>

                </div>
            </div>

        @endforeach  {{-- closes $rf loop --}}

        <div class="myrow col-md-12 col-sm-12" style="display: table-row; vertical-align: top;">
            <div class="col-md-2 col-sm-2" style="display: table-cell; text-align:center;">
                <h1 class="far fa-5x fa-dollar-sign"></h1>
            </div>
            <div class="col-md-7 col-sm-7" style="display: table-cell;">
                @if($rf->cost > 0)
                    <button id="payment" type="submit" data-toggle="modal" data-target="#stripe_modal"
                            class="card btn btn-primary btn-md">
                        <b>Pay Now by Credit Card</b>
                    </button>
                    <br/>
                @endif

                <button id="nocard" type="submit" class="btn btn-success btn-sm">&nbsp;
                    <b>{{ $rf->cost > 0 ? 'Pay by Cash/Check at Door' : 'Complete Registration' }}</b>
                </button>

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
                                        class="far fa-dollar-sign"></i> {{ number_format($rf->cost, 2, '.', ',') }}</b>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        {{--        </div>  --}}
    </div>
    {!! Form::close() !!}
    @include('v1.parts.end_content')
@endsection


@section('scripts')
    @if($rf->cost > 0)
        <script>
            $('.card').on('click', function (e) {
                // Open Checkout with further options:
                e.preventDefault();
            });
        </script>
    @endif

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

            $("#firstName-{{ $i }}").editable({type: 'text'});
            $("#midName-{{ $i }}").editable({type: 'text'});
            $("#lastName-{{ $i }}").editable({type: 'text'});
            $("#prefName-{{ $i }}").editable({type: 'text'});
            $("#suffix-{{ $i }}").editable({type: 'text'});

            $("#compName-{{ $i }}").editable({type: 'text'});
            $("#title-{{ $i }}").editable({type: 'text', emptytext: 'Title'});
            $("#chapterRole-{{ $i }}").editable({type: 'text', emptytext: 'Role'});
            $("#login-{{ $i }}").editable({type: 'text'});

            $('#affiliation-{{ $i }}').editable({
                type: 'checklist',
                source: [
<?php
                    for($j = 1; $j <= count($affiliation_array); $j++) {
                        $string .= "{ value: '" . $affiliation_array[$j] . "' , text: '" . $affiliation_array[$j] . "' },";
                    }
?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
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
                    }
?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });

            @endfor
        });
    </script>
    @include('v1.parts.menu-fix', array('path' => '/group'))

@endsection

@section('modals')
    @include('v1.modals.stripe', array('amt' => $rf->cost, 'rf' => $rf))
@endsection