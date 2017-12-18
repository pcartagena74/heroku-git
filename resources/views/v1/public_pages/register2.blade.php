<?php
/**
 * Comment: Confirmation screen post and Stripe Payment Processing
 * Created: 3/12/2017
 */

use App\EventSession;
use App\Ticket;
use App\Registration;
use App\Person;

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

if($event->isSymmetric && $event->hasTracks) {
    $columns = ($event->hasTracks * 2) + 1;
    $width   = number_format(85 / $event->hasTracks, 0, '', '');
    $mw      = number_format(90 / $event->hasTracks, 0, '', '');
} elseif($event->hasTracks) {
    $columns = $event->hasTracks * 3;
    $width   = number_format(80 / $event->hasTracks, 0, '', '');
    $mw      = number_format(85 / $event->hasTracks, 0, '', '');
}

?>
@extends('v1.layouts.no-auth')
@section('content')
    @include('v1.parts.start_content', ['header' => "Registration Confirmation", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    {!! Form::open(['url' => env('APP_URL').'/complete_registration/'.$rf->regID, 'method' => 'patch', 'id' => 'complete_registration', 'data-toggle' => 'validator']) !!}

    <div class="whole">

        <div style="float: right;" class="col-md-5 col-sm-5">
            <img style="opacity: .25;" src="{{ env('APP_URL') }}/images/meeting.jpg" width="100%" height="90%">
        </div>
        <div class="left col-md-7 col-sm-7">
            <div class="myrow col-md-12 col-sm-12">

                <div class="col-md-2 col-sm-2" style="text-align:center;">
                    <h1 class="fa fa-5x fa-calendar"></h1>
                </div>
                <div class="col-md-6 col-sm-6">
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
                <div class="col-md-3 col-sm-3 col-md-offset-1 col-sm-offset-1" style="text-align: right;">
                    <p></p>

                    @if($rf->cost > 0 && $rf->status != 'Wait List')
                        <button id="payment" type="submit" data-toggle="modal" data-target="#stripe_modal"
                                class="card btn btn-primary btn-md">
                            <b>Pay Now by Credit Card</b>
                        </button>
                        {{--
                        <div id="payment-request-button" class="card">
                        </div>
                        --}}
                    @endif

                    <button id='nocard' type="submit" class="btn btn-success btn-sm">&nbsp;
                        @if($rf->cost > 0 && $rf->status != 'Wait List')
                            <b>{{ $rf->cost > 0 ? 'Pay by Cash/Check at Door' : 'Complete Registration' }}</b>
                        @elseif($rf->status == 'Wait List')
                            <b>Join the Wait List</b>
                        @else
                            <b>Complete Registration</b>
                        @endif
                    </button>

                </div>
            </div>
            @if($show_pass_fields)
            <div class="col-md-10 col-sm-10 col-sm-offset-2 coll-md-offset-2">
                <div class="col-sm-6 form-group">
                    {!! Form::password('password', array('required', 'class' => 'form-control input-sm', 'placeholder' => 'Set a password')) !!}
                </div>
                <div class="col-sm-6 form-group">
                    {!! Form::password('password_confirmation', array('required', 'class' => 'form-control input-sm', 'placeholder' => 'Confirm your password')) !!}
                </div>
            </div>
            @endif

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

                                <td style="text-align: left;"><i class="fa fa-dollar"></i>
                                    @if($reg->membership == 'Member')
                                        {{ number_format($ticket->memberBasePrice, 2, ".", ",") }}
                                    @else
                                        {{ number_format($ticket->nonmbrBasePrice, 2, ".", ",") }}
                                    @endif
                                </td>

                                @if(($ticket->earlyBirdEndDate !== null) && $ticket->earlyBirdEndDate->gt($today))
                                    @if($reg->discountCode)
                                        <td style="text-align: left;">Early Bird, {{ $rf->discountCode }}</td>
                                    @else
                                        <td style="text-align: left;">Early Bird</td>
                                    @endif
                                @else
                                    @if($reg->discountCode)
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
                                        @elseif($person->indName !== null)
                                            Employed
                                        @endif
                                    @endif
                                    @if($person->indName !== null)
                                        in the <a id="indName-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                                  data-value="{{ $person->indName }}"
                                                  data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a> industry <br/>
                                    @endif

                                    @if($person->affiliation)
                                        <br/>Affiliated with: <a id="affiliation-{{ $tcount }}"
                                                                 data-pk="{{ $person->personID }}"
                                                                 data-value="{{ $person->affiliation }}"
                                                                 data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a>
                                    @endif
                                </td>
                                <td colspan="2" style="text-align: left;">
                                    @if($reg->isFirstEvent)
                                        <b>First Event?</b> <a id="firstEvent-{{ $tcount }}"
                                                               data-pk="{{ $reg->regID }}"
                                                               data-value="{{ $reg->isFirstEvent }}"
                                                               data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a><br/>
                                    @endif

                                    <b>Add to Roster:</b> <a id="canNetwork-{{ $tcount }}"
                                                             data-pk="{{ $reg->regID }}"
                                                             data-value="{{ $reg->canNetwork }}"
                                                             data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a><br/>
                                            @include('v1.parts.tooltip', ['title' => "Do you authorize PMI to submit your PDUs?"])
                                        <b>PDU Submission :</b> <a id="isAuthPDU-{{ $tcount }}"
                                                                    data-pk="{{ $reg->regID }}"
                                                                    data-value="{{ $reg->isAuthPDU }}"
                                                                    data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a><br/>
                                    @if($reg->eventQuestion)
                                        <p><b>Speaker Questions:</b> <a id="eventQuestion-{{ $tcount }}"
                                                                        data-pk="{{ $reg->regID }}"
                                                                        data-value="{{ $reg->eventQuestion }}"
                                                                        data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a>
                                        </p>
                                    @endif

                                    @if($reg->eventTopics)
                                        <p><b>Future Topics:</b><br/> <a id="eventTopics-{{ $tcount }}"
                                                                         data-pk="{{ $reg->regID }}"
                                                                         data-value="{{ $reg->eventTopics }}"
                                                                         data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a>
                                        </p>
                                    @endif

                                    @if($reg->cityState)
                                        <br/><b>Commuting From:</b> <a id="cityState-{{ $tcount }}"
                                                                       data-pk="{{ $reg->regID }}"
                                                                       data-value="{{ $reg->cityState }}"
                                                                       data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a></br>
                                    @endif

                                    @if($reg->specialNeeds)
                                        <b>Special Needs:</b> <a id="specialNeeds-{{ $tcount }}"
                                                                 data-pk="{{ $reg->regID }}"
                                                                 data-value="{{ $reg->specialNeeds }}"
                                                                 data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a><br/>
                                    @endif

                                    @if($reg->allergenInfo)
                                        <b>Dietary Info:</b> <a id="allergenInfo-{{ $tcount }}"
                                                                data-pk="{{ $reg->regID }}"
                                                                data-value="{{ $reg->allergenInfo }}"
                                                                data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a><br/>
                                        @if($reg->eventNotes)
                                            <a id="eventNotes-{{ $tcount }}" data-pk="{{ $reg->regID }}"
                                               data-value="{{ $reg->eventNotes }}"
                                               data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a>
                                        @endif
                                    @elseif($reg->eventNotes)
                                        <b>Other Comments/Notes:</b> <a id="eventNotes-{{ $tcount }}"
                                                                        data-pk="{{ $reg->regID }}"
                                                                        data-value="{{ $reg->eventNotes }}"
                                                                        data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a>
                                    @endif

                                </td>
                            </tr>
                        </table>

                        {!! Form::hidden('needSessionPick', $needSessionPick) !!}

                        @if($event->hasTracks > 0 && $needSessionPick == 1)
                            <table class="table table-bordered jambo_table table-striped">
                                <thead>
                                <tr>
                                    <th colspan="{{ $columns }}" style="text-align: left;">
                                        Track Selection
                                    </th>
                                </tr>
                                </thead>
                                <tr>
                                    @foreach($tracks as $track)
                                        @if($tracks->first() == $track || !$event->isSymmetric)
                                            <th style="text-align:left;">Session Times</th>
                                        @endif
                                        <th colspan="2" style="text-align:center;"> {{ $track->trackName }} </th>
                                    @endforeach
                                </tr>
                                @for($j=1;$j<=$event->confDays;$j++)
<?php
                                    $z = EventSession::where([
                                        ['confDay', '=', $j],
                                        ['eventID', '=', $event->eventID]
                                    ])->first();
                                    $y = Ticket::find($z->ticketID);
?>
                                    @if($tickets->contains('ticketID', $z->ticketID))
                                        <tr>
                                            <th style="text-align:center; color: yellow; background-color: #2a3f54;"
                                                colspan="{{ $columns }}">Day {{ $j }}:
                                                {{ $y->ticketLabel  }}
                                            </th>
                                        </tr>
                                        @for($x=1;$x<=5;$x++)
<?php
                                            // Check to see if there are any events for $x (this row)
                                            $s = EventSession::where([
                                                ['eventID', $event->eventID],
                                                ['confDay', $j],
                                                ['order', $x]
                                            ])->first();
                                            // As long as there are any sessions, the row will be displayed
?>
                                            @if($s !== null)
                                                <tr>
                                                    @foreach($tracks as $track)
<?php
                                                        $s = EventSession::where([
                                                            ['trackID', $track->trackID],
                                                            ['eventID', $event->eventID],
                                                            ['confDay', $j],
                                                            ['order', $x]
                                                        ])->first();

                                                        if($s !== null) {
                                                            $mySess = $s->sessionID;
                                                        }
?>
                                                        @if($s !== null)
                                                            @if($tracks->first() == $track || !$event->isSymmetric)

                                                                <td rowspan="1" style="text-align:left;">
                                                                    <nobr> {{ $s->start->format('g:i A') }} </nobr>
                                                                    &dash;
                                                                    <nobr> {{ $s->end->format('g:i A') }} </nobr>
                                                                </td>
                                                            @else

                                                            @endif
                                                            <td colspan="2" style="text-align:left; min-width:150px;
                                                                    width: {{ $width }}%; max-width: {{ $mw }}%;">
                                                                @if($s->maxAttendees > 0 && $s->regCount > $s->maxAttendees)
                                                                    <b>{{ $s->sessionName }}</b><br/>
                                                                    @include('v1.parts.tooltip', ['title' => "Maximum attendance reached.", 'c' => 'red'])
                                                                    {!! Form::radio('sess-'. $j . '-'.$x, $s->sessionID, false,
                                                                        $attributes=array('disabled', 'required', 'id' => 'sess-'. $j . '-'.$x .'-'. $mySess)) !!}
                                                                @else
                                                                    <b>{{ $s->sessionName }}</b><br/>
                                                                    {!! Form::radio('sess-'. $j . '-'.$x, $s->sessionID, false,
                                                                        $attributes=array('required', 'id' => 'sess-'. $j . '-'.$x .'-'. $mySess)) !!}
                                                                @endif

                                                                {{--  Need to connect to 'sess-'.$j.'-'.$x-1 and 'sess-'.$j.'-'.$x
                                                                      and have jquery set it to clicked and vice versa;
                                                                      if selection moves from x or x-1, it unselects the other
                                                                      if selection moves onto 1, it moves onto the other --}}
                                                            </td>
                                                        @else
                                                            @if($tracks->first() == $track || !$event->isSymmetric)
                                                                <td colspan="3" style="text-align:left;">
                                                            @else
                                                                <td colspan="2" style="text-align:left;">
                                                            @endif
<?php
                                                                    $t = EventSession::where([
                                                                        ['trackID', $track->trackID],
                                                                        ['eventID', $event->eventID],
                                                                        ['confDay', $j],
                                                                        ['order', $x - 1]
                                                                    ])->first();

                                                                    if($t !== null) {
                                                                        $myTess = $t->sessionID;
                                                                    }
?>
                                                                    @if($s->maxAttendees > 0 && $s->regCount > $s->maxAttendees)
                                                                        {!! Form::radio('sess-'. $j . '-'.$x, '', false,
                                                                            $attributes=array('disabled', 'required', 'id' => 'sess-'. $j . '-'.$x .'-x', 'style' => 'visibility:hidden;')) !!}
                                                                    @else
                                                                        {!! Form::radio('sess-'. $j . '-'.$x, '', false,
                                                                            $attributes=array('required', 'id' => 'sess-'. $j . '-'.$x .'-x', 'style' => 'visibility:hidden;')) !!}
                                                                    @endif
                                                                        <script>
                                                                        $(document).ready(function () {
                                                                            $("input:radio[name='{{ 'sess-'. $j . '-'.$x }}']").on('change', function () {
                                                                                if ($('#{{ 'sess-'. $j . '-'.$x.'-x' }}').is(":checked")) {
                                                                                    $('#{{ 'sess-'. $j . '-'.($x-1) .'-'. $myTess }}').prop('checked', 'checked');
                                                                                } else {
                                                                                    $('#{{ 'sess-'. $j . '-'.($x-1) .'-'. $myTess }}').removeAttr('checked');
                                                                                }
                                                                            });
                                                                            $("input:radio[name='{{ 'sess-'. $j . '-'.($x-1) }}']").on('change', function () {
                                                                                if ($('#{{ 'sess-'. $j . '-'.($x-1).'-' . $myTess }}').is(":checked")) {
                                                                                    $('#{{ 'sess-'. $j . '-'.($x) .'-x' }}').prop('checked', 'checked');
                                                                                } else {
                                                                                    $('#{{ 'sess-'. $j . '-'.($x) .'-x' }}').removeAttr('checked');
                                                                                }
                                                                            });
                                                                        });
                                                                    </script>
                                                                </td>
                                                            @endif
                                                            @endforeach
                                                </tr>
                                            @endif
                                        @endfor
                                    @endif  {{-- if included ticket --}}
                                @endfor  {{-- this closes confDays loop --}}
                            </table>
                        @endif  {{-- closes hasTracks loop --}}
                    </div>
                </div>

            @endfor  {{-- closes $rf loop --}}

            <div class="myrow col-md-12 col-sm-12" style="display: table-row; vertical-align: top;">
                <div class="col-md-2 col-sm-2" style="display: table-cell; text-align:center;">
                    <h1 class="fa fa-5x fa-dollar"></h1>
                </div>
                <div class="col-md-7 col-sm-7" style="display: table-cell;">
                    @if($rf->cost > 0 && $rf->status != 'Wait List')
                        <button id="payment" type="submit" data-toggle="modal" data-target="#stripe_modal"
                                class="card btn btn-primary btn-md">
                            <b>Pay Now by Credit Card</b>
                        </button>
                    @endif
                    <br />
                    <button id="nocard" type="submit" class="btn btn-success btn-sm">&nbsp;
                        @if($rf->cost > 0 && $rf->status != 'Wait List')
                            <b>{{ $rf->cost > 0 ? 'Pay by Cash/Check at Door' : 'Complete Registration' }}</b>
                        @elseif($rf->status == 'Wait List')
                            <b>Join the Wait List</b>
                        @else
                            <b>Complete Registration</b>
                        @endif
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
                                            class="fa fa-dollar"></i> {{ number_format($rf->cost, 2, '.', ',') }}</b>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
    @include('v1.parts.end_content')
@endsection

@section('scripts')
    <script>
        $('.card').on('click', function (e) {
            // Open Checkout with further options:
            e.preventDefault();
        });

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
                    }
?>
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
                    }
?>
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

@section('modals')
    @include('v1.modals.stripe', array('amt' => $rf->cost, 'rf' => $rf))
@endsection