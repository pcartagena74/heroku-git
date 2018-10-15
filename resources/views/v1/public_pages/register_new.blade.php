<?php
/**
 * Comment: Registration form - No longer in use. See varTKT_register
 * Created: 2/25/2017
 */

use Illuminate\Support\Facades\DB;
use App\Person;
use App\OrgPerson;
use App\Location;
use App\Registration;
use Illuminate\Support\Facades\Auth;

$org = \App\Org::find($event->orgID);
if (Auth::check()) {
    $person = Person::find(auth()->user()->id);
    $op = OrgPerson::where('personID', $person->personID)->first();
    $registration = new Registration;
    if ($person->is_member($event->orgID)) {
        $isMember = 1;
    } else {
        $isMember = 0;
    }
} else {
    $person = new Person;
    $op = new OrgPerson;
    $registration = new Registration;
    $isMember = 0;
}
$loc = Location::find($event->locationID);

$prefixes = DB::table('prefixes')->select('prefix', 'prefix')->get();
$prefix_array = ['' => trans('messages.fields.prefixes.select')] +
                $prefixes->pluck('prefix', 'prefix')->map(function($item, $key) {
                    return trans('messages.fields.prefixes.'.$item);
                })->toArray();

$industries = DB::table('industries')->select('industryName', 'industryName')->orderBy('industryName')->get();
$industry_array = ['' => trans('messages.fields.industries.select')] +
    $industries->pluck('industryName', 'industryName')->map(function($item, $key) {
        return trans('messages.fields.industries.'.$item);
    })->toArray();

$allergens = DB::table('allergens')->select('allergen', 'allergen')->get();
$allergen_array = $allergens->pluck('allergen', 'allergen')->toArray();

if ($event->eventTypeID == 5) { // This is a regional event so do that instead
    $chapters = DB::table('organization')->where('orgID', $event->orgID)->select('regionChapters')->first();
    $array = explode(',', $chapters->regionChapters);
} else {
    $chapters = DB::table('organization')->where('orgID', $event->orgID)->select('nearbyChapters')->first();
    $array = explode(',', $chapters->nearbyChapters);
}

$i = 0;
foreach ($array as $chap) {
    $i++;
    $chap = trim($chap);
    $affiliation_array[$chap] = $chap;
}

// Determine if Early Bird Pricing should be in effect
$today = Carbon\Carbon::now();
if ($ticket->valid_earlyBird()) {
    $earlymbr = number_format($ticket->memberBasePrice - ($ticket->memberBasePrice * $ticket->earlyBirdPercent / 100), 2, '.', ',');
    $earlynon = number_format($ticket->nonmbrBasePrice - ($ticket->nonmbrBasePrice * $ticket->earlyBirdPercent / 100), 2, '.', ',');
} else {
    $earlymbr = number_format($ticket->memberBasePrice, 2, '.', ',');
    $earlynon = number_format($ticket->nonmbrBasePrice, 2, '.', ',');
}

$experience_choices = [
    '0' => '0 ' . trans('messages.fields.years'),
    '1-4' => '1-4 ' . trans('messages.fields.years'),
    '5-9' => '5-9 ' . trans('messages.fields.years'),
    '10-14' => '10-14 ' . trans('messages.fields.years'),
    '15-19' => '15-19 ' . trans('messages.fields.years'),
    '20+' => '20+ ' . trans('messages.fields.years'),
];

//var_dump(Session::all());
?>
@extends('v1.layouts.no-auth')

@section('content')
<h1>This One</h1>
    @include('v1.parts.start_content', ['header' => "$event->eventName", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    @if($errors->any())
        @foreach($errors as $error)
            <h4>{{$error}}</h4>
        @endforeach
    @endif

    <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-12">
            {{ $event->eventStartDate->format('n/j/Y g:i A') }} - {{ $event->eventEndDate->format('n/j/Y g:i A') }}<br>
            <b>{{ $loc->locName }}</b><br>
            {{ $loc->addr1 }} <i class="far fa-circle fa-tiny-circle"></i> {{ $loc->city }}
            , {{ $loc->state }} {{ $loc->zip }}
        </div>
        <div class="col-md-3 col-sm-3 col-xs-12">
        </div>
        <div class="col-md-3 col-sm-3 col-xs-12">
            @if(!Auth::check())
                <button class='btn btn-primary btn-sm' id='loginButton' data-toggle="modal" data-target="#login_modal">
                    <i class='far fa-user'>&nbsp;</i> @lang('messages.auth.account')
                </button>
            @endif
        </div>
    </div>
    &nbsp;<br/>
    {{-- Possibly Redundant --}}
    <div class="flash-message">
        @foreach (['danger', 'warning', 'success', 'info'] as $msg)
            @if(Session::has('alert-' . $msg))
                <p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }}
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                </p>
            @endif
        @endforeach
    </div>

    {!! Form::model($person->toArray() + $registration->toArray() + $op->toArray(),
                    ['route' => ['register_step2', $event->eventID], 'method' => 'post', 'id' => 'regForm']) !!}
    {!! Form::hidden('eventID', $event->eventID, array('id' => 'eventID')) !!}
    {!! Form::hidden('ticketID', $ticket->ticketID, array('id' => 'ticketID')) !!}
    {!! Form::hidden('total', 0, array('id' => 'i_total')) !!}
    {!! Form::hidden('quantity', $quantity, array('id' => 'quantity')) !!}

    @if($ticket->waitlisting())
        <div class="clearfix"><p></div>
        <b class="red">
            @lang('messages.instructions.waitlist', $quantity)
        </b>
        <div class="clearfix"></div>
    @endif

    @for($i=1; $i<=$quantity; $i++)
<?php
        $i > 1 ? $i_cnt = "_$i" : $i_cnt = "";
?>
        {!! Form::hidden("percent".$i_cnt, 0, array('id' => "i_percent".$i_cnt)) !!}
        {!! Form::hidden("flatamt".$i_cnt, 0, array('id' => "i_flatamt".$i_cnt)) !!}
        {!! Form::hidden('sub'.$i, 0, array('id' => 'sub'.$i)) !!}
        @if($i == 1)
            {!! Form::hidden('cost'.$i, $isMember ? $earlymbr : $earlynon, array('id' => 'cost'.$i)) !!}
        @else
            {!! Form::hidden('cost'.$i, $earlynon, array('id' => 'cost'.$i)) !!}
        @endif

        <table id="ticket_head" class="table table-striped">
            <th colspan="3" style="text-align: left; vertical-align: middle;" class="col-md-6 col-sm-6 col-xs-12">
                #{{ $i }} <span id="ticket_type{{ $i }}">
                    @if($i == 1)
                        @if($isMember)
                            {{ strtoupper(__('messages.fields.member')) }}
                        @else
                            {{ strtoupper(__('messages.fields.nonmbr')) }}
                        @endif
                    @else
                        {{ strtoupper(__('messages.fields.nonmbr')) }}
                    @endif </span>
                {{ strtoupper(__('messages.fields.ticket')) }}:
                @if($i == 1)
                    {{ $ticket->ticketLabel }}
                @else
                    @if(count($tkts) > 1)
                        <a id="ticketID-{{ $i }}" data-pk="{{ $event->eventID }}"
                           data-value="{{ $ticket->ticketID }}"
                           data-url="{{ env('APP_URL') }}/profile/{{ $person->personID }}"></a>
                        @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.change_ticket')])
                    @else
                        {{ $ticket->ticketLabel }}
                    @endif
                @endif
            </th>
            <th colspan="3" style="text-align: right;" class="col-md-6 col-sm-6 col-xs-12">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="col-md-3 col-sm-3 col-xs-12"></div>
                    @if(1)
                        <div class="col-md-6 col-sm-6 col-xs-12" style="text-align: right; vertical-align: middle;">
                            {!! Form::text("discount_code$i_cnt", $discount_code ?: old($discount_code . $i_cnt),
                                array('size' => '25', 'class' => 'form-control input-sm', 'id' => "discount_code$i_cnt",
                                'placeholder' => trans('messages.fields.enter_disc'))) !!}
                        </div>
                        <div class="col-md-3 col-sm-3 col-xs-12" style="text-align: left; vertical-align: middle;">
                            <a class="btn btn-sm btn-primary"
                               id="btn-apply{{ $i_cnt }}">@lang('messages.buttons.apply')</a></div>
                    @else
                        <div class="col-md-6 col-sm-6 col-xs-12"></div>
                        <div class="col-md-3 col-sm-3 col-xs-12"></div>
                    @endif
                </div>
            </th>
            </tr>
            <tr>
                <td style="width: 11%"><b>@lang('messages.fields.tCost'):</b> @lang('messages.symbols.cur')
                    <span id="tcost{{ $i }}">
                        @if($isMember && $i == 1)
                            {{ $earlymbr }}
                        @else
                            {{ $earlynon }}
                        @endif
                        </span></td>
                <td colspan="2" style="width: 22%; text-align: right; vertical-align: middle;">
                    <b>@lang('messages.fields.app_disc'):</b>
                </td>
                <td colspan="2" style="width: 22%; text-align: left; vertical-align: middle;"><span class="status_msg{{ $i_cnt }}">---</span></td>
                <td style="width: 11%; text-align: right;"><b>@lang('messages.fields.fCost'):</b> @lang('messages.symbols.cur')
                    <span id="final{{ $i }}">---</span></td>
            </tr>
        </table>

        @if($i == 1)
            <div class="col-sm-12">
                <div class="col-sm-3">
                    <b id="notself">@lang('messages.fields.not_mine')<br>Not Today!</b>
                </div>
                <div class="col-sm-1">
                    <input id="selfcheck" onclick="ticket1();" class="flat js-switch input-sm" type="checkbox" checked
                           name="self" value="1">
                </div>
                <div class="col-sm-3">
                    <b id="self">@lang('messages.fields.my_ticket')</b>
                </div>
                <p>&nbsp;</p>
            </div>
        @endif

        <div class="col-sm-3">
            <div class="col-sm-12">
                <label class="control-label" for="login{{ $i_cnt }}">@lang('messages.fields.login') <sup
                            class='red'>*</sup></label>
                @if($i == 1 && $isMember)
                    @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.email_tip')])
                    {!! Form::email("login$i_cnt", old("login$i_cnt"), array('class' => 'form-control input-sm', 'id' => "login$i_cnt",
                        Auth::check() ? 'onfocus="blur();"' : '', 'required')) !!}
                @else
                    {!! Form::email("login$i_cnt", old("login$i_cnt"),
                                array('class' => 'form-control input-sm', 'id' => "login$i_cnt", 'required')) !!}
                @endif
                <br/>

                {!! Form::label("prefix$i_cnt", trans('messages.fields.prefix'), array('class' => 'control-label')) !!}
                {!! Form::select("prefix$i_cnt", $prefix_array, old("prefix$i_cnt"), array('class' => 'form-control input-sm', 'id' => "prefix$i_cnt")) !!}
                <br/>
            </div>

            <div class="col-sm-6">
                <label class="control-label" for="firstName{{ $i_cnt }}">@lang('messages.fields.firstName') <sup
                            class='red'>*</sup></label>
                @if($i == 1 && $isMember)
                    @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.pmi_tip')])
                    {!! Form::text("firstName$i_cnt", old("firstName$i_cnt"), $attributes =
                              array('class' => 'form-control input-sm', 'id' => "firstName$i_cnt", 'required', 'readonly')) !!}
                @else
                    {!! Form::text("firstName$i_cnt", old("firstName$i_cnt"), $attributes =
                              array('class' => 'form-control input-sm', 'id' => "firstName$i_cnt", 'required')) !!}
                @endif
                <br/>
            </div>

            <div class="col-sm-6">
                {!! Form::label("middleName$i_cnt", trans('messages.fields.midName'), array('class' => 'control-label')) !!}
                {!! Form::text("middleName$i_cnt", old("middleName$i_cnt"), array('class' => 'form-control input-sm', 'id' => "middleName$i_cnt")) !!}
                <br/>

            </div>

            <div class="col-sm-6">
                <label class="control-label" for="lastName{{ $i_cnt }}">@lang('messages.fields.lastName') <sup class='red'>*</sup></label>
                @if($i == 1 && $isMember)
                    @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.pmi_tip')])
                    {!! Form::text("lastName$i_cnt", old("lastName$i_cnt"),
                            array('class' => 'form-control input-sm', 'required', 'readonly', 'id' => "lastName$i_cnt")) !!}
                @else
                    {!! Form::text("lastName$i_cnt", old("lastName$i_cnt"),
                            array('class' => 'form-control input-sm', 'required', 'id' => "lastName$i_cnt")) !!}
                @endif
                <br/>
            </div>

            <div class="col-sm-6">
                {!! Form::label("suffix$i_cnt", trans('messages.fields.suffix'), array('class' => 'control-label', 'for' => 'suffix_'.$i)) !!}
                {!! Form::text("suffix$i_cnt", old("suffix$i_cnt"), array('class' => 'form-control input-sm', 'id' => "suffix$i_cnt")) !!}
                <br/>
            </div>

            <div class="col-sm-12">
                <label class="control-label" for="prefName{{ $i_cnt }}">@lang('messages.fields.prefName') <sup
                            class='red'>*</sup></label>
                {!! Form::text("prefName$i_cnt", old("prefName$i_cnt"),
                        array('class' => 'form-control input-sm', 'required', 'id' => "prefName$i_cnt")) !!}
                <br/>
            </div>
        </div>

        <div class="col-sm-3">
            <div class="col-sm-12">
                {!! Form::label("compName$i_cnt", trans('messages.fields.compName'), array('class' => 'control-label')) !!}
                {!! Form::text("compName$i_cnt", old("compName$i_cnt"), array('class' => 'form-control input-sm', 'id' => "compName$i_cnt")) !!}
                <br/>

                {!! Form::label("title$i_cnt", trans('messages.fields.title'), array('class' => 'control-label')) !!}
                {!! Form::text("title$i_cnt", old("title$i_cnt"), array('class' => 'form-control input-sm', 'id' => "title$i_cnt")) !!}
                <br/>
            </div>

            <div class="col-sm-6">
                {!! Form::label("indName$i_cnt", trans('messages.fields.indName'), array('class' => 'control-label')) !!}
                {!! Form::select("indName$i_cnt", $industry_array, old("indName$i_cnt"), array('class' => 'form-control input-sm', 'id' => "indName$i_cnt")) !!}
                <br/>
            </div>

            <div class="col-sm-6">
                {!! Form::label("experience$i_cnt", trans('messages.fields.experience'), array('class' => 'control-label')) !!}
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.experience_tip')])
                {!! Form::select("experience$i_cnt", $experience_choices, old("experience$i_cnt"),
                          array('class' =>'form-control input-sm', 'id' => "experience$i_cnt")) !!}
                <br/>
            </div>

            <div class="col-sm-12">
                {!! Form::label("eventTopics$i_cnt", trans('messages.fields.eventTopics'), array('class' => 'control-label')) !!}
                {!! Form::text("eventTopics$i_cnt", old("eventTopics$i_cnt"), array('class' => 'form-control input-sm', 'id' => "eventTopics$i_cnt")) !!}
                <br/>

                {!! Form::label("cityState$i_cnt", trans('messages.fields.cityState'), array('class' => 'control-label')) !!}
                {!! Form::text("cityState$i_cnt", old("cityState$i_cnt"), array('class' => 'form-control input-sm', 'id' => "cityState$i_cnt")) !!}
                <br/>

                {!! Form::label("canNetwork$i_cnt", trans('messages.fields.canNetwork'), array('class' => 'control-label')) !!}
                <div class="container row col-sm-3">
                    <div class="col-sm-1"><b>@lang('messages.yesno_check.no')</b></div>
                    <div class="col-sm-2">{!! Form::checkbox("canNetwork$i_cnt", '1', false, array('class' => 'flat js-switch', 'id' => "canNetwork$i_cnt")) !!}</div>
                    <div class="col-sm-1"><b>@lang('messages.yesno_check.yes')</b></div>
                </div>
                <br/>
                <p>&nbsp;</p>
            </div>
        </div>

        <div class="col-sm-3">
            {!! Form::label("OrgStat1$i_cnt", trans('messages.fields.orgStat1'), array('class' => 'control-label')) !!}
            @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.orgStat1_tip')])
            @if($i == 1 && $isMember)
                {!! Form::number("OrgStat1$i_cnt", old("OrgStat1$i_cnt"),
                        array('class' => 'form-control input-sm', 'readonly', 'id' => "OrgStat1$i_cnt")) !!}
            @else
                {!! Form::number("OrgStat1$i_cnt", old("OrgStat1$i_cnt"),
                        array('class' => 'form-control input-sm', 'id' => "OrgStat1$i_cnt")) !!}
            @endif
            <br/>

            @if($event->eventTypeID == 5)
                {!! Form::label("chapterRole$i_cnt", trans('messages.fields.chapterRole', ['org' => $org->orgName]), array('class' => 'control-label')) !!}
                {!! Form::text("chapterRole$i_cnt", old("chapterRole$i_cnt"), array('class' => 'form-control input-sm', 'id' => "chapterRole$i_cnt")) !!}
                <br/>
            @endif

            @if($event->eventTypeID == 5)
                {!! Form::label("isFirstEvent$i_cnt", trans('messages.fields.isFirstRegional'), array('class' => 'control-label')) !!}
            @else
                {!! Form::label("isFirstEvent$i_cnt", trans('messages.fields.isFirstEvent', ['org' => $org->orgName]), array('class' => 'control-label')) !!}
            @endif

            <div class="container row col-sm-3">
                <div class="col-sm-1"><b>@lang('messages.yesno_check.no')</b></div>
                <div class="col-sm-2"> {!! Form::checkbox("isFirstEvent$i_cnt", '1', false, array('class' => 'flat js-switch', 'id' => "isFirstEvent$i_cnt")) !!} </div>
                <div class="col-sm-1"><b>@lang('messages.yesno_check.yes')</b></div>
            </div>

            <p>&nbsp;</p>
            {!! Form::label("isAuthPDU$i_cnt", trans('messages.fields.isAuthPDU', ['org' => $org->orgName]), array('class' => 'control-label')) !!}
            <div class="container row col-sm-3">
                <div class="col-sm-1"><b>@lang('messages.yesno_check.no')</b></div>
                <div class="col-sm-2"> {!! Form::checkbox("isAuthPDU$i_cnt", '1', true, array('class' => 'flat js-switch', 'id' => "isAuthPDU$i_cnt")) !!} </div>
                <div class="col-sm-1"><b>@lang('messages.yesno_check.yes')</b></div>
            </div>
            <p>&nbsp;</p>

            @if($event->event_type->etName == trans('messages.fields.nmw'))
                {!! Form::label("eventQuestion$i_cnt", trans('messages.fields.eventQuestion'), array('class' => 'control-label')) !!}
            @else
                {!! Form::label("eventQuestion$i_cnt", trans('messages.fields.eventQuestion'), array('class' => 'control-label')) !!}
            @endif
            {!! Form::textarea("eventQuestion$i_cnt", old("eventQuestion$i_cnt"), $attributes = array('class'=>'form-control input-sm', 'rows' => '2', 'id' => "eventQuestion$i_cnt")) !!}
            <br/>

            <label class="control-label" for="login_{{ $i }}">@lang('messages.fields.affiliation')<sup
                        class='red'>*</sup></label>
            @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.affiliation_tip')])
            {!! Form::select("affiliation$i_cnt" . "[]", $affiliation_array, old("affiliation") ?: reset($affiliation_array), array('class' => 'form-control input-sm', 'size' => '3', 'multiple' => 'multiple', 'required', 'id' => "affiliation$i_cnt")) !!}
        </div>

        <div class="col-sm-3">
            @if($event->hasFood || 1)
                <div class="col-sm-12">
                    {!! Form::label("allergenInfo$i_cnt", trans('messages.fields.allergenInfo'), array('class' => 'control-label')) !!}
                    @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.allergenInfo_tip')])
                    <br/>
                    <small>@lang('messages.fields.accommodate')</small>
                    {!! Form::select("allergenInfo$i_cnt" .'[]', $allergen_array, old("allergenInfo_$i") ?: reset($allergen_array),
                        array('required', 'class' => 'form-control input-sm', 'multiple' => 'multiple', 'size' => '3', 'id' => "allergenInfo$i_cnt")) !!}
                    <br/>

                    {!! Form::label("eventNotes$i_cnt", trans('messages.fields.eventNotes'), array('class' => 'control-label')) !!}
                    {!! Form::textarea("eventNotes$i_cnt", old("eventNotes$i_cnt"), $attributes = array('class'=>'form-control input-sm', 'rows' => '2', 'id' => "eventNotes$i_cnt")) !!}
                    <br/>

                    {!! Form::label("specialNeeds$i_cnt", trans('messages.fields.specialNeeds'), array('class' => 'control-label')) !!}
                    <br/>
                    <small>@lang('messages.fields.accommodate')</small>
                </div>

                <div class="col-sm-12 form-group">
                    {!! Form::text("specialNeeds$i_cnt", old("specialNeeds$i_cnt"), $attributes = array('class' => 'form-control has-feedback-left', 'id' => "specialNeeds$i_cnt")) !!}
                    <span class="fas fa-wheelchair fa-fw fa-sm form-control-feedback left fa-border"
                          aria-hidden="true"></span>
                </div>

            @else
                <div width="100%">
                    <img src="/images/roundtable.jpg" width="100%"/>
                </div>
            @endif
        </div>

        <span id="discount{{ $i_cnt }}" style="display: none;">0</span>
        <span id="flatdisc{{ $i_cnt }}" style="display: none;">0</span>
    @endfor

    <table class="table table-striped">
        <tr>
            <th style="text-align: right; width: 85%; vertical-align: top;">@lang('messages.fields.total')</th>
            <th style="text-align: left; vertical-align: top;"><i class="far fa-dollar-sign"></i> <span id="total">0.00</span></th>
        </tr>
    </table>

    <div class="col-md-9 col-sm-9 col-xs-12"></div>
    <div class="col-md-3 col-sm-3 col-xs-12">
        {!! Form::reset('Reset', array('class' => 'btn btn-info')) !!}
        {!! Form::submit(trans('messages.buttons.rev&pay'), array('class' => 'btn btn-primary')) !!}
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
    @elseif(!Auth::check())
        <script>
            $(document).ready(function () {
                $('#login_modal2').modal('show');
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

        $.ajax({
            type: 'POST',
            cache: true,
            async: true,
            url: '{{ env('APP_URL') }}/tix/{{ $event->eventID }}/{{ $ticket->ticketID }}',
            dataType: 'json',
            success: function (data) {
                var result = eval(data);
                tix = result.tix;
                def_tick = result.def_tick;
                //console.log(result);
            }
        });
    </script>
    <script>
        var member = "{{ $member }}";
        var nonmbr = "{{ $nonmbr }}";
        var checkbox = document.getElementById('selfcheck');
        var myForm = document.getElementById('regForm');
        $('#selfcheck').change(function () {
            if (checkbox.checked != true) {
                // alert('Unclicked');
                $('#firstName').val('');
                $('#prefName').val('');
                $('#middleName').val('');
                $('#lastName').val('');
                $('#suffix').val('');
                $('#login').val('');
                $('#compName').val('');
                $('#title').val('');
                $('#suffix').val('');
                $('#OrgStat1').val('');
                $('#firstName').attr('readonly', false);
                $('#lastName').attr('readonly', false);
                $('#OrgStat1').attr('readonly', false);
                $('#login').attr('readonly', false);
                $('#login').attr('onfocus', false);
                $('#indName').val('');
                $('#prefix').val('');
                $('#experience').val('0');
            } else {
                myForm.reset();
                $('#firstName').attr('readonly', true);
                $('#lastName').attr('readonly', true);
                $('#login').attr('readonly', true);
                $('#login').attr('onfocus', true);
                $('#OrgStat1').attr('readonly', true);
            }
        });

        $(document).ready(function () {
            var subtotal = 0;

            @for($i=1;$i<=$quantity; $i++)
                var tc{{ $i }} = $('#tcost{{ $i }}').text().replace(/,/g, '') * 1;
                var newval{{ $i }} = tc{{ $i }} * 1;
                $('#final{{ $i }}').text(tc{{ $i }}.toFixed(2));
                subtotal += newval{{ $i }} * 1;
                $("#sub{{ $i }}").val(newval{{ $i }}.toFixed(2));
            @endfor

            @for($i=2;$i<=$quantity;$i++)
            $('#ticketID-{{ $i }}').editable({
                type: 'select',
                autotext: 'auto',
                source: [
<?php
                    $string = '';
                    foreach($tkts as $row) {
                        $string .= "{ value: '" . $row->ticketID . "' , text: '" . $row->ticketLabel . "' },\n";
                    }
?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ]
            });
            @endfor

            $('#total').text(subtotal.toFixed(2));
            $('#i_total').val(subtotal.toFixed(2));

            @for($i=1; $i<=$quantity; $i++)
<?php
                $i > 1 ? $i_cnt = "_$i" : $i_cnt = "";
?>
                var percent{{ $i_cnt }} = $('#discount{{ $i_cnt }}').text();
                var flatAmt{{ $i_cnt }} = $('#flatdisc{{ $i_cnt }}').text();

                if (!FieldIsEmpty($("#discount_code{{ $i_cnt }}"))) {
                    validateCode('{{ $event->eventID }}', '{{ $i_cnt }}');
                }

                $('#btn-apply{{ $i_cnt }}').on('click', function (e) {
                    e.preventDefault();
                    validateCode('{{ $event->eventID }}', '{{ $i_cnt }}');
                });

                $('#firstName{{ $i_cnt }}').on('change', function (e) {
                    $('#prefName{{ $i_cnt }}').val($('#firstName{{ $i_cnt }}').val());
                });

                $('#login{{ $i_cnt }}').on('change', function (e) {
                    findUser($('#login{{ $i_cnt }}').val(), '{{ $i_cnt }}');
                });

                $('#OrgStat1{{ $i_cnt }}').on('change', function (e) {
                    if(!FieldIsEmpty($("#OrgStat1{{ $i_cnt }}"))){
                        findID($('#OrgStat1{{ $i_cnt }}').val(), '{{ $i_cnt }}');
                    }
                });

            @endfor

            function validateCode(eventID, which) {
                var codeValue = $("#discount_code" + which).val();
                // alert('codeValue: ' + codeValue);
                if (FieldIsEmpty(codeValue)) {
                    var message = '<i class="fas fa-exclamation-triangle fa-2x text-warning mid_align">&nbsp;</i>@lang('messages.codes.empty')';
                    $('.status_msg' + which).html(message).fadeIn(500).fadeOut(3000);

                } else {
                    $.ajax({
                        type: 'POST',
                        cache: false,
                        async: true,
                        url: '{{ env('APP_URL') }}/discount/' + eventID,
                        dataType: 'json',
                        data: {
                            event_id: eventID,
                            discount_code: codeValue
                        },
                        beforeSend: function () {
                            $('.status_msg' + which).html('');
                            $('.status_msg' + which).fadeIn(0);
                        },
                        success: function (data) {
                            //console.log(data);
                            var result = eval(data);
                            $('.status_msg' + which).html(result.message).fadeIn(0);
                            $('#discount' + which).text(result.percent);
                            $('#flatdisc' + which).text(result.flatAmt);

                            $('#i_percent'+which).val(result.percent);
                            $('#i_flatamt'+which).val(result.flatAmt);
                            recalc();
                        },
                        error: function (data) {
                            //console.log(data);
                            var result = eval(data);
                            $('.status_msg{{ $i_cnt }}').html(result.message).fadeIn(0);
                        }
                    });
                }
            };

            function findUser(email, which) {
                if (!FieldIsEmpty(email)) {
                    $.ajax({
                        type: 'POST',
                        cache: false,
                        async: true,
                        url: '{{ env('APP_URL') }}/eLookup/' + email,
                        dataType: 'json',
                        success: function (data) {
                            //console.log(data);
                            var result = eval(data);
                            if (result.status == 'success') {
                                {{--
                                // prompt user with modal (email points to user x) and ask if that's correct
                                // and if they want to auto-populate the form, yes/no.
                                --}}
                                var p = result.p;

                                $('#confirm_modal-content').html(result.msg);
                                $('#confirm_modal').modal('show');
                                var confirm_modal = function (callback) {
                                    $('#modal-btn-yes').on('click', function () {
                                        callback(true);
                                        $('#confirm_modal').modal('hide');
                                    });

                                    $('#modal-btn-no').on('click', function () {
                                        callback(false);
                                        $('#confirm_modal').modal('hide');
                                    });
                                };
                                confirm_modal(function (confirm) {
                                    if (confirm) {
                                        $('#firstName' + which).val(p["firstName"]);
                                        $('#prefName' + which).val(p["prefName"]);
                                        $('#middleName' + which).val(p["middleName"]);
                                        $('#lastName' + which).val(p["lastName"]);
                                        $('#suffix' + which).val(p["suffix"]);
                                        $('#login' + which).val(p["login"]);
                                        $('#compName' + which).val(p["compName"]);
                                        $('#title' + which).val(p["title"]);
                                        $('#suffix' + which).val(p["suffix"]);
                                        $('#OrgStat1' + which).val(p["orgperson"]["OrgStat1"]);
                                        $('#indName' + which).val(p["indName"]);
                                        $('#prefix' + which).val(p["prefix"]);
                                        $('#experience' + which).val(p["experience"]);
                                        $('#affiliation' + which).val(p["affiliation"]);
                                        var pmi_id = $('#OrgStat1' + which).val();
                                        if (pmi_id > 0) {
                                            $('#firstName' + which).attr('readonly', true);
                                            $('#lastName' + which).attr('readonly', true);
                                            $('#OrgStat1' + which).attr('readonly', true);
                                            $('#login' + which).attr('readonly', true);
                                            $('#login' + which).attr('onfocus', true);
                                        }
                                    } else {
                                        $('#login' + which).val('');
                                        $('#login' + which).focus();
                                    }
                                });
                            }
                        }
                    });
                }
            }

            function findID(pmi_id, which) {
                if (!FieldIsEmpty(pmi_id)) {
                    console.log('starting search');
                    $.ajax({
                        type: 'POST',
                        cache: false,
                        async: true,
                        url: '{{ env('APP_URL') }}/oLookup/' + pmi_id,
                        dataType: 'json',
                        success: function (data) {
                            //console.log(data);
                            var result = eval(data);
                            if (result.status == 'success') {
                                console.log(data);
                               {{--
                               // prompt user with modal (email points to user x) and ask if that's correct
                               // and if they want to auto-populate the form, yes/no.
                               --}}
                                var p = result.p;

                                $('#confirm_modal-content').html(result.msg);
                                $('#confirm_modal').modal('show');
                                var confirm_modal2 = function (callback2) {
                                    $('#modal-btn-yes').on('click', function () {
                                        callback2(true);
                                        $('#confirm_modal').modal('hide');
                                    });

                                    $('#modal-btn-no').on('click', function () {
                                        callback2(false);
                                        $('#confirm_modal').modal('hide');
                                    });
                                };
                                confirm_modal2(function (confirm2) {
                                    if (confirm2) {
                                        j = which.replace('_', '');
                                        $('#firstName' + which).val(p["firstName"]);
                                        $('#prefName' + which).val(p["prefName"]);
                                        $('#middleName' + which).val(p["middleName"]);
                                        $('#lastName' + which).val(p["lastName"]);
                                        $('#suffix' + which).val(p["suffix"]);
                                        $('#login' + which).val(p["login"]);
                                        $('#compName' + which).val(p["compName"]);
                                        $('#title' + which).val(p["title"]);
                                        $('#suffix' + which).val(p["suffix"]);
                                        $('#OrgStat1' + which).val(p["orgperson"]["OrgStat1"]);
                                        $('#indName' + which).val(p["indName"]);
                                        $('#prefix' + which).val(p["prefix"]);
                                        $('#experience' + which).val(p["experience"]);
                                        $('#affiliation' + which).val(p["affiliation"]);
                                        var pmi_id = $('#OrgStat1' + which).val();
                                        if (pmi_id > 0) {
                                            $('#firstName' + which).attr('readonly', true);
                                            $('#lastName' + which).attr('readonly', true);
                                            $('#OrgStat1' + which).attr('readonly', true);
                                            $('#login' + which).attr('readonly', true);
                                            $('#login' + which).attr('onfocus', true);
                                            {{--
                                               Change the cost of the ticket from non-member to member
                                            --}}
                                            $("#ticket_type"+j).html(member);
                                            fix_pricing(j, which);
                                        }
                                    } else {
                                        var pmi_id = $('#OrgStat1' + which).val();
                                        if(pmi_id > 0){
                                            $("#ticket_type"+j).html(member);
                                            fix_pricing(j, which);
                                        }
                                    }
                                });
                            } else {
                                console.log('not success');
                                var pmi_id = $('#OrgStat1' + which).val();
                                if(pmi_id > 0){
                                    $("#ticket_type"+j).html(member);
                                    fix_pricing(j, which);
                                }
                            }
                        }
                    });
                }
            }

            function fix_pricing(j, which){
                x = document.getElementById('ticketID-'+j);
                compare = x.attributes['data-value'].value;
                tc = document.getElementById('tcost'+j);
                fc = document.getElementById('final'+j);
                var mbr_price = def_tick['memberBasePrice'];
                tix.forEach((ticket) => {
{{--
                Check if count(tix) > 1 and if so then check each ticket on ticketID = 'ticketID-'+j and take the price
--}}
                    if(tix.length > 1){
                        var new_price = ticket['memberBasePrice'];
                        if(ticket['ticketID'] == compare){
                            tc.innerHTML = new_price.toFixed(2);
                            fc.innerHTML = new_price.toFixed(2);
                        }
                    } else {
                        tc.innerHTML = mbr_price.toFixed(2);
                        fc.innerHTML = mbr_price.toFixed(2);
                    }
                recalc();
                });
            }

            function recalc(){
                subtotal = 0;
                @for($i=1; $i<=$quantity; $i++)
<?php
                    $i > 1 ? $i_cnt = "_$i" : $i_cnt = "";
?>
                    percent{{ $i_cnt }} = $('#i_percent{{ $i_cnt }}').val();
                    flatAmt{{ $i_cnt }} = $('#i_flatamt{{ $i_cnt }}').val();
                    tc{{ $i }} = $('#tcost{{ $i }}').text();

                    if (percent{{ $i_cnt }} > 0) {
                        newval{{ $i }} = tc{{ $i }} - (tc{{ $i }} * percent{{ $i_cnt }} / 100);
                        $('#final{{ $i }}').text(newval{{ $i }}.toFixed(2));
                    } else {
                        newval{{ $i }} = ((tc{{ $i }} * 1) - (flatAmt{{ $i_cnt }} * 1));
                        if (newval{{ $i }} < 0) newval{{ $i }} = 0;
                        $('#final{{ $i }}').text(newval{{ $i }}.toFixed(2));
                    }
                    subtotal += newval{{ $i }} * 1;
                    $("#sub{{ $i }}").val(newval{{ $i }}.toFixed(2));

                @endfor

                $('#total').text(subtotal.toFixed(2));
                $('#i_total').val(subtotal.toFixed(2));
            }

        });
    </script>
    <script>
        $("[data-toggle=tooltip]").tooltip();
    </script>
@endsection

@section('modals')
    @if(!Auth::check())
        @include('v1.modals.login')
        @include('v1.modals.login', ['id' => 'login_modal2', 'msg' => trans('messages.modals.login_msg2')])
        @include('v1.modals.forgot')
    @endif
    @include('v1.modals.yesno_confirm', ['id' => 'confirm_modal', 'content' => trans('messages.modals.confirm')])
@endsection
