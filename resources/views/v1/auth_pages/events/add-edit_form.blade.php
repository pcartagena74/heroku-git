<?php
/**
 * Comment: This is the form that will serve for both creation and editing of an event
 * Created: 2/11/2017
 */
use App\Location;
use App\Event;
use GrahamCampbell\Flysystem\Facades\Flysystem;

$dateFormat = 'm/d/Y h:i A';

if(isset($event)) {
    $eventStartDate = date($dateFormat, strtotime($event->eventStartDate));
    $eventEndDate   = date($dateFormat, strtotime($event->eventEndDate));
} else {
    $event          = new Event;
    $exLoc          = new Location;
    $eventStartDate = date('Y-m-d', strtotime("now"));
    $eventEndDate   = date('Y-m-d', strtotime("now"));
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
    || Entrust::hasRole('Development'))

@section('content')

    <h3>{{ $page_title }}</h3>

    @if($event->eventID !== null)
        {!! Form::model($event->toArray() + $exLoc->toArray(), ['route' => ['event_update', $event->eventID], 'method' => 'patch']) !!}
    @else
        {!! Form::open(array('url' => '/event/create')) !!}
    @endif
    @include('v1.parts.start_content', ['header' => 'Event Detail', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div class="form-group col-md-12">
        {!! Form::text('eventName', old('$event->eventName') ?: $event->eventName,
        $attributes = array('class'=>'form-control has-feedback-left', 'placeholder'=>'Event Name*', 'maxlength' => '255', 'required') ) !!}
        <span class="fa fa-calendar-o form-control-feedback left" aria-hidden="true"></span>
    </div>

    <div class="container row col-sm-12">
        <div class="col-sm-4"></div>
        <div class="col-sm-4">
            {!! Form::label('hasFood', 'Will food be served?', array('class' => 'control-label', 'style'=>'color:red;')) !!}
            @include('v1.parts.tooltip', ['title' => "Events with food have additional questions asked of attendees."])
        </div>
        @if($event->eventID !== null && $event->hasFood == 1)
            <div class="col-sm-1"> {!! Form::label('hasFood', 'No', array('class' => 'control-label')) !!} </div>
            <div class="col-sm-1">{!! Form::checkbox('hasFood', '1', true, array('class' => 'js-switch')) !!}</div>
            <div class="col-sm-1">{!! Form::label('hasFood', 'Yes', array('class' => 'control-label')) !!}</div>
        @else
            <div class="col-sm-1">{!! Form::label('hasFood', 'No', array('class' => 'control-label')) !!}</div>
            <div class="col-sm-1">{!! Form::checkbox('hasFood', '1', false, array('class' => 'js-switch')) !!}</div>
            <div class="col-sm-1">{!! Form::label('hasFood', 'Yes', array('class' => 'control-label')) !!}</div>
        @endif
    </div>
    <p>&nbsp;</p>

    <div class="form-group col-md-3">
        {!! Form::label('slug', 'Custom URL*', array('class' => 'control-label input-sm')) !!}
    </div>
    <div class="form-group col-md-3">
        {!! Form::text('slug', old('$event->slug'), $attributes = array('class'=>'form-control input-sm', 'maxlength' => '100', 'required', 'id' => 'slug') ) !!}
    </div>
    <div class="form-group col-md-3">
        <a class="btn btn-primary btn-sm" id="validateSlug"><i class="">Validate Availability</i></a>
    </div>
    <div class="form-group col-md-3" id="slug_feedback">
    </div>
    <div class="form-group col-md-11 col-md-offset-1">
        <b>URL will be: https://www.mCentric.org/events/<span id="curl"
                                                              style="color:red;">{{ $event->slug or '' }}</span></b>
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('eventDescription', 'Description*', array('class' => 'control-label')) !!}
        {!! Form::textarea('eventDescription', old('$event->eventDescription'), array('class'=>'form-control rich')) !!}
    </div>

    <div class="form-group col-md-12">
        {!! Form::label('eventTypeID', 'Event Type*', array('class' => 'control-label')) !!}
        {!! Form::select('eventTypeID', $event_types, old('$event->eventTypeID'), array('class' =>'form-control input-sm')) !!}
    </div>

    <div class="form-group col-md-12">
        {!! Form::label('catID', 'Category*', array('class' => 'control-label')) !!}
        {!! Form::select('catID', $categories, old('$event->catID') ?: $defaults->orgCategory, array('class' =>'form-control input-sm')) !!}
    </div>

    <div class="form-group col-md-12">
        {!! Form::label('eventInfo', 'Additional Information', array('class' => 'control-label')) !!}
        {!! Form::textarea('eventInfo', old('$event->eventInfo'), array('class'=>'form-control rich')) !!}
    </div>

    <div class="col-sm-5">
        {!! Form::label('hasTracks', 'Does this event have tracks? If so, how many?', array('class' => 'control-label', 'style'=>'color:red;',
        'data-toggle'=>'tooltip', 'title'=>'Events with tracks require session setup.')) !!}
    </div>
    @if($event->eventID !== null && $event->hasTracks > 0)
        <div class="col-sm-1"> {!! Form::label('hasTracks', 'No', array('class' => 'control-label')) !!} </div>
        <div class="col-sm-1">{!! Form::checkbox('hasTracksCheck', '1', true,
            array('class' => 'js-switch', 'onchange' => 'javascript:toggleShow()')) !!}</div>
        <div class="col-sm-1">{!! Form::label('hasTracks', 'Yes', array('class' => 'control-label')) !!}</div>
    @else
        <div class="col-sm-1">{!! Form::label('hasTracks', 'No', array('class' => 'control-label')) !!}</div>
        <div class="col-sm-1">{!! Form::checkbox('hasTracksCheck', '1', false,
            array('class' => 'js-switch', 'onchange' => 'javascript:toggleShow()')) !!}</div>
        <div class="col-sm-1">{!! Form::label('hasTracks', 'Yes', array('class' => 'control-label')) !!}</div>
    @endif
    <div id="trackInput"
         @if($event->hasTracks == 0)
         style="display:none;"
         @endif
         class="col-sm-4">
        {!! Form::number('hasTracks', old('$event->hasTracks') ?: $event->hasTracks, $attributes =
            array('class'=>'form-control has-feedback-left input-sm', 'placeholder'=>'Tracks') ) !!}
    </div>


    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Event Date &amp; Time', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div class="form-group col-md-5">
        {!! Form::text('eventStartDate', old($eventStartDate), $attributes = array('class'=>'form-control has-feedback-left', 'required', 'id' => 'eventStartDate') ) !!}
        <span class="fa fa-calendar form-control-feedback left" aria-hidden="true"></span>
    </div>

    <div class="form-group col-md-2" style="text-align: center; vertical-align: bottom;"><b> to </b></div>

    <div class="form-group col-md-5">
        {!! Form::text('eventEndDate', old($eventEndDate), $attributes = array('class'=>'form-control has-feedback-left', 'required', 'id' => 'eventEndDate') ) !!}
        <span class="fa fa-calendar form-control-feedback left" aria-hidden="true"></span>
    </div>

    <div class="form-group col-md-12">
        {!! Form::label('eventTimeZone', 'Time Zone*', array('class' => 'control-label')) !!}
        {!! Form::select('eventTimeZone', $timezones, old($event->eventTimeZone) ?: $defaults->orgZone, array('class' =>'form-control')) !!}
    </div>

    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Event Location', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div class="form-group">
        <div class="col-md-8">
            {!! Form::select('locationID', $loc_list, old($event->locationID), array('class' =>'form-control input-sm', 'id'=>'org_location_list')) !!}
        </div>
        <div class="col-md-4">
            <a class="btn btn-primary btn-sm" id="useAddr"><i class="">Use Address</i></a>
        </div>
    </div>
    <br/>
    <div class="ln_solid"></div>

    <div class="form-group col-md-12">
        {!! Form::text('locName', old('$exLoc->locName'),
            $attributes = array('class'=>'form-control has-feedback-left', 'maxlength' => '50',
                                'id'=>'locName', 'placeholder'=>'Location Name', 'required')) !!}
        <span class="fa fa-building form-control-feedback left" aria-hidden="true"></span>
    </div>

    <div id="address_info"
    @if($event->eventID !== null)
        {!! $exLoc->isVirtual==0 ?: 'style="display:none;"' !!}
    @endif
    >
        <div class="form-group col-md-12">
            {!! Form::text('addr1', old('$exLoc->addr1'),
                $attributes = array('class'=>'form-control input-sm', 'maxlength' => '255',
                                    'id'=>'addr1', 'placeholder'=>'Address 1', 'required')) !!}
        </div>
        <div class="form-group col-md-12">
            {!! Form::text('addr2', old('$exLoc->addr2'),
                $attributes = array('class'=>'form-control input-sm', 'maxlength' => '255',
                                    'id'=>'addr2', 'placeholder'=>'Address 2')) !!}
        </div>
        <div class="form-group col-md-6">
            {!! Form::text('city', old('$exLoc->city'),
                $attributes = array('class'=>'form-control input-sm', 'maxlength' => '50',
                                    'id'=>'city', 'placeholder'=>'City', 'required')) !!}
        </div>
        <div class="form-group col-md-3">
            {!! Form::text('state', old('$exLoc->state'),
                $attributes = array('class'=>'form-control input-sm', 'maxlength' => '10',
                                    'id'=>'state', 'placeholder'=>'State', 'required')) !!}
        </div>
        <div class="form-group col-md-3">
            {!! Form::text('zip', old('$exLoc->zip'),
                $attributes = array('class'=>'form-control input-sm', 'maxlength' => '10',
                                    'id'=>'zip', 'placeholder'=>'Zip', 'required')) !!}
        </div>
    </div>

    <div class="form-group col-md-3 col-md-offset-1">
        {!! Form::label('virtual', 'This is a virtual event', array('class' => 'control-label')) !!} &nbsp; &nbsp;
    </div>
    <div class="form-group col-md-8">
        {!! Form::label('virtual', 'No', array('class' => 'control-label')) !!} &nbsp;
        @if($event->eventID !== null)
            {!! Form::checkbox('virtual', 1, ($exLoc->isVirtual?'true':'false'),
                $attributes = array('class'=>'js-switch', 'id'=>'virtual', 'onchange' => 'javascript:toggleHide()')) !!}
        @else
            {!! Form::checkbox('virtual', 1, false,
                $attributes = array('class'=>'js-switch', 'id'=>'virtual', 'onchange' => 'javascript:toggleHide()')) !!}
        @endif
        {!! Form::label('virtual', 'Yes', array('class' => 'control-label')) !!}
    </div>
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Contact Detail', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div class="form-group col-md-12">
        <div class="form-group col-md-3">
            {!! Form::label('contactOrg', 'Offered By', array('class' => 'control-label')) !!}
        </div>

        <div class="form-group col-md-9">
            {!! Form::text('contactOrg', $event->contactOrg ?: $defaults->orgName, $attributes = array('class'=>'form-control has-feedback-left', 'maxlength' => '100', 'placeholder'=> $current_person->defaultOrg->orgName, 'required') ) !!}
            <span class="fa fa-building form-control-feedback left" aria-hidden="true"></span>
        </div>
    </div>

    <div class="form-group col-md-12">
        <div class="form-group col-md-3">
            {!! Form::label('contactEmail', 'Organizer', array('class' => 'control-label')) !!}
        </div>

        <div class="form-group col-md-9">
            {!! Form::text('contactEmail', $event->contactEmail ?: $defaults->eventEmail, $attributes = array('class'=>'form-control has-feedback-left', 'maxlength' => '100', 'placeholder'=>'Contact Email', 'required') ) !!}
            <span class="fa fa-envelope form-control-feedback left" aria-hidden="true"></span>
        </div>
    </div>

    <div class="form-group col-md-12">
        <div class="form-group col-md-3">
            {!! Form::label('contactDetails', 'Additional Detail', array('class' => 'control-label')) !!}
        </div>

        <div class="form-group col-md-9">
            {!! Form::textarea('contactDetails', $event->contactDetails ?: '', $attributes = array('class'=>'form-control', 'rows' => '5')) !!}
        </div>
    </div>
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Event Logo', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div>

        <div class="form-group col-sm-3 col-md-3">
            {!! Form::label('contactDetails', 'Display Logo?', array('class' => 'control-label')) !!}
        </div>

        <div class="form-group col-sm-5 col-md-5">
            <div class="container row col-sm-12">
                @if($event->eventID !== null && $event->showLogo != 1)
                    <div class="col-sm-2"> {!! Form::label('showLogo', 'No', array('class' => 'control-label')) !!} </div>
                    <div class="col-sm-3">{!! Form::checkbox('showLogo', '1', false, array('class' => 'js-switch')) !!}</div>
                    <div class="col-sm-1">{!! Form::label('showLogo', 'Yes', array('class' => 'control-label')) !!}</div>
                @else
                    <div class="col-sm-2">{!! Form::label('showLogo', 'No', array('class' => 'control-label')) !!}</div>
                    <div class="col-sm-3">{!! Form::checkbox('showLogo', '1', true, array('class' => 'js-switch')) !!}</div>
                    <div class="col-sm-1">{!! Form::label('showLogo', 'Yes', array('class' => 'control-label')) !!}</div>
                @endif
            </div>
        </div>

        <div class="form-group col-sm-4 col-md-4">
            <img src="{{ $logo }}" alt=" Logo">
        </div>


    </div>
    @include('v1.parts.end_content')

    <div class="col-md-12">
        {!! Form::submit('Submit & Review Tickets', array('class' => 'btn btn-primary')) !!}
        <a href="{{ env('APP_URL') }}/events" class="btn btn-default">Cancel</a>
    </div>


    @include('v1.parts.start_content', ['header' => 'OPTIONAL: Post-Registration Information', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <div class="form-group col-md-12">
        {!! Form::label('postRegInfo', "Anything added here will displayed to attendees AFTER they've registered.", array('class' => 'control-label red')) !!}
        {!! Form::textarea('postRegInfo', old('$event->postRegInfo'), array('class'=>'form-control rich')) !!}
    </div>
    @include('v1.parts.end_content')


    {!! Form::close() !!}
@endsection

@section('scripts')
    @include('v1.parts.footer-tinymce')
    @if($event->eventID !== null)
        <script>
            $(document).ready(function () {
                $('#eventStartDate').val(moment(new Date($('#eventStartDate').val())).format("MM/DD/YYYY HH:mm A"));
                $('#eventEndDate').val(moment(new Date($('#eventEndDate').val())).format("MM/DD/YYYY HH:mm A"));
            });
        </script>
    @endif

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
                $('#addr1').removeAttr('required')
                $('#addr2').removeAttr('required')
                $('#city').removeAttr('required')
                $('#state').removeAttr('required')
                $('#zip').removeAttr('required')
            } else {
                show = 1;
                hide = 0;
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
                    $('#slug_feedback').text('Please enter a custom URL.');
                    $('#slug').focus();
                }
            });
        });
    </script>
    @if($event->eventID === null)
        @include('v1.parts.menu-fix', array('path' => '/event/create'))
    @else
        @include('v1.parts.menu-fix', array('path' => '/event/create', 'tag' => '#add', 'newTxt' => 'Edit Event'))
    @endif
@endsection

@endif
