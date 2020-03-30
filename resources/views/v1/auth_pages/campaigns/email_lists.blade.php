@php
/**
 * Comment: Main Page for Email List Maintenance
 * Created: 9/4/2017
 */

$c = count($lists);
$d = count($defaults);
$default_header = ['List Name', 'Contacts', 'Create Date'];
$list_header = ['List Name', 'Description', 'Contacts', 'Create Date'];
$today = \Carbon\Carbon::now();
if(!isset($emailList)){
    $emailList = null;
}
$topBits = '';  // remove this if this was set in the controller
@endphp
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
<div class="col-md-12 col-sm-12 col-xs-12">
    <ul class="nav nav-tabs bar_tabs nav-justified" id="myTab" role="tablist">
        <li class="active">
            <a aria-expanded="true" data-toggle="tab" href="#tab_content1" id="lists-tab">
                <b>
                    Email Lists
                </b>
            </a>
        </li>
        <li class="">
            <a aria-expanded="false" data-toggle="tab" href="#tab_content2" id="create-tab">
                @if($emailList === null)
                <b>
                    Create New List
                </b>
                @else
                <b>
                    Edit List
                </b>
                @endif
            </a>
        </li>
    </ul>
    <div class="tab-content" id="tab-content">
        <div aria-labelledby="lists-tab" class="tab-pane active" id="tab_content1">
            <br/>
            @include('v1.parts.start_content', ['header' => "Campaign Lists", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

                @include('v1.parts.start_content', ['header' => "Default Lists ($d)", 'subheader' => '', 'w1' => '9', 'w2' => '9', 'r1' => 1, 'r2' => 0, 'r3' => 0])
                @include('v1.parts.datatable', ['headers' => $default_header, 'data' => $defaults, 'scroll' => 0])
                @include('v1.parts.end_content')

                @if($c>0)
                    @include('v1.parts.start_content', ['header' => "Custom Lists ($c)", 'subheader' => '', 'w1' => '9', 'w2' => '9', 'r1' => 1, 'r2' => 0, 'r3' => 0])
                    @include('v1.parts.datatable', ['headers' => $list_header, 'data' => $lists, 'scroll' => 0])
                    @include('v1.parts.end_content')
                @endif

                @include('v1.parts.end_content')
        </div>
        <div aria-labelledby="create-tab" class="tab-pane fade" id="tab_content2">
            <br/>
            @include('v1.parts.start_content', ['header' => "Create New List", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
                @if($emailList === null)
                    {!! Form::open(array('url' => env('APP_URL')."/list", 'method' => 'post','id'=>'create_email_list_form')) !!}
                @else
                    {!! Form::model($emailList, array('url' => env('APP_URL')."/list/".$emailList->id, 'method' => 'patch')) !!}
                @endif

                @if($emailList === null)
                    {!! Form::submit('Create List', array('style' => 'float:right;', 'class' => 'btn btn-primary btn-sm')) !!}
                @else
                    {!! Form::submit('Edit List', array('style' => 'float:right;', 'class' => 'btn btn-primary btn-sm')) !!}
                @endif

                To create an email list, you can select a foundation from which to start your list or filter it.
            <br/>
            You can then choose events from which to include or exclude people.
            <br/>
            <div class="form-group">
                <div class="col-sm-6">
                    {!! Form::label('name', "Name*") !!}
                        @if($emailList === null)
                            {!! Form::text('name', null, array('class' => 'form-control', 'maxlength' => 255, 'required')) !!}
                        @else
                            {!! Form::text('name', $emailList->listName, array('class' => 'form-control', 'maxlength' => 255, 'required')) !!}
                        @endif
                </div>
                <div class="col-sm-6">
                    {!! Form::label('description') !!}
                        @if($emailList === null)
                            {!! Form::text('description', null, array('class' => 'form-control', 'maxlength' => 255)) !!}
                        @else
                            {!! Form::text('description', $emailList->listDesc, array('class' => 'form-control', 'maxlength' => 255)) !!}
                        @endif
                </div>
            </div>
            @include('v1.parts.start_content', ['header' => "Foundation: Start with or filter by...", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
            <div class="col-sm-12">
                Selecting a foundation either gives you a starting point for your list or will filter from the event
                    lists you include (below).
                <p>
                </p>
            </div>
            <div class="col-sm-3">
                {!! Form::radio('foundation', 'none', true, array('class' => 'flat')) !!}
                    {!! Form::label('foundation', 'None - Skip') !!}
                <br/>
            </div>
            <div class="col-sm-3">
                {!! Form::radio('foundation', 'everyone', false, array('class' => 'flat')) !!}
                    {!! Form::label('foundation', 'Everyone') !!}
                <br/>
            </div>
            <div class="col-sm-3">
                {!! Form::radio('foundation', 'pmiid', false, array('class' => 'flat')) !!}
                    {!! Form::label('foundation', 'Current and Past Members') !!}
                <br/>
            </div>
            <div class="col-sm-3">
                {!! Form::radio('foundation', 'nonexpired', false, array('class' => 'flat')) !!}
                    {!! Form::label('foundation', 'Current Members') !!}
                <br/>
            </div>
            @include('v1.parts.end_content')
            <!-----------dragable list start ---------->
            <div class="col-md-6 col-xs-6 ">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>
                            Inclusions: Include attendees of...
                            <small>
                            </small>
                        </h2>
                        <ul class="nav navbar-right panel_toolbox">
                            <li>
                                <a class="collapse-link">
                                    <i aria-hidden="true" class="fa fa-chevron-up">
                                    </i>
                                </a>
                            </li>
                        </ul>
                        <div class="clearfix">
                        </div>
                    </div>
                    <div class="x_content" ondragover="allowDrop(event)" ondrop="drop(event,this,'include[]')" style="display: block;">
                        <div style="width: 100%">
                            Drop Here
                        </div>
                        <div class="clearfix" draggable="true" id="this-year-event" ondragstart="drag(event)">
                            <div class="col-md-6">
                                {!! Form::checkbox('include[]', 'current-year#'.$ytd_events, false, array('class' => 'flat')) !!}
                            {!! Form::label('include', "This Year's Events") !!}
                             {!! Form::text('eventStartDate', null, $attributes = array('class'=>'form-control', 'required', 'id' => 'eventStartDate') ) !!}
                            </div>
                        </div>
                        <div class="clearfix" draggable="true" id="last-year-event" ondragstart="drag(event)">
                            {!! Form::checkbox('include[]', 'last-year#'.$last_year, false, array('class' => 'flat','id'=>'last-year-event')) !!}
                            {!! Form::label('include', "Last Year's Events") !!}
                        </div>
                        {{--
                        <div class="clearfix" draggable="true" id="all-event" ondragstart="drag(event)">
                            {!! Form::checkbox('include[]', $pddays, false, array('class' => 'flat','id'=>'all-event')) !!}
                            {!! Form::label('include[]', 'All PD Day Events') !!}
                        </div>
                        --}}
                        @foreach($excludes as $e)
                            @php
                                $name = substr($e->eventName, 0, 60);
                                if(strlen($name) == 60) {
                                    $name .= "...";
                                }
                            @endphp
                        <div class="clearfix" draggable="true" id="event_{{$e->eventID}}" ondragstart="drag(event)">
                            {!! Form::checkbox('include[]', $e->eventID, false, array('class' => 'flat')) !!}
                            {!! Form::label('include', $name, array('textWrap' => 'true')) !!}
                            <br/>
                        </div>
                        @endforeach
                    </div>
                    <!-- x_content -->
                </div>
                <!-- x_panel -->
            </div>
            <div class="col-md-6 col-xs-6 ">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>
                            Exclusions: Exclude attendees of...
                            <small>
                            </small>
                        </h2>
                        <ul class="nav navbar-right panel_toolbox">
                            <li>
                                <a class="collapse-link">
                                    <i aria-hidden="true" class="fa fa-chevron-up">
                                    </i>
                                </a>
                            </li>
                        </ul>
                        <div class="clearfix">
                        </div>
                    </div>
                    <div class="x_content" ondragover="allowDrop(event)" ondrop="drop(event,this,'exclude[]')" style="display: block;">
                        <div style="width: 100%">
                            Drop Here
                        </div>
                    </div>
                    <!-- x_content -->
                </div>
                <!-- x_panel -->
            </div>
            <!-----------dragable list end --------->
            {{-- @include('v1.parts.start_content', ['header' => "Inclusions: Include attendees of...", 'subheader' => '', 'w1' => '6', 'w2' => '6', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                {!! Form::checkbox('include[]', $ytd_events, false, array('class' => 'flat')) !!}
                {!! Form::label('include[]', "This Year's Events") !!}
            <br/>
            {!! Form::checkbox('include[]', $last_year, false, array('class' => 'flat')) !!}
                {!! Form::label('include[]', "Last Year's Events") !!}
            <br/>
            {!! Form::checkbox('include[]', $pddays, false, array('class' => 'flat')) !!}
                {!! Form::label('include[]', 'All PD Day Events') !!}
            <br/>
            @include('v1.parts.end_content')

                @include('v1.parts.start_content', ['header' => "Inclusions: Include attendees of...", 'subheader' => '', 'w1' => '6', 'w2' => '6', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                @foreach($excludes as $e)
                    @php
                        $name = substr($e->eventName, 0, 60);
                        if(strlen($name) == 60) {
                            $name .= "...";
                        }
                    @endphp
            <nobr>
                {!! Form::checkbox('exclude[]', $e->eventID, false, array('class' => 'flat')) !!}
                        {!! Form::label('exclude[]', $name, array('textWrap' => 'true')) !!}
                <br/>
            </nobr>
            @endforeach

                @include('v1.parts.end_content') --}}

                {{-- {!! Form::submit('Create List', array('style' => 'float:left;', 'class' => 'btn btn-primary btn-sm')) !!} --}}
                {!! Form::button('Create List', array('style' => 'float:left;', 'class' => 'btn btn-primary btn-sm','onclick'=>'create_list()')) !!}
                {!! Form::close() !!}
                {{-- @include('v1.parts.end_content') --}}
            <div class="errors" id="errors">
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    //redirection to a specific tab
        $(document).ready(function () {
            $('#myTab a[href="#{{ old('tab') }}"]').tab('show')
        });

    function create_list(){
        var formElements = {'include[]':[],'exclude[]':[]};
        $('#create_email_list_form :input[type=checkbox],#create_email_list_form :input[type=text],#create_email_list_form :input[type=radio]').each(function(){
            var current = $(this);
            var input_name = $(this).attr('name');
            var val  = $(this).val(); 
            if($(this).attr('type') == 'checkbox' && $(this).prop("checked") == true){
                formElements[input_name].push(val);
            } else if($(this).attr('type') == 'text') {
                formElements[input_name] = val;
            } else if($(this).attr('type') == 'radio' && $(this).prop("checked") == true) {
                formElements[input_name] = val;
            }
        });
        $('#errors').html('');
        // console.log('here',formElements);
        $.ajax({
            url: '{{route("EmailList.Save")}}', 
            method:'POST',
            dataType:'json',
            data: formElements,
            success: function(result){
                if(result.success == true){
                    window.location = result.redirect_url;
                } else {
                    if(result.errors_validation){
                        $.each(result.errors_validation,function(key,value){
                            var str = '<div class="alert alert-danger"><a aria-label="close" class="close" data-dismiss="alert" href="#">×</a>'+value[0]+'</div>';
                        $('#errors').append(str);
                        });
                    }
                    if(result.errors){
                         $.each(result.errors,function(key,value){
                            var str = '<div class="alert alert-danger"><a aria-label="close" class="close" data-dismiss="alert" href="#">×</a>'+value+'</div>';
                            $('#errors').append(str);
                        });
                    }
                }
            },
            error(xhr,status,error){
                console.log(status);
            }
        });
    }
    function allowDrop(ev) {
      ev.preventDefault();
      if (ev.target.getAttribute("draggable") == "true"){
        ev.dataTransfer.dropEffect = "none"; // dropping is not allowed[]
      } else {
        ev.dataTransfer.dropEffect = "all"; // drop it like it's hot
      }
    }

    function drag(ev) {
      ev.dataTransfer.setData("text/plain", ev.target.id);
    }

    function drop(ev,el,type) {
      ev.preventDefault();
      var data = ev.dataTransfer.getData("text");
      el.appendChild(document.getElementById(data));
      $('#'+data).find('input[type=checkbox]').attr('name',type);
    }

    $(document).ready(function () {
          $('#eventStartDate').daterangepicker({
                    autoUpdateInput: true,
                    showDropdowns: true,
                    startDate: moment().startOf('year'),
                    endDate: moment().endOf('year'),
                    timePicker: false,
                    locale: {
                        format: 'M/D/Y'
                    }
                });
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

            $SIDEBAR_MENU.find('a[href="{{ env('APP_URL') }}/lists"]').parent('li').addClass('current-page').parents('ul').slideDown(function () {
                setContentHeight();
            }).parent().addClass('active');
        });
</script>
@endsection
