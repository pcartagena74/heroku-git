<?php
/**
 * Comment: Registration form (multiple ticket quantities)
 * Created: 8/24/2017
 * Updated: October 2018 - This is the one in use (not register_new)
 */

use App\Location;
use App\Org;
use App\OrgPerson;
use App\Person;
use App\Registration;
use App\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$org = Org::find($event->orgID);
if (Auth::check()) {
    $auth = 1;
    $person = Person::find(auth()->user()->id);
    $op = OrgPerson::where([
        ['personID', '=', $person->personID],
        ['orgID', '=', $org->orgID],
    ])->first();
    $registration = new Registration;
    if ($person->is_member($org->orgID)) {
        $isMember = 1;
    } else {
        $isMember = 0;
    }
} else {
    $auth = 0;
    $person = new Person;
    $op = new OrgPerson;
    $registration = new Registration;
    $isMember = 0;
}
$loc = Location::find($event->locationID);

$prefixes = DB::table('prefixes')->select('prefix', 'prefix')->get();
$prefix_array = ['' => trans('messages.fields.prefixes.select')] +
    $prefixes->pluck('prefix', 'prefix')->map(function ($item, $key) {
        return trans('messages.fields.prefixes.'.$item);
    })->toArray();

$industries = DB::table('industries')->select('industryName', 'industryName')->orderBy('industryName')->get();
$industry_array = ['' => trans('messages.fields.industries.select')] +
    $industries->pluck('industryName', 'industryName')->map(function ($item, $key) {
        return trans('messages.fields.industries.'.$item);
    })->toArray();

$allergens = DB::table('allergens')->select('allergen', 'allergen')->get();
$allergen_array = $allergens->pluck('allergen', 'allergen')->toArray();

if ($event->eventTypeID == 5) { // This is a regional event so do that instead
    //$chapters = DB::table('organization')->where('orgID', $event->orgID)->select('regionChapters')->first();
    $array = explode(',', $org->regionChapters);
} else {
    //$chapters = DB::table('organization')->where('orgID', $event->orgID)->select('nearbyChapters')->first();
    $array = explode(',', $org->nearbyChapters);
}

if ($org->canSubmitPDU !== null) {
    $PDU_org_types = explode(',', $org->canSubmitPDU);
} else {
    $PDU_org_types = [];
}

$i = 0;
foreach ($array as $chap) {
    $i++;
    $chap = trim($chap);
    $affiliation_array[$chap] = $chap;
}

foreach ($certs as $cert) {
    $cert = trim($cert->certification);
    $cert_array[$cert] = $cert;
}

$today = Carbon\Carbon::now();

$tix_dropdown = $tkts->pluck('ticketLabel', 'ticketID');

$experience_choices = [
    '0' => '0 '.trans('messages.fields.years'),
    '1-4' => '1-4 '.trans('messages.fields.years'),
    '5-9' => '5-9 '.trans('messages.fields.years'),
    '10-14' => '10-14 '.trans('messages.fields.years'),
    '15-19' => '15-19 '.trans('messages.fields.years'),
    '20+' => '20+ '.trans('messages.fields.years'),
];

//var_dump(Session::all());
$i = 0; $should_skip = 0;
if (in_array($event->eventTypeID, explode(',', $org->anonCats))) {
    $should_skip = 1;
    $orig_q = 0;
}
?>
@extends('v1.layouts.no-auth')

@section('content')

    @include('v1.parts.start_content', ['header' => "$event->eventName", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    @if($errors->any())
        @foreach($errors as $error)
            <h4>{{$error}}</h4>
        @endforeach
    @endif

    <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-12">
            @include('v1.parts.event_address')
        </div>
        <div class="col-md-3 col-sm-3 col-xs-12"></div>
        <div class="col-md-3 col-sm-3 col-xs-12">
            @if(!Auth::check())
                <button class='btn btn-primary btn-sm' id='loginButton' data-toggle="modal" data-target="#login_modal">
                    <i class='far fa-user'>&nbsp;</i> @lang('messages.auth.account')
                </button>
            @endif
        </div>
    </div>
    &nbsp;<br/>

    {!! Form::model($person->toArray() + $registration->toArray() + $op->toArray(),
                    ['route' => ['register_step2', $event->eventID], 'method' => 'post', 'id' => 'regForm']) !!}
    {!! Form::hidden('eventID', $event->eventID, array('id' => 'eventID')) !!}
    {!! Form::hidden('total', 0, array('id' => 'i_total')) !!}
    {!! Form::hidden('quantity', $quantity, array('id' => 'quantity')) !!}

    @foreach($tq as $x)
        <?php

        $ticket = Ticket::find($x['t']);
        $q = $x['q'];

        // Determine if Early Bird Pricing should be in effect
        if ($ticket->valid_earlyBird()) {
            $earlymbr = number_format($ticket->memberBasePrice - ($ticket->memberBasePrice * $ticket->earlyBirdPercent / 100), 2, '.', ',');
            $earlynon = number_format($ticket->nonmbrBasePrice - ($ticket->nonmbrBasePrice * $ticket->earlyBirdPercent / 100), 2, '.', ',');
        } else {
            $earlymbr = number_format($ticket->memberBasePrice, 2, '.', ',');
            $earlynon = number_format($ticket->nonmbrBasePrice, 2, '.', ',');
        }
        ?>
        @if($ticket->waitlisting())
            <div class="clearfix"><p></div>
            <b class="red">
                {!! trans_choice('messages.instructions.waitlist', $quantity) !!}
            </b>
            <div class="clearfix"></div>
        @endif

        @for($j=1; $j<=$q; $j++)
            <?php
            $i++;
            $i > 1 ? $i_cnt = "_$i" : $i_cnt = '';
            if ($q > 1 && $should_skip) {
                $orig_q = $q;
                $q = 1;
            }
            ?>
            {!! Form::hidden("percent".$i_cnt, 0, array('id' => "i_percent".$i_cnt)) !!}
            {!! Form::hidden("flatamt".$i_cnt, 0, array('id' => "i_flatamt".$i_cnt)) !!}
            {!! Form::hidden('sub'.$i, 0, array('id' => 'sub'.$i)) !!}
            @if($i == 1)
                {!! Form::hidden('cost'.$i, $isMember ? $earlymbr : $earlynon, array('id' => 'cost'.$i)) !!}
            @else
                {!! Form::hidden('cost'.$i, $earlynon, array('id' => 'cost'.$i)) !!}
            @endif

            <div class="col-md-12">
                <div style="text-align: left; vertical-align: middle;" class="col-md-6 col-sm-6 col-xs-12">
                    &nbsp;<br/>
                    <b>#{{ $i }}</b>
                    <span id="ticket_type{{ $i }}">
                        @if($i == 1)
                            @if($isMember)
                                <b>{{ strtoupper(__('messages.fields.member')) }}</b>
                            @else
                                <b>{{ strtoupper(__('messages.fields.nonmbr')) }}</b>
                            @endif
                        @else
                            <b>{{ strtoupper(__('messages.fields.nonmbr')) }}</b>
                        @endif
                    </span>
                    <b>{{ strtoupper(__('messages.fields.ticket')) }}:</b>
                    @if(count($tkts) > 1)
                        {{ Form::select('ticketID-'.$i, $tix_dropdown, $ticket->ticketID, array('id' => 'ticketID-'.$i)) }}
                        @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.change_ticket')])
                    @else
                        <b>{{ $ticket->ticketLabel }}</b>
                        {{ Form::hidden('ticketID-'.$i, $ticket->ticketID, array('id' => 'ticketID-'.$i)) }}
                    @endif
                    <br/>

                    @if($ticket->waitlisting())
                        <div id="so-{{ $i }}" class="red" style="visibility: visible;"> &nbsp;
                            &nbsp; {{ trans('messages.instructions.sold_out2') }}</div>
                    @else
                        <div id="so-{{ $i }}" class="red" style="visibility: hidden;"> &nbsp;
                            &nbsp; {{ trans('messages.instructions.sold_out2') }}</div>
                    @endif
                </div>
                <div style="text-align: right;" class="col-md-6 col-sm-6 col-xs-12">
                    <div id="adc-{{ $i }}"
                         @if($ticket->waitlisting())
                         style="visibility: hidden;"
                         @else
                         style="visibility: visible;"
                         @endif
                         class="col-md-12 col-sm-12 col-xs-12">
                        <div class="col-md-5 col-sm-5 col-xs-12"></div>
                        <div class="col-md-6 col-sm-6 col-xs-10" style="text-align: right; vertical-align: middle;">
                            {!! Form::text("discount_code$i_cnt", $discount_code ?: old($discount_code . $i_cnt),
                                array('size' => '25', 'class' => 'form-control input-sm', 'id' => "discount_code$i_cnt",
                                'placeholder' => trans('messages.fields.enter_disc'))) !!}
                        </div>
                        <div class="col-md-1 col-sm-1 col-xs-2" style="text-align: left; vertical-align: middle;">
                            <a class="btn btn-sm btn-primary" id="btn-apply{{ $i_cnt }}">
                                @lang('messages.buttons.apply')</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <b>@lang('messages.fields.tCost'):</b> @lang('messages.symbols.cur')
                    <span id="tcost{{ $i }}">
                        @if($isMember && $i == 1)
                            {{ $earlymbr }}
                        @else
                            {{ $earlynon }}
                        @endif
                        </span></div>
                <div class="col-md-2 col-xs-6" style="text-align: right; vertical-align: middle;">
                    <div id="da-{{ $i }}" style="visibility: {{ $ticket->waitlisting() ? "hidden" : "visible" }};">
                        <b>@lang('messages.fields.app_disc'):</b>
                    </div>
                </div>
                <div class="col-md-2 col-xs-6" style="text-align: left; vertical-align: middle;">
                    <span id="dm-{{ $i }}" style="visibility: {{ $ticket->waitlisting() ? "hidden" : "visible" }};"
                          class="status_msg{{ $i_cnt }}">---</span>
                </div>
                <div class="col-md-4 col-xs-12" style="text-align: right;"><b>@lang('messages.fields.fCost') :</b>
                    @lang('messages.symbols.cur') <span id="final{{ $i }}">---</span>
                </div>
            </div>

            @if($i == 1)
                <div id="tkt1st" class="col-sm-12 col-xs-12">
                    <div class="container" style="border: 0px red solid;">
                        <div class="col-sm-2 col-xs-2" style="text-align: right;">
                            {!! Form::checkbox('self', 1, 1, array('id'=>'selfcheck', 'class'=>'flat js-switch input-sm')) !!}
                        </div>
                        <div class="col-sm-10 col-xs-10">
                            <span class="red" id="notself"><b>@lang('messages.fields.not_mine')</b></span>
                        </div>
                    </div>
                </div>
            @endif
            <div class="col-xs-12"><p>&nbsp;</p></div>

            <div class="col-sm-3 col-xs-12">
                <div class="col-xs-12">
                    <label class="control-label" for="login{{ $i_cnt }}">
                        @lang('messages.fields.login')
                        <sup class='red'>*</sup>
                    </label>
                    @if($i == 1 && $isMember)
                        @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.email_tip')])
                        {!! Form::email("login$i_cnt", old("login$i_cnt"), array('class' => 'form-control input-sm',
                                  'id' => "login$i_cnt", Auth::check() ? 'onfocus="blur();"' : '', 'required')) !!}
                    @else
                        {!! Form::email("login$i_cnt", old("login$i_cnt"),
                                  array('class' => 'form-control input-sm', 'id' => "login$i_cnt", 'required')) !!}
                    @endif
                    <br/>

                    {!! Form::label("prefix$i_cnt", trans('messages.fields.prefix'), array('class' => 'control-label')) !!}
                    {!! Form::select("prefix$i_cnt", $prefix_array, old("prefix$i_cnt"),
                              array('class' => 'form-control input-sm', 'id' => "prefix$i_cnt")) !!}
                    <br/>
                </div>

                <div class="col-xs-6">
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

                <div class="col-xs-6">
                    {!! Form::label("middleName$i_cnt", trans('messages.fields.midName'), array('class' => 'control-label')) !!}
                    {!! Form::text("middleName$i_cnt", old("middleName$i_cnt"), array('class' => 'form-control input-sm', 'id' => "middleName$i_cnt")) !!}
                    <br/>

                </div>

                <div class="col-xs-6">
                    <label class="control-label" for="lastName{{ $i_cnt }}">@lang('messages.fields.lastName') <sup
                                class='red'>*</sup></label>
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

                <div class="col-xs-6">
                    {!! Form::label("suffix$i_cnt", trans('messages.fields.suffix'), array('class' => 'control-label', 'for' => 'suffix_'.$i)) !!}
                    {!! Form::text("suffix$i_cnt", old("suffix$i_cnt"), array('class' => 'form-control input-sm', 'id' => "suffix$i_cnt")) !!}
                    <br/>
                </div>

                <div class="col-xs-12">
                    <label class="control-label" for="prefName{{ $i_cnt }}">@lang('messages.fields.prefName') <sup
                                class='red'>*</sup></label>
                    {!! Form::text("prefName$i_cnt", old("prefName$i_cnt"),
                            array('class' => 'form-control input-sm', 'required', 'id' => "prefName$i_cnt")) !!}
                    <br/>

                    @if(!$should_skip)
                        <label class="control-label" for="certifications{{ $i_cnt }}">
                            @lang('messages.fields.certification')
                            <sup class='red'>*</sup>
                        </label>
                        @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.certification_tip')])
                        {{--
                        Laravel help re: multiple repeating form elements with variable model
                        --}}
                        <?php
                        if (old('certifications'.$i_cnt)) {
                            $selected = old('certifications'.$i_cnt);
                        } elseif ($person->certifications) {
                            $selected = explode(',', $person->certifications);
                        } else {
                            $selected = reset($cert_array);
                        }
                        ?>
                        {!! Form::select("certifications" . $i_cnt . "[]", $cert_array, $selected,
                            array('class' => 'form-control input-sm', 'size' => '3', 'multiple' => 'multiple', 'required', 'id' => "certifications$i_cnt")) !!}
                    @endif
                </div>
            </div>

            <div class="col-sm-3 col-xs-12">
                @if(!$should_skip)
                    <div class="col-sm-12">
                        {!! Form::label("compName$i_cnt", trans('messages.fields.compName'), array('class' => 'control-label')) !!}
                        {!! Form::text("compName$i_cnt", old("compName$i_cnt"), array('class' => 'form-control input-sm', 'id' => "compName$i_cnt")) !!}
                        <br/>

                        {!! Form::label("title$i_cnt", trans('messages.fields.title'), array('class' => 'control-label')) !!}
                        {!! Form::text("title$i_cnt", old("title$i_cnt"), array('class' => 'form-control input-sm', 'id' => "title$i_cnt")) !!}
                        <br/>
                    </div>

                    <div class="col-xs-6">
                        <label class="control-label" for="indName{{ $i_cnt }}">
                            @lang('messages.fields.indName')
                            <sup class='red'>*</sup>
                        </label>
                        {!! Form::select("indName$i_cnt", $industry_array, old("indName$i_cnt"),
                                  array('class' => 'form-control input-sm', 'id' => "indName$i_cnt", 'required')) !!}
                        <br/>
                    </div>

                    <div class="col-xs-6">
                        <label class="control-label" for="experience{{ $i_cnt }}">
                            @lang('messages.fields.experience')
                            <sup class='red'>*</sup>
                        </label>
                        @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.experience_tip')])
                        {!! Form::select("experience$i_cnt", $experience_choices, old("experience$i_cnt"),
                                  array('class' =>'form-control input-sm', 'id' => "experience$i_cnt", 'required')) !!}
                        <br/>
                    </div>
                @endif

                <div class="col-xs-12">
                    {!! Form::label("eventTopics$i_cnt", trans('messages.fields.eventTopics'), array('class' => 'control-label')) !!}
                    {!! Form::text("eventTopics$i_cnt", old("eventTopics$i_cnt"), array('class' => 'form-control input-sm', 'id' => "eventTopics$i_cnt")) !!}
                    <br/>

                    {!! Form::label("cityState$i_cnt", trans('messages.fields.cityState'), array('class' => 'control-label')) !!}
                    {!! Form::text("cityState$i_cnt", old("cityState$i_cnt"), array('class' => 'form-control input-sm', 'id' => "cityState$i_cnt")) !!}
                    <br/>

                    {!! Form::label("canNetwork$i_cnt", trans('messages.fields.canNetwork'), array('class' => 'control-label')) !!}
                    <div class="container row col-sm-3">
                        <div class="col-sm-1"><b>@lang('messages.yesno_check.no')</b></div>
                        <div class="col-sm-2">{!! Form::checkbox("canNetwork$i_cnt", '1', false, array('class' => 'flat js-switch', 'id' => "canNetwork$i_cnt", 'checked')) !!}</div>
                        <div class="col-sm-1"><b>@lang('messages.yesno_check.yes')</b></div>
                    </div>
                    <br/>
                    <p>&nbsp;</p>
                </div>
            </div>

            <div class="col-sm-3 col-xs-12">
                {!! Form::label("OrgStat1$i_cnt", trans('messages.fields.orgStat1'), array('class' => 'control-label')) !!}
                @if($isMember)
                    @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.orgStat1_tip2', ['acs' => strtolower($org->adminContactStatement)])])
                @else
                    @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.orgStat1_tip')])
                @endif

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
                    <div class="container row col-xs-3">
                        <div class="col-xs-1"><b>@lang('messages.yesno_check.no')</b></div>
                        <div class="col-xs-2"> {!! Form::checkbox("isFirstEvent$i_cnt", '1', false, array('class' => 'flat js-switch', 'id' => "isFirstEvent$i_cnt")) !!} </div>
                        <div class="col-xs-1"><b>@lang('messages.yesno_check.yes')</b></div>
                    </div>

                    <p>&nbsp;</p>
                @else
                    {{--
                    {!! Form::label("isFirstEvent$i_cnt", trans('messages.fields.isFirstEvent', ['org' => $org->orgName]), array('class' => 'control-label')) !!}
                    --}}
                @endif

                @if(!$should_skip)
                    {!! Form::label("isAuthPDU$i_cnt", trans('messages.fields.isAuthPDU', ['org' => $org->orgName]), array('class' => 'control-label')) !!}
                    <div class="container row col-sm-3">
                        <div class="col-sm-1"><b>@lang('messages.yesno_check.no')</b></div>
                        <div class="col-sm-2"> {!! Form::checkbox("isAuthPDU$i_cnt", '1', true, array('class' => 'flat js-switch', 'id' => "isAuthPDU$i_cnt")) !!} </div>
                        <div class="col-sm-1"><b>@lang('messages.yesno_check.yes')</b></div>
                    </div>
                    <p>&nbsp;</p>
                @endif

                @if(!$should_skip)
                    @if($event->event_type->etName == trans('messages.fields.nmw'))
                        {!! Form::label("eventQuestion$i_cnt", trans('messages.fields.nmwQuestion'), array('class' => 'control-label')) !!}
                    @else
                        {!! Form::label("eventQuestion$i_cnt", trans('messages.fields.eventQuestion'), array('class' => 'control-label')) !!}
                    @endif
                    {!! Form::textarea("eventQuestion$i_cnt", old("eventQuestion$i_cnt"), $attributes = array('class'=>'form-control input-sm', 'rows' => '2', 'id' => "eventQuestion$i_cnt")) !!}
                    <br/>
                @endif
                <?php
                if (old('affiliation'.$i_cnt)) {
                    $selected = old('affiliation'.$i_cnt);
                } elseif ($person->affiliation) {
                    $selected = explode(',', $person->affiliation);
                } else {
                    $selected = reset($affiliation_array);
                }
                ?>

                <label class="control-label" for="affiliation{{ $i_cnt }}">
                    @lang('messages.fields.affiliation')<sup class='red'>*</sup></label>
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.affiliation_tip')])
                {!! Form::select("affiliation" . $i_cnt . "[]", $affiliation_array, $selected,
                    array('class' => 'form-control input-sm', 'size' => '3', 'multiple' => 'multiple', 'required', 'id' => "affiliation$i_cnt")) !!}
            </div>

            <div class="col-sm-3 col-xs-12">
                @if($event->hasFood)
                    <label class="control-label" for="allergenInfo{{ $i_cnt }}">
                        @lang('messages.fields.allergenInfo')
                        <sup class='red'>*</sup></label>
                    @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.allergenInfo_tip')])
                    <br/>
                    <small>@lang('messages.tooltips.accommodate')</small>
                    <?php
                    if (old('allergenInfo'.$i_cnt)) {
                        $selected = old('allergenInfo'.$i_cnt);
                    } elseif ($person->allergenInfo) {
                        $selected = explode(',', $person->allergenInfo);
                    } else {
                        $selected = reset($allergen_array);
                    }
                    ?>
                    {!! Form::select("allergenInfo" . $i_cnt .'[]', $allergen_array, $selected,
                        array('required', 'class' => 'form-control input-sm', 'multiple' => 'multiple', 'size' => '3', 'id' => "allergenInfo$i_cnt")) !!}
                    <br/>

                    {!! Form::label("eventNotes$i_cnt", trans('messages.fields.eventNotes'), array('class' => 'control-label')) !!}
                    {!! Form::textarea("eventNotes$i_cnt", old("eventNotes$i_cnt"), $attributes = array('class'=>'form-control input-sm', 'rows' => '2', 'id' => "eventNotes$i_cnt")) !!}
                    <br/>

                    {!! Form::label("specialNeeds$i_cnt", trans('messages.fields.specialNeeds'), array('class' => 'control-label')) !!}
                    <br/>
                    <small>@lang('messages.tooltips.accommodate')</small>

                    <div class="form-group has-feedback">
                        {!! Form::text("specialNeeds$i_cnt", old("specialNeeds$i_cnt"), $attributes = array('class' => 'form-control has-feedback-left', 'id' => "specialNeeds$i_cnt")) !!}
                        <span class="fab fa-accessible-icon fa-fw input-xs form-control-feedback left"
                              aria-hidden="true"></span>
                    </div>

                @else
                    <div class="hidden-xs" width="100%">
                        <img src="/images/roundtable.jpg" width="100%"/>
                    </div>
                @endif
            </div>

            <span id="discount{{ $i_cnt }}" style="display: none;">0</span>
            <span id="flatdisc{{ $i_cnt }}" style="display: none;">0</span>

            {{--
                This is where post-ticket anonymous counting inputs need to be put
            --}}
            @if($should_skip)
                <div class="col-md-12 panel panel-default">
                    &nbsp; <br/>
                    <div class="col-md-3 col-xs-12 form-group">
                        <label class="control-label" for="additional{{ $i_cnt }}">
                            @lang('messages.headers.addl_label')
                            @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.addl_ticket')])
                        </label>
                    </div>
                    <div class="col-md-3 col-xs-12 form-group">
                        {!! Form::number('additional'.$i_cnt, $orig_q-1, array('class' => 'form-control input-sm', 'id' => 'additional'.$i_cnt)) !!}
                    </div>
                    <div class="col-md-3 col-xs-12 form-group" style="text-align: right">
                        <b>@lang('messages.fields.aCost'):</b>
                        <b>@lang('messages.symbols.cur')</b> <b><span id="addl{{ $i }}">---</span></b>
                    </div>
                </div>
            @else
                {!! Form::hidden('additional'.$i_cnt, 0) !!}
            @endif
        @endfor
    @endforeach

    <table class="table table-striped">
        <tr>
            <th style="text-align: right; width: 85%; vertical-align: top;">@lang('messages.fields.total')</th>
            <th style="text-align: left; vertical-align: top;">@lang('messages.symbols.cur')
                <span id="total">0.00</span>
            </th>
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
    <script>
        jQuery.fn.viz = function () {
            return this.css('visibility', 'visible');
        };

        var tix = [];
    </script>
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

        $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: 'POST',
            cache: true,
            async: true,
            url: '{{ env('APP_URL') }}/tix/{{ $event->eventID }}/{{ $ticket->ticketID ?? '' }}',
            dataType: 'json',
            success: function (data) {
                var result = eval(data);
                def_tick = result.def_tick;
                var mbr_price = def_tick['eb_mbr_price'];
                var nmb_price = def_tick['eb_non_price'];
                tix = result.tix;
                console.log('def_tick defined: '+ def_tick);
                console.log(def_tick);

                if (def_tick.isDiscountExempt == 1) {
                    $("#discount_code").attr('placeholder', '{{ trans('messages.reg_status.disc_exempt') }}');
                    $("#discount_code").prop('disabled', true);
                    $("#btn-apply").prop('disabled', true);
                    da = document.getElementById('da-1');
                    dm = document.getElementById('dm-1');
                    btn = document.getElementById('btn-apply');
                    da.style.visibility = "hidden";
                    dm.style.visibility = "hidden";
                    btn.style.visibility = "hidden";
                }

            }
        });
            var member = "{{ $member }}";
            var nonmbr = "{{ $nonmbr }}";
            var disc_chap = '{{ $discountChapters }}';
            var dc = disc_chap.split(",");
            var checkbox = document.getElementById('selfcheck');
            var myForm = document.getElementById('regForm');

            $('#selfcheck').change(function () {
                if (checkbox.checked != true) {
                    // setting from checked to unchecked
                    if (!{{ Auth::check() ? 1 : 0 }}) {
                        alert("{{ trans('messages.instructions.not_self') }}");
                        location.reload();
                    } else {
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
                        $('#tkt1st').hide();
                        change_tick(1, '');
                    }
                } else {
                    myForm.reset();
                    $('#firstName').attr('readonly', true);
                    $('#lastName').attr('readonly', true);
                    $('#login').attr('readonly', true);
                    $('#login').attr('onfocus', true);
                    $('#OrgStat1').attr('readonly', true);
                }
            });

            var subtotal = 0;

            @for($i=1; $i<=$quantity; $i++)
            var tc{{ $i }} = $('#tcost{{ $i }}').text().replace(/,/g, '') * 1;
            var ac{{ $i }} = $('#addl{{ $i }}').text().replace(/,/g, '') * 1;
            var newval{{ $i }} = tc{{ $i }} * 1;
            $('#final{{ $i }}').text(tc{{ $i }}.toFixed(2));
            subtotal += newval{{ $i }} * 1;
            $("#sub{{ $i }}").val(newval{{ $i }}.toFixed(2));
            @endfor

            $('#total').text(subtotal.toFixed(2));
            $('#i_total').val(subtotal.toFixed(2));

                    @for($i=1; $i<=$quantity; $i++)
<?php
                $i > 1 ? $i_cnt = "_$i" : $i_cnt = '';
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

            $('#affiliation{{ $i_cnt }}').on('change', function (e) {
                which = '{{ $i_cnt }}';
                j = which.replace('_', '');
                if (j == '') j = 1;
                fix_pricing(j, which);
            });

            @if($i == 1)
            $('#login{{ $i_cnt }}').on('change', function (e) {
                findUser($('#login{{ $i_cnt }}').val(), '{{ $i_cnt }}');
            });

            $('#OrgStat1{{ $i_cnt }}').on('change', function (e) {
                if (!FieldIsEmpty($("#OrgStat1{{ $i_cnt }}").val())) {
                    findID($('#OrgStat1{{ $i_cnt }}').val(), '{{ $i_cnt }}');
                } else {
                    change_tick('{{ $i }}', '{{ $i_cnt }}')
                }
            });
            @else   // This can be trashed
            $('#login{{ $i_cnt }}').on('change', function (e) {
                findUser($('#login{{ $i_cnt }}').val(), '{{ $i_cnt }}');
            });

            $('#OrgStat1{{ $i_cnt }}').on('change', function (e) {
                if (!FieldIsEmpty($("#OrgStat1{{ $i_cnt }}").val())) {
                    findID($('#OrgStat1{{ $i_cnt }}').val(), '{{ $i_cnt }}');
                } else {
                    change_tick('{{ $i }}', '{{ $i_cnt }}')
                }
            });
            @endif

            $('#ticketID-{{ $i }}').on('change', function (e) {
                change_tick('{{ $i }}', '{{ $i_cnt }}');
            });

            $('#additional{{ $i_cnt }}').on('change', function (e) {
                change_tick('{{ $i }}', '{{ $i_cnt }}');
            });

            @endfor

            function check_affiliation_disc(aff_array) {
                mp_ok = 0;
                // console.log(aff_array);
                if (aff_array !== null) {
                    aff_array.forEach(function (aff) {
                        if (dc.includes(aff)) {
                            mp_ok = 1;
                        }
                    });
                } else {
                    mp_ok = 0;
                }
                return mp_ok;
            }

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
                            {{--
                            if (result.status == 'error') {
                                // console.log($("#discount_code" + which).val());
                                $("#discount_code" + which).val('');
                            } else {
                            --}}
                            $('#discount' + which).text(result.percent);
                            $('#flatdisc' + which).text(result.flatAmt);

                            $('#i_percent' + which).val(result.percent);
                            $('#i_flatamt' + which).val(result.flatAmt);
                            recalc();
                            {{--
                            }
                    --}}
                        },
                        error: function (data) {
                            //console.log(data);
                            var result = eval(data);
                            $('.status_msg{{ $i_cnt }}').html(result.message).fadeIn(0);
                        }
                    });
                }
            }

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
                                        // prompt user with modal (email addr indicates user x) and ask if that's correct
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
                                        @if($event->hasFood)
                                        $('#allergenInfo' + which).val(p["allergenInfo"]);
                                        $('#specialNeeds' + which).val(p["specialNeeds"]);
                                        $('#eventNotes' + which).val(p["eventNotes"]);
                                                @endif
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
                                                j = which.replace('_', '');
                                            if (j == '') j = 1;
                                            fix_pricing(j, which);
                                            $("#ticket_type" + j).html(member);
                                        }
                                        var popup_text = '';
                                        if (!{{ $auth }} && which == '' && result.pass == 0) {
                                            popup_text = "{!! trans('messages.instructions.no_password') !!}";
                                            $('#modal-content').html(popup_text);
                                            $('#dynamic_modal').modal('show');
                                        } else if (!{{ $auth }} && which == '' && result.pass == 1) {
                                            popup_text = "{!! trans('messages.instructions.need_to_login') !!}";
                                            $('#modal-content').html(popup_text);
                                            $('#dynamic_modal').modal('show');
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
                    console.log('findID triggered.');
                    $.ajax({
                        type: 'POST',
                        cache: false,
                        async: true,
                        url: '{{ env('APP_URL') }}/oLookup/' + pmi_id,
                        dataType: 'json',
                        success: function (data) {
                            var result = eval(data);
                            console.log('returned');
                            console.log(result);
                            if (result.status == 'success') {
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
                                        if (j == '') j = 1;
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
                                        @if($event->hasFood)
                                        $('#allergenInfo' + which).val(p["allergenInfo"]);
                                        $('#specialNeeds' + which).val(p["specialNeeds"]);
                                        $('#eventNotes' + which).val(p["eventNotes"]);
                                                @endif
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
                                            fix_pricing(j, which);
                                            $("#ticket_type" + j).html(member);
                                        }
                                        if (which == '' && result.pass == 0) {
                                            $('#dynamic_modal').modal('show');
                                        }
                                    }
                                });
                            } else {
                                j = which.replace('_', '');
                                if (j == '') j = 1;
                                var pmi_id = $('#OrgStat1' + which).val();
                                if (pmi_id > 0) {
                                    $("#ticket_type" + j).html(member);
                                    fix_pricing(j, which);
                                }
                            }
                        },
                        error: function (data) {
                            j = which.replace('_', '');
                            if (j == '') j = 1;
                            var pmi_id = $('#OrgStat1' + which).val();
                            if (pmi_id > 0) {
                                $("#ticket_type" + j).html(member);
                                fix_pricing(j, which);
                            }
                        }
                    });
                }
            }

            function fix_pricing(j, which) {
                console.log('fix_pricing called...');
                x = document.getElementById('ticketID-' + j);
                aff_array = $('#affiliation' + which).val();
                // mp_ok ~ member pricing is a go - is ok.  ;-)
                mp_ok = check_affiliation_disc(aff_array) * $("#OrgStat1" + which).val();
                compare = x.value;
                @if($should_skip)
                    additional = document.getElementById('additional' + which).value;
                    ac = document.getElementById('addl' + j);
                @else
                    additional = 0;
                    ac = 0;
                @endif

                tc = document.getElementById('tcost' + j);
                fc = document.getElementById('final' + j);
                var mbr_price = def_tick['eb_mbr_price'];
                var nmb_price = def_tick['eb_non_price'];
                tix.forEach(function (ticket) {
                    {{--
                                    Need to change logic so pricing fix
                                        1. Checks if the PMI ID is present and
                                        2. Checks if the affiliated chapter matches discounted chapter list

                                    Then for each ticket, check if count(tix) > 1 and if so then check each
                                    ticket on ticketID = 'ticketID-'+j and take the price
                    --}}
                    if (tix.length > 0) {
                        var mem_price = ticket['eb_mbr_price'];
                        var non_price = ticket['eb_non_price'];
                        if (ticket['ticketID'] == compare) {
                            console.log('mem_price: ' + mem_price);
                            console.log('non_price: ' + non_price);
                            if (mp_ok) {
                                $("#ticket_type" + j).html(member);
                                console.log('mpok_: ' + mp_ok + ", updating member stuff...");
                                console.log(additional);
                                tc.innerHTML = mbr_price.toFixed(2);
                                ac.innerHTML = additional * non_price.toFixed(2);
                                fc.innerHTML = mbr_price + additional * 1;
                            } else {
                                $("#ticket_type" + j).html(nonmbr);
                                console.log('not mpok_: ' + mp_ok + ", updating nonmember stuff...");
                                tc.innerHTML = nmb_price.toFixed(2);
                                ac.innerHTML = additional * non_price.toFixed(2);
                                fc.innerHTML = nmb_price + additional * 1;
                            }
                        }
                    } else {
                        if (mp_ok) {
                            $("#ticket_type" + j).html(member);
                            console.log('mpok: ' + mp_ok + ", updating member stuff 2...");
                            tc.innerHTML = mbr_price.toFixed(2);
                            ac.innerHTML = additional * non_price.toFixed(2);
                            fc.innerHTML = mbr_price + additional * 1;
                        } else {
                            $("#ticket_type" + j).html(nonmbr);
                            console.log('not mpok: ' + mp_ok + ", updating nonmember stuff 2...");
                            tc.innerHTML = nmb_price.toFixed(2);
                            ac.innerHTML = additional * non_price.toFixed(2);
                            fc.innerHTML = nmb_price + additional * 1;
                        }
                    }
                    recalc();
                });
            }

            function new_tick(id) {
                return $.grep(tix, function (e) {
                    return e.ticketID == tktID;
                });
            }

            function wait_list(t) {
                if (t['maxAttendees'] != null && t['regCount'] >= t['maxAttendees']) {
                    return 1;
                } else {
                    return 0;
                }
            }

            function change_tick(i, which) {
                console.log("Called change_tick...");
                tktID = document.getElementById('ticketID-' + i).value;
                so = document.getElementById('so-' + i);
                adc = document.getElementById('adc-' + i);
                da = document.getElementById('da-' + i);
                dm = document.getElementById('dm-' + i);
                btn = document.getElementById('btn-apply' + which);
                t = new_tick(tktID);
                console.log(t);
                tc = document.getElementById('tcost' + i);
                fc = document.getElementById('final' + i);
                os = document.getElementById('OrgStat1' + which);
                // console.log(so.style.visibility);
                if (wait_list(t[0])) {
                    so.style.visibility = "visible";
                    adc.style.visibility = "hidden";
                    da.style.visibility = "hidden";
                    dm.style.visibility = "hidden";
                } else {
                    so.style.visibility = "hidden";
                    adc.style.visibility = "visible";
                    da.style.visibility = "visible";
                    dm.style.visibility = "visible";
                }
                if (t[0].isDiscountExempt == 1) {
                    $("#discount_code" + which).attr('placeholder', '{{ trans('messages.reg_status.disc_exempt') }}');
                    $("#discount_code" + which).prop('disabled', true);
                    $("#btn-apply" + which).prop('disabled', true);
                    da.style.visibility = "hidden";
                    dm.style.visibility = "hidden";
                    btn.style.visibility = "hidden";
                } else {
                    $("#discount_code" + which).attr('placeholder', '{{ trans('messages.fields.enter_disc') }}');
                    $("#discount_code" + which).attr('disabled', false);
                    $("#btn-apply" + which).prop('disabled', false);
                    da.style.visibility = "visible";
                    dm.style.visibility = "visible";
                    btn.style.visibility = "visible";
                }
                // console.log(so.style.visibility);
                fix_pricing(i, which);
            }

            function recalc() {
                subtotal = 0;
                @for($i=1; $i<=$quantity; $i++)
<?php
                    $i > 1 ? $i_cnt = "_$i" : $i_cnt = '';
?>
                    percent{{ $i_cnt }} = $('#i_percent{{ $i_cnt }}').val();
                flatAmt{{ $i_cnt }} = $('#i_flatamt{{ $i_cnt }}').val();
                tc{{ $i }} = $('#tcost{{ $i }}').text();
                @if($should_skip)
                    ac{{ $i }} = $('#addl{{ $i }}').text();
                @else
                    ac{{ $i }} = 0;
                @endif

                if (percent{{ $i_cnt }} > 0) {
                    newval{{ $i }} = tc{{ $i }} - (tc{{ $i }} * percent{{ $i_cnt }} / 100);
                    $('#final{{ $i }}').text(newval{{ $i }}.toFixed(2));
                } else {
                    newval{{ $i }} = ((tc{{ $i }} * 1) - (flatAmt{{ $i_cnt }} * 1));
                    if (newval{{ $i }} < 0) newval{{ $i }} = 0;
                    $('#final{{ $i }}').text(newval{{ $i }}.toFixed(2));
                }
                subtotal += newval{{ $i }} + ac{{ $i }} * 1;
                $("#sub{{ $i }}").val(newval{{ $i }}.toFixed(2) + ac{{ $i }}.toFixed(2));

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
    @include('v1.modals.dynamic', ['header' => trans('messages.headers.reset_pass'), 'content' => trans('messages.instructions.no_password')])
@endsection
