<?php
/**
 * Comment: This is the form that will serve for both creation and editing of an event
 * Created: 2/11/2017
 */
use App\Location;
use App\Event;
use GrahamCampbell\Flysystem\Facades\Flysystem;

$dateFormat = 'm/d/Y h:i A';
$org = $current_person->defaultOrg;

if(isset($event)) {
    $eventStartDate = date($dateFormat, strtotime($event->eventStartDate));
    $eventEndDate   = date($dateFormat, strtotime($event->eventEndDate));
} elseif(old('eventStartDate')) {
    //$eventStartDate = DateTime::createFromFormat('m/d/Y h:M A', old('eventStartDate'))->format('Y-m-d');
    $eventStartDate = date($dateFormat, strtotime(old('eventStartDate')));
    $eventEndDate = date($dateFormat, strtotime(old('eventEndDate')));
    $event          = new Event;
    $exLoc          = new Location;
} else {
    $event          = new Event;
    $exLoc          = new Location;
    $eventStartDate = date($dateFormat, strtotime("now"));
    $eventEndDate   = date($dateFormat, strtotime("now"));
    //$eventStartDate = date('Y-m-d', strtotime("now"));
    //$eventEndDate   = date('Y-m-d', strtotime("now"));
}
$topBits = '';

$cats = DB::table('event-category')
          ->select('catID', 'catTXT')
          ->where([
              ['isActive', 1],
              ['orgID', $current_person->defaultOrgID]
          ])->get();

$categories = $cats->pluck('catTXT', 'catID');

$oe_types = DB::table('org-event_types')
              ->select('etID', 'etName')
              ->whereIn('orgID', [1, $current_person->defaultOrgID])
              ->whereNull('deleted_at')
              ->get();

$event_types = $oe_types->pluck('etName', 'etID');

$tz = DB::table('timezone')->select('zoneName', 'zoneOffset')->get();

$timezones = $tz->pluck('zoneName', 'zoneOffset');

$defaults = DB::table('organization')
              ->select('orgZone', 'orgCategory', 'orgName', 'eventEmail')
              ->where('orgID', $current_person->defaultOrgID)->first();

$locations = DB::table('event-location')
               ->where(
                   [
                       ['orgID', $current_person->defaultOrgID],
                       ['isDeleted', 0]
                   ])->orderBy('locName', 'asc')->get();

$loc_list = ['' => 'Existing Location'] + Location::orderBy('locName')->pluck('locName', 'locID')->toArray();

$orgLogoPath = DB::table('organization')
                 ->select('orgPath', 'orgLogo')
                 ->where('orgID', $current_person->defaultOrgID)->first();

$currentPerson = $current_person;
$currentOrg    = $org;

try {
    if ($org->orgLogo !== null) {
        $s3m = Flysystem::connection('s3_media');
        $logo = $s3m->getAdapter()->getClient()->getObjectURL(env('AWS_BUCKET3'), $org->orgPath . "/" . $org->orgLogo);
    }
} catch (\League\Flysystem\Exception $exception) {
    $logo = '';
}

?>

@extends('v1.layouts.auth', ['topBits' => $topBits])

@if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('event-management'))
    || Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))

@section('content')
    <h3>{{ $page_title }}</h3>
    @if($page_title == trans('messages.headers.event_edit'))
        <div class="col-xs-12">
            <div class="col-xs-6">
                @include('v1.parts.event_buttons', ['event' => $event])
            </div>
        </div>
    @endif

    @if($event->eventID !== null)
        {!! Form::model($event->toArray() + $exLoc->toArray(), ['route' => ['event_update', $event->eventID], 'method' => 'patch']) !!}
    @else
        {!! Form::open(array('url' => '/event/create')) !!}
    @endif
    @include('v1.parts.start_content', ['header' => trans('messages.fields.detail'), 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div class="form-group col-md-12">
        {!! Form::text('eventName', old('eventName') ?: $event->eventName,
        $attributes = array('class'=>'form-control has-feedback-left', 'placeholder'=>trans('messages.fields.event'). ' ' . trans('messages.fields.name') .'*', 'maxlength' => '255', 'required') ) !!}
        <span class="far fa-calendar-alt fa-fw form-control-feedback left fa-border" aria-hidden="true"></span>
    </div>

    <div class="container row col-sm-12">
        <div class="col-sm-4"></div>
        <div class="col-sm-4">
            {!! Form::label('hasFood', trans('messages.fields.hasFood'), array('class' => 'control-label', 'style'=>'color:red;')) !!}
            @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.hasFood')])
        </div>
        @if($event->eventID !== null && $event->hasFood == 1)
            <div class="col-sm-1">{!! Form::label('hasFood', trans('messages.yesno_check.no'), array('class' => 'control-label')) !!}</div>
            <div class="col-sm-1">{!! Form::checkbox('hasFood', '1', true, array('class' => 'js-switch')) !!}</div>
            <div class="col-sm-1">{!! Form::label('hasFood', trans('messages.yesno_check.yes'), array('class' => 'control-label')) !!}</div>
        @else
            <div class="col-sm-1">{!! Form::label('hasFood', trans('messages.yesno_check.no'), array('class' => 'control-label')) !!}</div>
            <div class="col-sm-1">{!! Form::checkbox('hasFood', '1', false, array('class' => 'js-switch')) !!}</div>
            <div class="col-sm-1">{!! Form::label('hasFood', trans('messages.yesno_check.yes'), array('class' => 'control-label')) !!}</div>
        @endif
    </div>
    <p>&nbsp;</p>

    <div class="form-group col-md-3">
        {!! Form::label('slug', trans('messages.fields.customURL').'*', array('class' => 'control-label input-sm')) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::text('slug', old('slug'), $attributes = array('class'=>'form-control input-sm', 'maxlength' => '100', 'required', 'id' => 'slug') ) !!}
    </div>
    <div class="form-group col-md-3">
        <a class="btn btn-primary btn-sm" id="validateSlug"><i class="">@lang('messages.fields.validate') @lang('messages.headers.avail')</i></a>
    </div>
    <div class="form-group col-md-3" id="slug_feedback">
    </div>
    <div class="form-group col-md-11 col-md-offset-1">
        <b>URL will be: https://www.mCentric.org/events/<span id="curl" style="color:red;">{{ $event->slug or '' }}</span></b>
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('eventDescription', trans('messages.headers.desc').'*', array('class' => 'control-label')) !!}
        {!! Form::textarea('eventDescription', old('eventDescription'), array('class'=>'form-control rich', 'id'=>'snote')) !!}
    </div>

    <div class="form-group col-md-12">
        {!! Form::label('eventTypeID', trans_choice('messages.headers.et', 1).'*', array('class' => 'control-label')) !!}
        {!! Form::select('eventTypeID', $event_types, old('eventTypeID'), array('class' =>'form-control input-sm')) !!}
    </div>

    <div class="form-group col-md-12">
        {!! Form::label('catID', trans('messages.fields.category').'*', array('class' => 'control-label')) !!}
        {!! Form::select('catID', $categories, old('catID') ?: $defaults->orgCategory, array('class' =>'form-control input-sm')) !!}
    </div>

    <div class="form-group col-md-12">
        {!! Form::label('eventInfo', trans('messages.fields.additional'), array('class' => 'control-label')) !!}
        {!! Form::textarea('eventInfo', old('eventInfo'), array('class'=>'form-control rich')) !!}
    </div>

    <div class="col-sm-5">
        {!! Form::label('hasTracks', trans('messages.headers.hasTracks'), array('class' => 'control-label', 'style'=>'color:red;',
        'data-toggle'=>'tooltip', 'title'=>trans('messages.tooltips.hasTracks'))) !!}
    </div>
    @if($event->eventID !== null && $event->hasTracks > 0)
        <div class="col-sm-1"> {!! Form::label('hasTracks', trans('messages.yesno_check.no'), array('class' => 'control-label')) !!} </div>
        <div class="col-sm-1">{!! Form::checkbox('hasTracksCheck', '1', true,
            array('class' => 'js-switch', 'onchange' => 'javascript:toggleShow()')) !!}</div>
        <div class="col-sm-1">{!! Form::label('hasTracks', trans('messages.yesno_check.yes'), array('class' => 'control-label')) !!}</div>
    @else
        <div class="col-sm-1">{!! Form::label('hasTracks', trans('messages.yesno_check.no'), array('class' => 'control-label')) !!}</div>
        <div class="col-sm-1">{!! Form::checkbox('hasTracksCheck', '1', false,
            array('class' => 'js-switch', 'onchange' => 'javascript:toggleShow()')) !!}</div>
        <div class="col-sm-1">{!! Form::label('hasTracks', trans('messages.yesno_check.yes'), array('class' => 'control-label')) !!}</div>
    @endif
    <div id="trackInput"
         @if($event->hasTracks == 0)
         style="display:none;"
         @endif
         class="col-sm-4">
        {!! Form::number('hasTracks', old('hasTracks') ?: $event->hasTracks, $attributes =
            array('class'=>'form-control has-feedback-left input-sm', 'placeholder'=>trans('messages.headers.tracks')) ) !!}
    </div>


    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.ed&t'), 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div class="form-group col-md-5">
        {!! Form::text('eventStartDate', $eventStartDate, $attributes = array('class'=>'form-control has-feedback-left', 'required', 'id' => 'eventStartDate') ) !!}
        <span class="far fa-calendar fa-fw form-control-feedback left fa-border" aria-hidden="true"></span>
    </div>

    <div class="form-group col-md-2" style="text-align: center; vertical-align: bottom;"><b> {{ strtolower(__('messages.headers.to')) }} </b></div>

    <div class="form-group col-md-5">
        {!! Form::text('eventEndDate', $eventEndDate, $attributes = array('class'=>'form-control has-feedback-left', 'required', 'id' => 'eventEndDate') ) !!}
        <span class="far fa-calendar fa-fw form-control-feedback left fa-border" aria-hidden="true"></span>
    </div>

    <div class="form-group col-md-12">
        {!! Form::label('eventTimeZone', trans('messages.headers.tz').'*', array('class' => 'control-label')) !!}
        {!! Form::select('eventTimeZone', $timezones, old('eventTimeZone') ?: $defaults->orgZone, array('class' =>'form-control')) !!}
    </div>

    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => trans('messages.fields.event').' '. trans('messages.fields.loc'),
             'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div class="form-group">
        <div class="col-md-8">
            {!! Form::select('locationID', $loc_list, old('locationID'), array('class' =>'form-control input-sm', 'id'=>'org_location_list')) !!}
        </div>
        <div class="col-md-4">
            <a class="btn btn-primary btn-sm" id="useAddr"><i class="">@lang('messages.buttons.use_addr')</i></a>
        </div>
    </div>
    <br/>
    <div class="ln_solid"></div>

    <div class="form-group col-md-12">
        {!! Form::text('locName', old('locName'),
            $attributes = array('class'=>'form-control has-feedback-left', 'maxlength' => '50',
                                'id'=>'locName', 'placeholder'=>trans('messages.fields.loc_name'), 'required')) !!}
        <span class="far fa-building fa-fw form-control-feedback left fa-border" aria-hidden="true"></span>
    </div>

    <div id="address_info"
    @if($event->eventID !== null)
        {!! $exLoc->isVirtual==0 ?: 'style="display:none;"' !!}
    @endif
    >
        <div class="form-group col-md-12">
            {!! Form::text('addr1', old('addr1'),
                $attributes = array('class'=>'form-control input-sm', 'maxlength' => '255',
                                    'id'=>'addr1', 'placeholder'=>trans('messages.fields.addr').' 1', 'required')) !!}
        </div>
        <div class="form-group col-md-12">
            {!! Form::text('addr2', old('addr2'),
                $attributes = array('class'=>'form-control input-sm', 'maxlength' => '255',
                                    'id'=>'addr2', 'placeholder'=>trans('messages.fields.addr').' 2')) !!}
        </div>
        <div class="form-group col-md-6">
            {!! Form::text('city', old('city'),
                $attributes = array('class'=>'form-control input-sm', 'maxlength' => '50',
                                    'id'=>'city', 'placeholder'=>trans('messages.fields.city'), 'required')) !!}
        </div>
        <div class="form-group col-md-3">
            {!! Form::text('state', old('state'),
                $attributes = array('class'=>'form-control input-sm', 'maxlength' => '10',
                                    'id'=>'state', 'placeholder'=>trans('messages.fields.state'), 'required')) !!}
        </div>
        <div class="form-group col-md-3">
            {!! Form::text('zip', old('zip'),
                $attributes = array('class'=>'form-control input-sm', 'maxlength' => '10',
                                    'id'=>'zip', 'placeholder'=>trans('messages.fields.zip'), 'required')) !!}
        </div>
    </div>

    <div class="form-group col-md-3 col-md-offset-1">
        {!! Form::label('virtual', trans('messages.headers.virtual'), array('class' => 'control-label')) !!} &nbsp; &nbsp;
    </div>
    <div class="form-group col-md-8">
        {!! Form::label('virtual', trans('messages.yesno_check.no'), array('class' => 'control-label')) !!} &nbsp;
        @if($event->eventID !== null)
            {!! Form::checkbox('virtual', 1, ($exLoc->isVirtual?'true':'false'),
                $attributes = array('class'=>'js-switch', 'id'=>'virtual', 'onchange' => 'javascript:toggleHide()')) !!}
        @else
            {!! Form::checkbox('virtual', 1, false,
                $attributes = array('class'=>'js-switch', 'id'=>'virtual', 'onchange' => 'javascript:toggleHide()')) !!}
        @endif
        {!! Form::label('virtual', trans('messages.yesno_check.yes'), array('class' => 'control-label')) !!}
    </div>
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.contact_det'),
             'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div class="form-group col-md-12">
        <div class="form-group col-md-3">
            {!! Form::label('contactOrg', trans('messages.headers.off_by'), array('class' => 'control-label')) !!}
        </div>

        <div class="form-group col-md-9">
            {!! Form::text('contactOrg', $event->contactOrg ?: $defaults->orgName, $attributes = array('class'=>'form-control has-feedback-left', 'maxlength' => '100', 'placeholder'=> $current_person->defaultOrg->orgName, 'required') ) !!}
            <span class="far fa-building fa-fw form-control-feedback left fa-border" aria-hidden="true"></span>
        </div>
    </div>

    <div class="form-group col-md-12">
        <div class="form-group col-md-3">
            {!! Form::label('contactEmail', trans('messages.headers.organizer'), array('class' => 'control-label')) !!}
        </div>

        <div class="form-group col-md-9">
            {!! Form::text('contactEmail', $event->contactEmail ?: $defaults->eventEmail, $attributes = array('class'=>'form-control has-feedback-left', 'maxlength' => '100', 'placeholder'=>'Contact Email', 'required') ) !!}
            <span class="far fa-envelope fa-fw form-control-feedback left fa-border" aria-hidden="true"></span>
        </div>
    </div>

    <div class="form-group col-md-12">
        <div class="form-group col-md-3">
            {!! Form::label('contactDetails', trans('messages.fields.add_det'), array('class' => 'control-label')) !!}
        </div>

        <div class="form-group col-md-9">
            {!! Form::textarea('contactDetails', $event->contactDetails ?: '', $attributes = array('class'=>'form-control', 'rows' => '5')) !!}
        </div>
    </div>
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.ev_logo'),
             'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div>

        <div class="form-group col-sm-3 col-md-3">
            {!! Form::label('contactDetails', trans('messages.headers.disp_logo').'?', array('class' => 'control-label')) !!}
        </div>

        <div class="form-group col-sm-5 col-md-5">
            <div class="container row col-sm-12">
                @if($event->eventID !== null && $event->showLogo != 1)
                    <div class="col-sm-2"> {!! Form::label('showLogo', trans('messages.yesno_check.no'), array('class' => 'control-label')) !!} </div>
                    <div class="col-sm-3">{!! Form::checkbox('showLogo', '1', false, array('class' => 'js-switch')) !!}</div>
                    <div class="col-sm-1">{!! Form::label('showLogo', trans('messages.yesno_check.yes'), array('class' => 'control-label')) !!}</div>
                @else
                    <div class="col-sm-2">{!! Form::label('showLogo', trans('messages.yesno_check.no'), array('class' => 'control-label')) !!}</div>
                    <div class="col-sm-3">{!! Form::checkbox('showLogo', '1', true, array('class' => 'js-switch')) !!}</div>
                    <div class="col-sm-1">{!! Form::label('showLogo', trans('messages.yesno_check.yes'), array('class' => 'control-label')) !!}</div>
                @endif
            </div>
        </div>

        <div class="form-group col-sm-4 col-md-4">
            <img src="{{ $logo }}" alt=" {{ trans('messages.headers.logo') }}">
        </div>


    </div>
    @include('v1.parts.end_content')

    <div class="col-md-12">
        {!! Form::submit('Submit & Review Tickets', array('class' => 'btn btn-primary')) !!}
        <a href="{{ env('APP_URL') }}/events" class="btn btn-default">@lang('messages.headers.cancel')</a>
    </div>


    @include('v1.parts.start_content', ['header' => strtoupper(trans('messages.headers.opt')).': '. trans('messages.headers.post-reg').' '.trans('messages.headers.info'),
             'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <div class="form-group col-md-12">
        {!! Form::label('postRegInfo', trans('messages.instructions.postRegInfo'), array('class' => 'control-label red')) !!}
        {!! Form::textarea('postRegInfo', old('postRegInfo'), array('class'=>'form-control rich')) !!}
    </div>
    @include('v1.parts.end_content')


    {!! Form::close() !!}
@endsection

@section('scripts')
    @include('v1.parts.summernote')
    @include('v1.parts.footer-tinymce')
<script>
    $(document).ready(function () {
        $('#eventStartDate').val(moment(new Date($('#eventStartDate').val())).format("MM/DD/YYYY HH:mm A"));
        $('#eventEndDate').val(moment(new Date($('#eventEndDate').val())).format("MM/DD/YYYY HH:mm A"));
    });
</script>

    @include('v1.parts.footer-daterangepicker', ['fieldname' => 'eventStartDate', 'time' => 'true', 'single' => 'true'])
    @include('v1.parts.footer-daterangepicker', ['fieldname' => 'eventEndDate', 'time' => 'true', 'single' => 'true'])
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var show;
            var hide;
        });

        show = 1;
        hide = 0;

        function toggleShow() {
            $("#trackInput").toggle();
        }
        function toggleHide() {
            $("#address_info").toggle();
            if(show){
                show = 0;
                hide = 1;
                $('#addr1').removeAttr('required');
                $('#addr2').removeAttr('required');
                $('#city').removeAttr('required');
                $('#state').removeAttr('required');
                $('#zip').removeAttr('required');
            } else {
                show = 1;
                hide = 0;
                $('#addr1').required = true;
                $('#addr2').required = true;
                $('#city').required = true;
                $('#state').required = true;
                $('#zip').required = true;
            }
        }
        $(document).ready(function () {
            // We'll help out users by populating the end date/time based on the start
            $('#eventStartDate').on('change', function () {
                foo = new moment($('#eventStartDate').val()).add(1, 'h').toDate();
                $('#eventEndDate').val(moment(new Date(foo)).format("MM/DD/YYYY HH:mm A"));
                $('#eventEndDate').daterangepicker({
                    timePicker: true,
                    autoUpdateInput: true,
                    singleDatePicker: true,
                    showDropdowns: true,
                    timePickerIncrement: 15,
                    locale: {
                        format: 'M/D/Y h:mm A'
                    }
                });
            });
            $('#hasTracksCheck').on('change', function () {
                $("#trackInput").toggle();
            });
        });
    </script>
    <script>
        $('form').submit(function () {
            $('#eventStartDate').each(function () {
                $(this).val(moment(new Date($(this).val())).format("YYYY-MM-DD HH:mm:ss"))
            });
            $('#eventEndDate').each(function () {
                $(this).val(moment(new Date($(this).val())).format("YYYY-MM-DD HH:mm:ss"))
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            $('#useAddr').click(function () {
                var selection = $('#org_location_list').val();
                if (selection != '') {
                    var theurl = "/locations/" + selection;
                    $.ajax({
                        method: 'get',
                        url: theurl,
                        dataType: "json",
                        success: function (data) {
                            console.log(data);
                            $('#locName').val(data.locName);
                            $('#addr1').val(data.addr1);
                            $('#addr2').val(data.addr2);
                            $('#city').val(data.city);
                            $('#state').val(data.state);
                            $('#zip').val(data.zip);
                        },
                        error: function (data) {
                            alert('error?  url: ' + theurl);
                            console.log(data);
                        }
                    });
                }
            });
        });
    </script>
    <script>
        $(document).ready(function () {
            $('#validateSlug').click(function () {
                var selection = $('#slug').val();
                if (selection != '') {
                    var theurl = "/eventslug/" + "{{ $event->eventID or 0 }}";
                    $.ajax({
                        method: 'post',
                        url: theurl,
                        data: {
                            eventID: '{{ $event->eventID }}',
                            slug: selection
                        },
                        dataType: "json",
                        success: function (data) {
                            $('#slug_feedback').html(data.message);
                            //console.log(data);
                            $('#curl').text($('#slug').val());
                        },
                        error: function (data) {
                            alert('error?  url: ' + theurl);
                            console.log(data);
                        },
                        statusCode: {
                            500: function () {
                                //
                            },
                            422: function (data) {
                                //
                            }
                        }
                    });
                } else {
                    $('#slug_feedback').text('{{ trans('messages.instructions.customURL') }}');
                    $('#slug').focus();
                }
            });
        });
    </script>
    @if($event->eventID === null)
        @include('v1.parts.menu-fix', array('path' => '/event/create'))
    @else
        @include('v1.parts.menu-fix', array('path' => '/event/create', 'tag' => '#add', 'newTxt' => trans('messages.fields.edit_event')))
    @endif
@endsection

@endif
