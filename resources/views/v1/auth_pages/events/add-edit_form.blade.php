<?php
/**
 * Comment: This is the form that will serve for both creation and editing of an event
 * Created: 2/11/2017
 */
use Illuminate\Support\Collection;
use App\Location;
use App\Event;

$dateFormat = 'm/d/Y h:i A';

if(isset($event)){
    $eventStartDate = date($dateFormat, strtotime($event->eventStartDate));
    $eventEndDate = date($dateFormat, strtotime($event->eventEndDate));
} else {
    $event = new Event;
    $exLoc = new Location;
    $eventStartDate = date('Y-m-d', strtotime("now"));
    $eventEndDate = date('Y-m-d', strtotime("now"));
}
$topBits    = '';

$cats = DB::table('event-category')
          ->select('catID', 'catTXT')
          ->where([
              ['isActive', 1],
              ['orgID', $current_person->defaultOrgID]
          ])->get();

$categories = $cats->pluck('catTXT', 'catID');

$oe_types = DB::table('org-event_types')
              ->select('etID', 'etName')
              ->where([['isActive', 1], ['orgID', $current_person->defaultOrgID]])->get();

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
                   ])->get();

$loc_list = ['' => 'Existing Location'] + Location::pluck('locName', 'locID')->toArray();

$orgLogoPath = DB::table('organization')
                 ->select('orgPath', 'orgLogo')
                 ->where('orgID', $current_person->defaultOrgID)->first();
?>

@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    <h3>{{ $page_title }}</h3>

    @if($event->eventID !== null)
        {!! Form::model($event->toArray() + $exLoc->toArray(), ['route' => ['event_update', $event->eventID], 'method' => 'patch']) !!}
    @else
        {!! Form::open(array('url' => '/event/create')) !!}
    @endif
    @include('v1.parts.start_content', ['header' => 'Step One: Event Detail', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div class="form-group col-md-12">
        {!! Form::text('eventName', old('$event->eventName') ?: $event->eventName, $attributes = array('class'=>'form-control has-feedback-left', 'placeholder'=>'Event Name*', 'required') ) !!}
        <span class="fa fa-calendar form-control-feedback left" aria-hidden="true"></span>
    </div>

    <div class="form-group col-md-12">
        {!! Form::label('eventDescription', 'Description*', array('class' => 'control-label')) !!}
        {!! Form::textarea('eventDescription', old('$event->eventDescription'), array('class'=>'form-control rich')) !!}
    </div>

    <div class="form-group col-md-12">
        {!! Form::label('eventTypeID', 'Event Type*', array('class' => 'control-label')) !!}
        {!! Form::select('eventTypeID', $event_types, old('$event->eventTypeID'), array('class' =>'form-control')) !!}
    </div>

    <div class="form-group col-md-12">
        {!! Form::label('catID', 'Category*', array('class' => 'control-label')) !!}
        {!! Form::select('catID', $categories, old('$event->catID') ?: $defaults->orgCategory, array('class' =>'form-control')) !!}
    </div>

    <div class="form-group col-md-12">
        {!! Form::label('eventInfo', 'Additional Information', array('class' => 'control-label')) !!}
        {!! Form::textarea('eventInfo', old('$event->eventInfo'), array('class'=>'form-control rich')) !!}
    </div>

    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Step Two: Event Date &amp; Time', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

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

    @include('v1.parts.start_content', ['header' => 'Step Three: Event Location', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div class="form-group">
        <div class="col-md-8">
            {!! Form::select('locationID', $loc_list, old($event->locationID), array('class' =>'form-control', 'id'=>'org_location_list')) !!}
        </div>
        <div class="col-md-4">
            <a class="btn btn-primary btn-sm" id="useAddr"><i class="">Use Address</i></a>
        </div>
    </div>
    <br/>
    <div class="ln_solid"></div>

    <div class="form-group col-md-12">
        {!! Form::text('locName', old('$exLoc->locName'), $attributes = array('class'=>'form-control has-feedback-left', 'id'=>'locName', 'placeholder'=>'Location Name', 'required') ) !!}
        <span class="fa fa-building form-control-feedback left" aria-hidden="true"></span>
    </div>

    <div class="form-group col-md-12">
        {!! Form::text('addr1', old('$exLoc->addr1'), $attributes = array('class'=>'form-control', 'id'=>'addr1', 'placeholder'=>'Address 1', 'required') ) !!}
    </div>

    <div class="form-group col-md-12">
        {!! Form::text('addr2', old('$exLoc->addr2'), $attributes = array('class'=>'form-control', 'id'=>'addr2', 'placeholder'=>'Address 2') ) !!}
    </div>

    <div class="form-group col-md-6">
        {!! Form::text('city', old('$exLoc->city'), $attributes = array('class'=>'form-control', 'id'=>'city', 'placeholder'=>'City', 'required') ) !!}
    </div>

    <div class="form-group col-md-3">
        {!! Form::text('state', old('$exLoc->state'), $attributes = array('class'=>'form-control', 'id'=>'state', 'placeholder'=>'State', 'required') ) !!}
    </div>

    <div class="form-group col-md-3">
        {!! Form::text('zip', old('$exLoc->zip'), $attributes = array('class'=>'form-control', 'id'=>'zip', 'placeholder'=>'Zip', 'required') ) !!}
    </div>

    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Step Four: Contact Detail', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div class="form-group col-md-12">
        <div class="form-group col-md-3">
            {!! Form::label('contactOrg', 'Offered By', array('class' => 'control-label')) !!}
        </div>

        <div class="form-group col-md-9">
            {!! Form::text('contactOrg', old('$event->contactOrg') ?: $defaults->orgName, $attributes = array('class'=>'form-control has-feedback-left', 'placeholder'=> $current_person->defaultOrg->orgName, 'required') ) !!}
            <span class="fa fa-building form-control-feedback left" aria-hidden="true"></span>
        </div>
    </div>

    <div class="form-group col-md-12">
        <div class="form-group col-md-3">
            {!! Form::label('contactEmail', 'Organizer', array('class' => 'control-label')) !!}
        </div>

        <div class="form-group col-md-9">
            {!! Form::text('contactEmail', old('$event->contactEmail') ?: $defaults->eventEmail, $attributes = array('class'=>'form-control has-feedback-left', 'placeholder'=>'Contact Email', 'required') ) !!}
            <span class="fa fa-envelope form-control-feedback left" aria-hidden="true"></span>
        </div>
    </div>

    <div class="form-group col-md-12">
        <div class="form-group col-md-3">
            {!! Form::label('contactDetails', 'Additional Detail', array('class' => 'control-label')) !!}
        </div>

        <div class="form-group col-md-9">
            {!! Form::textarea('contactDetails', old('$event->contactDetails'), $attributes = array('class'=>'form-control', 'rows' => '5')) !!}
        </div>
    </div>
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Step Five: Event Logo', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div>

        <div class="form-group col-sm-4 col-md-4">
            {!! Form::label('contactDetails', 'Display Corporate Logo?', array('class' => 'control-label')) !!}
        </div>

        <div class="form-group col-sm-4 col-md-4">
            @if($event->eventID !== null && $event->showLogo != 1)
                {!! Form::label('showLogo', 'No', array('class' => 'control-label')) !!}
                {!! Form::checkbox('showLogo', '1', false, array('class' => 'flat js-switch')) !!}
                {!! Form::label('showLogo', 'Yes', array('class' => 'control-label')) !!}
            @else
                {!! Form::label('showLogo', 'No', array('class' => 'control-label')) !!}
                {!! Form::checkbox('showLogo', '1', true, array('class' => 'flat js-switch')) !!}
                {!! Form::label('showLogo', 'Yes', array('class' => 'control-label')) !!}
            @endif
        </div>

        <div class="form-group col-sm-4 col-md-4">
            <img src="{{ $orgLogoPath->orgPath . "/" . $orgLogoPath->orgLogo }}" alt=" Logo">
        </div>


    </div>
    @include('v1.parts.end_content')

    <div class="col-md-12">
        {!! Form::submit('Submit & Review Tickets', array('class' => 'btn btn-primary')) !!}
        <a href="/events" class="btn btn-default">Cancel</a>
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @include('v1.parts.footer-tinymce')
    @if($event->eventID !== null)
        <script>
            $(document).ready(function () {
                $('#eventStartDate').val(moment(new Date($('#eventStartDate').val())).format("MM/DD/YYYY HH:mm A"));
            });
            $(document).ready(function () {
                $('#eventEndDate').val(moment(new Date($('#eventEndDate').val())).format("MM/DD/YYYY HH:mm A"));
            });
        </script>
    @endif

    @include('v1.parts.footer-daterangepicker', ['fieldname' => 'eventStartDate', 'time' => 'true', 'single' => 'true'])
    @include('v1.parts.footer-daterangepicker', ['fieldname' => 'eventEndDate', 'time' => 'true', 'single' => 'true'])

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
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            var setContentHeight = function () {
                // reset height
                $RIGHT_COL.css('min-height', $(window).height());

                var bodyHeight = $BODY.outerHeight(),
                    footerHeight = $BODY.hasClass('footer_fixed') ? -10 : $FOOTER.height(),
                    leftColHeight = $LEFT_COL.eq(1).height() + $SIDEBAR_FOOTER.height(),
                    contentHeight = bodyHeight < leftColHeight ? leftColHeight : bodyHeight;

                // normalize content
                contentHeight -= $NAV_MENU.height() + footerHeight;

                $RIGHT_COL.css('min-height', contentHeight);
            };

            $SIDEBAR_MENU.find('a[href="/event/create"]').parent('li').addClass('current-page').parents('ul').slideDown(function () {
                setContentHeight();
            }).parent().addClass('active');

            @if($event->eventID !== null)
            $("#add").text('Edit Event');
            @endif
        });
    </script>

@endsection