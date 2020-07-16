@php
/**
 * Comment: Confirmation screen post and Stripe Payment Processing
 * Created: 3/12/2017
 *
 * @var Event $event
 * @var Org $org
 * @var RegFinance $rf
 *
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

if ($event->eventTypeID == 5) { // This is a regional event so do that instead
    //$chapters = $org->regionChapters; DB::table('organization')->where('orgID', $event->orgID)->select('regionChapters')->first();
    $array = explode(',', $org->regionChapters);
} else {
    //$chapters = DB::table('organization')->where('orgID', $event->orgID)->select('nearbyChapters')->first();
    $array = explode(',', $org->nearbyChapters);
}

if($org->canSubmitPDU !== null){
    $PDU_org_types = explode(',', $org->canSubmitPDU);
} else {
    $PDU_org_types = [];
}

$i = 0;
foreach ($array as $chap) {
    $i++;
    $chap = trim($chap);
    $affiliation_array[$i] = $chap;
}

if ($event->isSymmetric && $event->hasTracks) {
    $columns = ($event->hasTracks * 2) + 1;
    $width = number_format(85 / $event->hasTracks, 0, '', '');
    $mw = number_format(90 / $event->hasTracks, 0, '', '');
} elseif ($event->hasTracks) {
    $columns = $event->hasTracks * 3;
    $width = number_format(80 / $event->hasTracks, 0, '', '');
    $mw = number_format(85 / $event->hasTracks, 0, '', '');
}
$rfp = $rf->person;

@endphp

@extends('v1.layouts.no-auth')
@section('content')
    @include('v1.parts.start_content', ['header' => trans('messages.headers.reg_con'), 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    {!! Form::open(['url' => env('APP_URL').'/complete_registration/'.$rf->regID, 'method' => 'patch', 'id' => 'complete_registration', 'data-toggle' => 'validator']) !!}

    <div class="whole">

        <div style="float: right;" class="col-lg-4 hidden-md hidden-sm hidden-xs">
            <img style="opacity: .25;" src="{{ env('APP_URL') }}/images/meeting.jpg" width="100%" height="90%">
        </div>
        <div class="left col-lg-8 col-md-12 col-sm-12 col-xs-12">
            <div class="myrow col-lg-12 col-xs-12">

                <div class="col-md-2 col-sm-2" style="text-align:center;">
                    <h1 class="far fa-5x fa-calendar-alt"></h1>
                </div>
                <div class="col-md-6 col-sm-6">
                    <h2><b>{{ $event->eventName }}</b></h2>
                    <div style="margin-left: 10px;">
                        @include('v1.parts.location_display', ['loc' => $loc, 'event' => $event, 'time' => 1])
                    </div>
                    <br />
                </div>
                <div class="col-md-3 col-sm-3 col-md-offset-1 col-sm-offset-1" style="text-align: right;">
                    <p></p>

                    @if($rf->cost > 0 && $rf->status != 'wait')
                        @include('v1.parts.stripe_pay_button', array('id' => 'payment'))
                    @endif

                    @if($event->acceptsCash)
                        <button id="nocard" type="submit" class="btn btn-success btn-sm">&nbsp;
                            @if($rf->cost > 0 && $rf->status != 'wait')
                                <b>{{ $rf->cost > 0 ? trans('messages.buttons.door') : trans('messages.buttons.comp_reg') }}</b>
                            @elseif($rf->status == 'wait')
                                <b>@lang('messages.buttons.wait')</b>
                            @else
                                <b>@lang('messages.buttons.comp_reg')</b>
                            @endif
                        </button>
                    @else
                        @if($rf->status == 'wait')
                            <button id="nocard" type="submit" class="btn btn-success btn-sm">&nbsp;
                                <b>@lang('messages.buttons.wait')</b>
                            </button>
                        @elseif($rf->cost == 0)
                            <button id="nocard" type="submit" class="btn btn-success btn-sm">&nbsp;
                                <b>@lang('messages.buttons.comp_reg')</b>
                            </button>
                        @endif
                    @endif

                </div>
            </div>
            @if($show_pass_fields)
                <div class="col-md-10 col-sm-10 col-sm-offset-2 coll-md-offset-2">
                    <div class="col-sm-6 form-group">
                        {!! Form::password('password', array('required', 'class' => 'form-control input-sm', 'placeholder' => trans('messages.instructions.pw_set'))) !!}
                    </div>
                    <div class="col-sm-6 form-group">
                        {!! Form::password('password_confirmation', array('required', 'class' => 'form-control input-sm', 'placeholder' => trans('messages.instructions.pw_conf'))) !!}
                    </div>
                </div>
            @endif

            @if($bought_for_other)
                <div class="col-md-10 col-sm-10 col-sm-offset-2 coll-md-offset-2">
                    <div class="col-xs-12">
                        <b class="red">@lang('messages.headers.purchased_by'):</b> {!! $rfp->showFullName() !!}
                    </div>
                </div>
            @endif

            @foreach($regs as $reg)
@php
                $tcount++;
                $person = Person::find($reg->personID);
                $ticket = Ticket::find($reg->ticketID);
@endphp

                <div class="myrow col-md-12 col-sm-12">
                    <div class="col-md-2 col-sm-2" style="text-align:center;">
                        <h1 class="far fa-5x fa-user"></h1>
                    </div>
                    <div class="col-md-10 col-sm-10">
                        <table class="table table-bordered table-condensed table-striped jambo_table">
                            <thead>
                            <tr>
                                <th colspan="4" style="text-align: left;">{{ strtoupper($reg->membership) }}
                                    {{ strtoupper(__('messages.fields.ticket')) }}:
                                    #{{ $tcount }}</th>
                            </tr>
                            </thead>
                            <tr>
                                <th style="text-align: left; color:darkgreen;">@lang('messages.fields.ticket')</th>
                                <th style="text-align: left; color:darkgreen;">@lang('messages.fields.oCost')</th>
                                <th style="text-align: left; color:darkgreen;">@lang('messages.fields.disc')</th>
                                <th style="text-align: left; color:darkgreen;">@lang('messages.fields.subtotal')</th>
                            </tr>
                            <tr>
                                <td style="text-align: left;">{{ $ticket->ticketLabel }}</td>

                                <td style="text-align: left;">@lang('messages.symbols.cur')
                                    @if($reg->membership == 'member')
                                        {{ number_format($ticket->memberBasePrice, 2, ".", ",") }}
                                    @else
                                        {{ number_format($ticket->nonmbrBasePrice, 2, ".", ",") }}
                                    @endif
                                </td>

                                @if(($ticket->earlyBirdEndDate !== null) && $ticket->earlyBirdEndDate->gt($today))
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
                                <td style="text-align: left;">@lang('messages.symbols.cur')
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
                                        <a id="prefix-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                           data-value="{{ $person->prefix }}"
                                           data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a>
                                    @endif
                                    @if($reg->membership == 'nonmbr')
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
                                    @if($reg->membership == 'nonmbr')
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
                                    <br />
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
                                                @lang('messages.headers.employed')
                                            @endif
                                            @lang('messages.headers.at') <a id="compName-{{ $tcount }}"
                                                                            data-pk="{{ $person->personID }}"
                                                                            data-value="{{ $person->compName }}"
                                                                            data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a>
                                        @else
                                            @if($person->title !== null)
                                                <a id="title-{{ $tcount }}" data-pk="{{ $person->personID }}"
                                                   data-value="{{ $person->title }}"
                                                   data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a>
                                            @else($person->indName !== null)
                                                @lang('messages.headers.employed')
                                            @endif
                                            @if($person->indName !== null)
                                                @lang('messages.headers.inthe') <a id="indName-{{ $tcount }}"
                                                                                   data-pk="{{ $person->personID }}"
                                                                                   data-value="{{ $person->indName }}"
                                                                                   data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a> @lang('messages.headers.ind')
                                                <br />
                                            @endif
                                        @endif
                                        @if($person->affiliation)
                                            <br />@lang('messages.headers.aff_with'): <a id="affiliation-{{ $tcount }}"
                                                                                        data-pk="{{ $person->personID }}"
                                                                                        data-value="{{ $person->affiliation }}"
                                                                                        data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a>
                                        @endif
                                    @endif
                                </td>
                                <td colspan="2" style="text-align: left;">
                                    @if($reg->isFirstEvent)
                                        <b>@lang('messages.headers.isFirst')</b> <a id="isFirstEvent-{{ $tcount }}"
                                                                                    data-pk="{{ $reg->regID }}"
                                                                                    data-value="{{ $reg->isFirstEvent }}"
                                                                                    data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a>
                                        <br />
                                    @endif

                                    <b>@lang('messages.headers.roster_add'):</b> <a id="canNetwork-{{ $tcount }}"
                                                                                    data-pk="{{ $reg->regID }}"
                                                                                    data-value="{{ $reg->canNetwork }}"
                                                                                    data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a><br />

                                    <b>@lang('messages.headers.certs'):</b> <br /><a id="certifications-{{ $tcount }}"
                                                                               data-pk="{{ $person->personID }}"
                                                                               data-value="{{ $person->certifications }}"
                                                                               data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a><br />

                                    @if(in_array($event->eventTypeID, $PDU_org_types))
                                    @include('v1.parts.tooltip', ['title' => trans('messages.fields.isAuthPDU', array('org' => $org->orgName))])
                                    <b>@lang('messages.fields.pdu_sub'):</b> <a id="isAuthPDU-{{ $tcount }}"
                                                                                data-pk="{{ $reg->regID }}"
                                                                                data-value="{{ $reg->isAuthPDU }}"
                                                                                data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a><br />
                                    @endif

                                    @if($reg->eventQuestion)
                                        <p><b>@lang('messages.fields.spk_question'):</b> <a
                                                    id="eventQuestion-{{ $tcount }}"
                                                    data-pk="{{ $reg->regID }}"
                                                    data-value="{{ $reg->eventQuestion }}"
                                                    data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a>
                                        </p>
                                    @endif

                                    @if($reg->eventTopics)
                                        <p><b>@lang('messages.fields.future_topics'):</b><br /> <a
                                                    id="eventTopics-{{ $tcount }}"
                                                    data-pk="{{ $reg->regID }}"
                                                    data-value="{{ $reg->eventTopics }}"
                                                    data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a>
                                        </p>
                                    @endif

                                    @if($reg->cityState)
                                        <br /><b>@lang('messages.fields.commute'):</b> <a id="cityState-{{ $tcount }}"
                                                                                         data-pk="{{ $reg->regID }}"
                                                                                         data-value="{{ $reg->cityState }}"
                                                                                         data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a></br>
                                    @endif

                                    @if($reg->specialNeeds)
                                        <b>@lang('messages.fields.spc_needs'):</b> <a id="specialNeeds-{{ $tcount }}"
                                                                                      data-pk="{{ $reg->regID }}"
                                                                                      data-value="{{ $reg->specialNeeds }}"
                                                                                      data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a>
                                        <br />
                                    @endif

                                    @if($reg->allergenInfo)
                                        <b>@lang('messages.fields.diet_info'):</b> <a id="allergenInfo-{{ $tcount }}"
                                                                                      data-pk="{{ $reg->regID }}"
                                                                                      data-value="{{ $reg->allergenInfo }}"
                                                                                      data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a>
                                        <br />
                                        @if($reg->eventNotes)
                                            <a id="eventNotes-{{ $tcount }}" data-pk="{{ $reg->regID }}"
                                               data-value="{{ $reg->eventNotes }}"
                                               data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a>
                                        @endif
                                    @elseif($reg->eventNotes)
                                        <b>@lang('messages.fields.other'):</b> <a id="eventNotes-{{ $tcount }}"
                                                                                  data-pk="{{ $reg->regID }}"
                                                                                  data-value="{{ $reg->eventNotes }}"
                                                                                  data-url="{{ env('APP_URL') }}/reg_verify/{{ $reg->regID }}"></a>
                                    @endif

                                </td>
                            </tr>
                        </table>

                        {{-- Display session selection stuff if sessions are attached to the ticket attached to $reg --}}

                        @if($event->hasTracks > 0 && $ticket->has_sessions())
                            @include('v1.parts.session_bubbles',
                                ['event' => $rf->event, 'ticket' => $reg->ticket, 'rf' => $rf,
                                 'reg' => $reg, 'suppress' => 1, 'registering' => $registering])
                        @endif
                    </div>
                </div>

            @endforeach  {{-- closes $rf loop --}}

            <div class="myrow col-md-12 col-sm-12" style="display: table-row; vertical-align: top;">
                <div class="col-md-2 col-sm-2" style="display: table-cell; text-align:center;">
                    <h1 @lang('messages.symbols.cur_class_5x')></h1>
                </div>
                <div class="col-md-7 col-sm-7" style="display: table-cell;">
                    @if($rf->cost > 0 && $rf->status != 'wait')
                        @include('v1.parts.stripe_pay_button', array('id' => 'payment'))
                        <br />
                    @endif
                    @if($event->acceptsCash)
                        <button id="nocard" type="submit" class="btn btn-success btn-sm">&nbsp;
                            @if($rf->cost > 0 && $rf->status != 'wait')
                                <b>{{ $rf->cost > 0 ? trans('messages.buttons.door') : trans('messages.buttons.comp_reg') }}</b>
                            @elseif($rf->status == 'wait')
                                <b>@lang('messages.buttons.wait')</b>
                            @else
                                <b>@lang('messages.buttons.comp_reg')</b>
                            @endif
                        </button>
                    @else
                            @if($rf->cost == 0)
                                <button id="nocard" type="submit" class="btn btn-success btn-sm">&nbsp;
                                <b>@lang('messages.buttons.comp_reg')</b>
                                </button>
                            @elseif($rf->status == 'wait')
                                <button id="nocard" type="submit" class="btn btn-success btn-sm">&nbsp;
                                <b>@lang('messages.buttons.wait')</b>
                                </button>
                            @endif
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
                                <b>@lang('messages.symbols.cur') {{ number_format($rf->cost, 2, '.', ',') }}</b>
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
                    foreach ($prefixes as $row) {
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
                    foreach ($industries as $row) {
                        $string .= "{ value: '" . $row->industryName . "' , text: '" . $row->industryName . "' },";
                    }
?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });

            $("#compName-{{ $i }}").editable({type: 'text'});
            $("#title-{{ $i }}").editable({type: 'text', emptytext: 'Title'});
            $("#chapterRole-{{ $i }}").editable({type: 'text', emptytext: 'Chapter Role'});
            $("#login-{{ $i }}").editable({type: 'text'});

            $('#affiliation-{{ $i }}').editable({
                type: 'checklist',
                source: [
<?php
                    for ($j = 1; $j <= count($affiliation_array); $j++) {
                        $string .= "{ value: '" . $affiliation_array[$j] . "' , text: '" . $affiliation_array[$j] . "' },";
                    }
?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });

            $('#certifications-{{ $i }}').editable({
                type: 'checklist',
                source: [
<?php
                    foreach ($cert_array as $row) {
                        $string .= "{ value: '" . $row->certification . "' , text: '" . $row->certification . "' },";
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

            $("#isFirstEvent-{{ $i }}").editable({
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
                    foreach ($allergen_array as $x) {
                        $string .= "{ value: '" . $x . "' , text: '" . $x . "' },";
                    }
?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });

            @endfor
        });
    </script>
@endsection

@section('modals')
    @if($rf->cost > 0)
    @include('v1.modals.stripe', array('amt' => $rf->cost, 'rf' => $rf))
    @endif
@endsection