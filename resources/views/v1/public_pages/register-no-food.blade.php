<?php
/**
 * Comment: Registration form for events that do not have foot... (Roundtables)
 * Created: 2/25/2017
 */
use Illuminate\Support\Facades\DB;
use App\Person;
use App\Location;
use App\Registration;
use Illuminate\Support\Facades\Auth;

if(Auth::check()) {
    $person       = Person::find(auth()->user()->id);
    $registration = new Registration;
} else {
    $person       = new Person;
    $registration = new Registration;
}
$loc = Location::find($event->locationID);

$prefixes     = DB::table('prefixes')->select('prefix', 'prefix')->get();
$prefix_array = ['' => 'Prefix'] + $prefixes->pluck('prefix', 'prefix')->toArray();

$industries     = DB::table('industries')->select('industryName', 'industryName')->get();
$industry_array = ['' => 'Select Industry'] + $industries->pluck('industryName', 'industryName')->toArray();

$chapters = DB::table('organization')->where('orgID', $event->orgID)->select('nearbyChapters')->first();
$array    = explode(',', $chapters->nearbyChapters);

foreach($array as $chap) {
    $affiliation_array[$chap] = $chap;
}

// Determine if Early Bird Pricing should be in effect
$today = Carbon\Carbon::now();
if((!$ticket->earlyBirdEndDate === null) && $ticket->earlyBirdEndDate->gt($today)){
    $earlymbr = number_format($ticket->memberBasePrice - ($ticket->memberBasePrice * $ticket->earlyBirdPercent / 100), 2, '.', ',');
    $earlynon = number_format($ticket->nonmbrBasePrice - ($ticket->nonmbrBasePrice * $ticket->earlyBirdPercent / 100), 2, '.', ',');
} else {
    $earlymbr = number_format($ticket->memberBasePrice, 2, '.', ',');
    $earlynon = number_format($ticket->nonmbrBasePrice, 2, '.', ',');
}
?>
@extends('v1.layouts.no-auth')

@section('content')

    @include('v1.parts.start_content', ['header' => "$event->eventName", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    @if($errors->any())
        <h4>{{$errors->first()}}</h4>
    @endif

    <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-12">
            {{ $event->eventStartDate->format('n/j/Y g:i A') }} - {{ $event->eventEndDate->format('n/j/Y g:i A') }}<br>
            {{ $loc->locName }}<br>
            {{ $loc->addr1 }} <i class="fa fa-circle fa-tiny-circle"></i> {{ $loc->city }}
            , {{ $loc->state }} {{ $loc->zip }}
        </div>
        <div class="col-md-3 col-sm-3 col-xs-12">

        </div>
        <div class="col-md-3 col-sm-3 col-xs-12">
            @if(!Auth::check())
                <button class='btn btn-primary btn-sm' id='loginButton' data-toggle="modal" data-target="#login_modal">
                    <i class='fa fa-user'>&nbsp;</i> Have an account?
                    Login
                </button>
            @endif
        </div>
    </div>

    <div class="flash-message">
        @foreach (['danger', 'warning', 'success', 'info'] as $msg)
            @if(Session::has('alert-' . $msg))
                <p>&nbsp;</p>
                <p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }} <a href="#" class="close"
                                                                                         data-dismiss="alert"
                                                                                         aria-label="close">&times;</a>
                </p>
            @endif
        @endforeach
    </div>

    {!! Form::model($person->toArray() + $registration->toArray(), ['route' => ['register_step2', $event->eventID], 'method' => 'post']) !!}
    {!! Form::hidden('eventID', $event->eventID, array('id' => 'eventID')) !!}
    {!! Form::hidden('ticketID', $ticket->ticketID, array('id' => 'ticketID')) !!}
    {!! Form::hidden('percent', 0, array('id' => 'i_percent')) !!}
    {!! Form::hidden('flatamt', 0, array('id' => 'i_flatamt')) !!}
    {!! Form::hidden('total', 0, array('id' => 'i_total')) !!}
    {!! Form::hidden('quantity', $quantity, array('id' => 'quantity')) !!}

    @for($i=1; $i<=$quantity; $i++)
        {!! Form::hidden('sub'.$i, 0, array('id' => 'sub'.$i)) !!}
        {!! Form::hidden('cost'.$i, Auth::check() ? $earlymbr : $earlynon, array('id' => 'cost'.$i)) !!}
        <div class="clearfix"><p>&nbsp;</p></div>
        <table id="ticket_head" class="table table-striped">
            <th colspan="3" style="text-align: left; vertical-align: middle;" class="col-md-6 col-sm-6 col-xs-12">
                <span id="ticket_type{{ $i }}">#{{ $i }} @if(Auth::check()) MEMBER @else NON-MEMBER @endif
                    TICKET: </span> {{ $ticket->ticketLabel }} </th>
            <th colspan="3" style="text-align: right;" class="col-md-6 col-sm-6 col-xs-12">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="col-md-3 col-sm-3 col-xs-12"></div>
                    @if($i==1)
                        <div class="col-md-6 col-sm-6 col-xs-12" style="text-align: right; vertical-align: middle;">
                            {!! Form::text('discount_code', $discount_code ?: old('$discount_code'),
                                array('id' => 'discount_code', 'size' => '25', 'class' => 'control', 'placeholder' => 'Enter discount code')) !!}
                        </div>
                        <div class="col-md-3 col-sm-3 col-xs-12" style="text-align: left; vertical-align: middle;">
                            <a class="btn btn-xs btn-primary" id="btn-apply">Apply</a></div>
                    @else
                        <div class="col-md-6 col-sm-6 col-xs-12"></div>
                        <div class="col-md-3 col-sm-3 col-xs-12"></div>
                    @endif
                </div>
            </th>
            </tr>
            <tr>
                <td style="width: 11%"><b>Ticket Cost:</b> <i class="fa fa-dollar"></i> <span id="tcost{{ $i }}">
                        @if(Auth::check())
                                @if(Auth::check())
                                    {{ $earlymbr }}
                                @else
                                    {{ $earlynon }}
                                @endif
                        @else
                            @if($event->earlyBirdEndDate && (time() - strtotime($event->earlyBirdEndDate) < 0))
                                {{ $ticket->nonmbrBasePrice - ($ticket->nonmbrBasePrice * $ticket->earlyBirdPercent) }}
                            @else
                                {{ $ticket->nonmbrBasePrice }}
                            @endif
                        @endif
                        </span></td>
                <td colspan="2" style="width: 22%; text-align: right; vertical-align: middle;"><b>Discount Applied:</b>
                </td>
                <td colspan="2" style="width: 22%; text-align: left; vertical-align: middle;"><span
                            class="status_msg">---</span></td>
                <td style="width: 11%; text-align: right;"><b>Final Cost:</b> <i class="fa fa-dollar"></i>
                    <span id="final{{ $i }}">---</span></td>
            </tr>
        </table>
        <table class="table table-striped">
            <tr>
                <th style="width:20%;">Prefix</th>
                <th style="width:20%;">First Name<sup>*</sup></th>
                <th style="width:20%;">Middle Name</th>
                <th style="width:20%;">Last Name<sup>*</sup></th>
                <th style="width:20%;">Suffix</th>
            </tr>
            <tr>
                @if($i==1)
                    <td>{!! Form::select("prefix", $prefix_array, old("prefix"), array('class' => 'form-control')) !!}</td>
                @else
                    <td>{!! Form::select("prefix_$i", $prefix_array, old("prefix_$i"), array('class' => 'form-control')) !!}</td>
                @endif
                @if($i==1)
                    <td>{!! Form::text("firstName", old("firstName"), array('class' => 'form-control',
                    Auth::check() ? 'disabled' : '', 'required')) !!}</td>
                @else
                    <td>{!! Form::text("firstName_$i", old("firstName_$i"), array('class' => 'form-control', 'required')) !!}</td>
                @endif
                @if($i==1)
                    <td>{!! Form::text("middleName", old("middleName"), array('class' => 'form-control')) !!}</td>
                @else
                    <td>{!! Form::text("middleName_$i", old("middleName_$i"), array('class' => 'form-control')) !!}</td>
                @endif
                @if($i==1)
                    <td>{!! Form::text("lastName", old("lastName"), array('class' => 'form-control',
                    Auth::check() ? 'disabled' : '', 'required')) !!}</td>
                @else
                    <td>{!! Form::text("lastName_$i", old("lastName_$i"), array('class' => 'form-control', 'required')) !!}</td>
                @endif
                @if($i==1)
                    <td>{!! Form::text("suffix", old("suffix"), array('class' => 'form-control')) !!}</td>
                @else
                    <td>{!! Form::text("suffix_$i", old("suffix_$i"), array('class' => 'form-control')) !!}</td>
                @endif
            </tr>
            <tr>
                <th style="width:20%;">Preferred Name<sup>*</sup></th>
                <th style="width:20%;">Industry</th>
                <th style="width:20%;">Company</th>
                <th style="width:20%;">Title</th>
                <th style="width:20%;">Email Address<sup>*</sup></th>
            </tr>
            <tr>
                @if($i==1)
                    <td>{!! Form::text("prefName", old("prefName"), array('class' => 'form-control', 'required')) !!}</td>
                @else
                    <td>{!! Form::text("prefName_$i", old("prefName_$i"), array('class' => 'form-control', 'required')) !!}</td>
                @endif
                @if($i==1)
                    <td>{!! Form::select("indName", $industry_array, old("indName_$i"), array('class' => 'form-control')) !!}</td>
                @else
                    <td>{!! Form::select("indName_$i", $industry_array, old("indName_$i"), array('class' => 'form-control')) !!}</td>
                @endif
                @if($i==1)
                    <td>{!! Form::text("compName", old("compName"), array('class' => 'form-control')) !!}</td>
                @else
                    <td>{!! Form::text("compName_$i", old("compName_$i"), array('class' => 'form-control')) !!}</td>
                @endif
                @if($i==1)
                    <td>{!! Form::text("title", old("title"), array('class' => 'form-control')) !!}</td>
                @else
                    <td>{!! Form::text("title_$i", old("title_$i"), array('class' => 'form-control')) !!}</td>
                @endif
                @if($i==1)
                    <td>{!! Form::email("login", old("login"), array('class' => 'form-control', Auth::check() ? 'onfocus="blur();"' : '', 'required')) !!}</td>
                @else
                    <td>{!! Form::email("login_$i", old("login_$i"), array('class' => 'form-control', 'required')) !!}</td>
                @endif
            </tr>

            <tr>
                <th style="width:20%;">Is this your first event?</th>
                <th style="width:20%;">What future event topics would interest you?</th>
                <th style="width:20%;">Do you authorize PMI MassBay to submit your PDUs for you?</th>
                <td colspan="2" rowspan="4">
                    <div width="100%">
                        <img src="/images/roundtable.jpg" width="100%"/>
                    </div>
                </td>
            </tr>
            <tr>
                @if($i==1)
                    <td>
                        <div class="container row col-sm-3">
                            <div class="col-sm-1">No</div>
                            <div class="col-sm-2"> {!! Form::checkbox("isFirstEvent", '1', false, array('class' => 'flat js-switch')) !!} </div>
                            <div class="col-sm-1">Yes</div>
                        </div>
                    </td>
                @else
                    <td>
                        <div class="container row col-sm-3">
                            <div class="col-sm-1">No</div>
                            <div class="col-sm-2"> {!! Form::checkbox("isFirstEvent_$i", '1', false, array('class' => 'flat js-switch')) !!} </div>
                            <div class="col-sm-1">Yes</div>
                        </div>
                    </td>
                @endif
                @if($i==1)
                    <td>{!! Form::text("eventTopics", old("eventTopics"), array('class' => 'form-control')) !!}</td>
                @else
                    <td>{!! Form::text("eventTopics_$i", old("eventTopics_$i"), array('class' => 'form-control')) !!}</td>
                @endif
                @if($i==1)
                    <td>
                        <div class="container row col-sm-3">
                            <div class="col-sm-1">No</div>
                            <div class="col-sm-2"> {!! Form::checkbox("isAuthPDU", '1', true, array('class' => 'flat js-switch')) !!} </div>
                            <div class="col-sm-1">Yes</div>
                        </div>
                    </td>
                @else
                    <td>
                        <div class="container row col-sm-3">
                            <div class="col-sm-1">No</div>
                            <div class="col-sm-2"> {!! Form::checkbox("isAuthPDU_$i", '1', true, array('class' => 'flat js-switch')) !!} </div>
                            <div class="col-sm-1">Yes</div>
                        </div>
                    </td>
                @endif
            </tr>

            <tr>
                <th style="width:20%;">List any questions for the speaker(s).</th>
                <td style="width:20%;"><b>Please select your chapter affiliation(s).<sup>*</sup></b><br>
                    <small>Ctrl-Click to select > 1 choice.</small>
                </td>
                <th style="width:20%;">Do you want to be added to a participant roster?</th>
            </tr>

            <tr>
                @if($i==1)
                    <td>{!! Form::textarea("eventQuestion", old("eventQuestion"), $attributes = array('class'=>'form-control', 'rows' => '3')) !!}</td>
                @else
                    <td>{!! Form::textarea("eventTopics_$i", old("eventTopics_$i"), $attributes = array('class'=>'form-control', 'rows' => '3')) !!}</td>
                @endif
                @if($i==1)
                    <td>{!! Form::select('affiliation[]', $affiliation_array, old("affiliation") ?: reset($affiliation_array), array('class' => 'form-control', 'multiple' => 'multiple', 'required')) !!}</td>
                @else
                    <td>{!! Form::select('affiliation'."_$i".'[]', $affiliation_array, old("affiliation_$i") ?: reset($affiliation_array), array('class' => 'form-control', 'multiple' => 'multiple', 'required')) !!}</td>
                @endif
                @if($i==1)
                    <td>
                        <div class="container row col-sm-3">
                            <div class="col-sm-1">No</div>
                            <div class="col-sm-2">{!! Form::checkbox("canNetwork", '1', true, array('class' => 'flat js-switch')) !!}</div>
                            <div class="col-sm-1">Yes</div>
                        </div>
                    </td>
                @else
                    <td>
                        <div class="container row col-sm-3">
                            <div class="col-sm-1">No</div>
                            <div class="col-sm-2"> {!! Form::checkbox("canNetwork_$i", '1', true, array('class' => 'flat js-switch')) !!} </div>
                            <div class="col-sm-1">Yes</div>
                        </div>
                    </td>
                @endif
            </tr>
        </table>
    @endfor

    <span id="discount" style="display: none;">0</span>
    <span id="flatdisc" style="display: none;">0</span>
    <table class="table table-striped">
        <tr>
            <th style="text-align: right; width: 85%; vertical-align: top;">Total
            </td>
            <th style="text-align: left; vertical-align: top;"><i class="fa fa-dollar"></i> <span id="total">0.00</span>
            </td>
        </tr>
    </table>

    <div class="col-md-9 col-sm-9 col-xs-12"></div>
    <div class="col-md-3 col-sm-3 col-xs-12">
        {!! Form::submit('Next: Review & Payment', array('class' => 'btn btn-primary')) !!}
    </div>
    @include('v1.parts.end_content')
    {!! Form::close() !!}
@endsection


@section('scripts')
    @if(!empty(Session::get('modal_error')) && !Auth::check() && Session::get('modal_error') == 1)
        <script>
            $(document).ready(function () {
                $('#login_modal').modal('show');
            });
        </script>
    @endif
    <script src="https://www.google.com/recaptcha/api.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <script>
        $(document).ready(function () {

            var percent = $('#discount').text();
            var flatAmt = $('#flatdisc').text();
            var subtotal = 0;

            @for($i=1;$i<=$quantity; $i++)
                var tc{{ $i }} = $('#tcost{{ $i }}').text() * 1;
                var newval{{ $i }} = tc{{ $i }} * 1;
                $('#final{{ $i }}').text(tc{{ $i }});
                subtotal += newval{{ $i }} * 1;
                $("#sub{{ $i }}").val(newval{{ $i }}.toFixed(2));
            @endfor

            $('#total').text(subtotal.toFixed(2));
            $('#i_total').val(subtotal.toFixed(2));

            if (!FieldIsEmpty($("#discount_code"))) {
                validateCode({{ $event->eventID }});
            }

            $('#btn-apply').on('click', function (e) {
                e.preventDefault();
                validateCode({{ $event->eventID }});
            });

            function validateCode(eventID) {
                var codeValue = $("#discount_code").val();
                if (FieldIsEmpty(codeValue)) {
                    var message = '<span><i class="fa fa-warning fa-2x text-warning mid_align">&nbsp;</i>Enter a discount code.</span>';
                    $('.status_msg').html(message).fadeIn(500).fadeOut(3000);

                } else {
                    $.ajax({
                        type: 'POST',
                        cache: false,
                        async: true,
                        url: '{{ env('APP_URL') }}{{ env('APP_URL') }}/discount/' + eventID,
                        dataType: 'json',
                        data: {
                            event_id: eventID,
                            discount_code: codeValue
                        },
                        beforeSend: function () {
                            $('.status_msg').html('');
                            $('.status_msg').fadeIn(0);
                        },
                        success: function (data) {
                            console.log(data);
                            var result = eval(data);
                            $('.status_msg').html(result.message).fadeIn(0);
                            $('#discount').text(result.percent);
                            $('#flatdisc').text(result.flatAmt);

                            percent = result.percent;
                            flatAmt = result.flatAmt;
                            $('#i_percent').val(percent);
                            $('#i_flatamt').val(flatAmt);
                            subtotal = 0;

                            if(percent>0) {
                                @for($i=1;$i<=$quantity; $i++)
                                    newval{{ $i }} = (tc{{ $i }} - (tc{{ $i }} * percent / 100));
                                $('#final{{ $i }}').text(newval{{ $i }}.toFixed(2));
                                subtotal += newval{{ $i }} * 1.00;
                                $("#sub{{ $i }}").val(newval{{ $i }}.toFixed(2));
                                @endfor
                            } else {
                                newval{{ 1 }} = ((tc{{ 1 }} * 1) - (flatAmt * 1));
                                if(newval1 < 0) newval1 = 0;
                                    $('#final{{ 1 }}').text(newval{{ 1 }}.toFixed(2));
                                    subtotal += newval{{ 1 }} * 1;
                                    $("#sub{{ 1 }}").val(newval{{ 1 }}.toFixed(2));

                                @for($i=2;$i<=$quantity; $i++)
                                    newval{{ $i }} = tc{{ $i }} * 1;
                                    if(newval{{ $i }} < 0) newval{{ $i }} = 0;
                                    $('#final{{ $i }}').text(newval{{ $i }}.toFixed(2));
                                    subtotal += newval{{ $i }} * 1;
                                    $("#sub{{ $i }}").val(newval{{ $i }}.toFixed(2));
                                @endfor
                            }

                            $('#total').text(subtotal.toFixed(2));
                            $('#i_total').val(subtotal.toFixed(2));
                        },
                        error: function (data) {
                            console.log(data);
                            var result = eval(data);
                            $('.status_msg').html(result.message).fadeIn(0);
                            bootbox("error");
                        }
                    });
                }
            };
        });
    </script>

    @if(!Auth::check())
        <script>
            $(document).ready(function () {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: 'GET',
                    cache: false,
                    async: true,
                    url: '{{ env('APP_URL') }}/password/forgotmodal',
                    dataType: 'json',
                    success: function (data) {
                        //alert("success");
                        console.log(data);
                        var result = eval(data);
                        $('#forgot-modal-body').html(result.message);
                    },
                    error: function (xhr, status, error) {
                        console.log(xhr);
                        //alert("An AJAX error occured: " + status + "\nError: " + error);
                    }
                });
            });
        </script>
    @endif
@endsection

@section('modals')
    @if(!Auth::check())
        @include('v1.modals.login')
        @include('v1.modals.forgot')
    @endif
@endsection
