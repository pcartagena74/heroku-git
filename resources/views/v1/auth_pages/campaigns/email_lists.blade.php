@php
/**
 * Comment: Main Page for Email List Maintenance
 * Created: 9/4/2017
 */

$c = count($lists);
$d = count($defaults);
$default_header = ['List Name', 'Contacts', 'Create Date'];
$list_header = ['List Name', 'Description', 'Contacts', 'Create Date','Action'];
$today = \Carbon\Carbon::now();
if(!isset($emailList)){
    $emailList = null;
}
$topBits = '';  // remove this if this was set in the controller
@endphp
@extends('v1.layouts.auth', ['topBits' => $topBits])
@section('content')
<style type="text/css">
    .daterangepicker.opensright .ranges, .daterangepicker.opensright .calendar, .daterangepicker.openscenter .ranges, .daterangepicker.openscenter .calendar {
     float: left; //fix for datepicker
}
.list-group-mine{
    overflow: auto;
    max-height: 300px;
    min-height: 300px;
}
.list-group-mine .list-group-item {
  background-color: #eeeeee;
  /*border-top: 1px solid #0091b5;*/
  /*border-left-color: #fff;
  border-right-color: #fff;*/
}

.list-group-mine .list-group-item:hover {
  background-color: #ffffff;
}

.list-group-mine .list-group-item:nth-child(1) {
  background-color: #d3d2d2;
}

.list-group-mine .list-group-item:nth-child(1):first hover {
  background-color: #d3d2d2;
}
.dragged {
  position: absolute;
  opacity: 0.5;
  z-index: 2000;
}
.list-group-mine .placeholder{
    position: relative;
    margin: 0;
    padding: 0;
    border: none;
}
.list-group-mine .placeholder::before{
    position: absolute;
    content: "";
    width: 0;
    height: 0;
    margin-top: -5px;
    left: -5px;
    top: -4px;
    border: 5px solid transparent;
    border-left-color: $error;
    border-right: none;

}
.font-weight-normal {
    font-weight: normal;
}
</style>
<div class="col-md-12 col-sm-12 col-xs-12">
    <ul class="nav nav-tabs bar_tabs nav-justified" id="myTab" role="tablist">
        <li class="{{ $emailList === null ? 'active':''}}">
            <a aria-expanded="true" data-toggle="tab" href="#tab_content1" id="lists-tab">
                <b>
                    {{trans('messages.headers.email_list')}}
                </b>
            </a>
        </li>
        <li class="{{ $emailList != null ? 'active':''}}">
            <a aria-expanded="false" data-toggle="tab" href="#tab_content2" id="create-tab">
                @if($emailList === null)
                <b>
                    {{trans('messages.headers.email_list_create')}}
                </b>
                @else
                <b>
                    {{trans('messages.headers.email_list_edit')}}
                </b>
                @endif
            </a>
        </li>
    </ul>
    <div class="tab-content" id="tab-content">
        <div aria-labelledby="lists-tab" class="tab-pane {{ $emailList === null ? 'active':'fade'}}" id="tab_content1">
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
        <div aria-labelledby="create-tab" class="tab-pane {{ $emailList != null ? 'active':'fade'}}" id="tab_content2">
            @include('v1.parts.start_content', ['header' => trans('messages.headers.email_list_new'), 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
                @if($emailList === null)
                    {!! Form::open(array('url' => env('APP_URL')."/list", 'method' => 'post','id'=>'create_email_list_form')) !!}
                @else
                    {!! Form::model($emailList, array('url' => env('APP_URL')."/list/".$emailList->id, 'method' => 'patch','id'=>'update_email_list_form')) !!}
                @endif

                @if($emailList === null)
                    {!! Form::button(trans('messages.buttons.create_list'), array('style' => 'float:right;', 'class' => 'btn btn-primary btn-sm','onclick'=>'createList(this)')) !!}
                @else
                    {!! Form::button(trans('messages.buttons.edit_list'), array('style' => 'float:right;', 'class' => 'btn btn-primary btn-sm','onclick'=>'updateList(this)')) !!}
                @endif

                {!! trans('messages.instructions.email_list_foundation') !!}
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
            @include('v1.parts.start_content', ['header' => trans('messages.headers.email_list_foundation'), 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
            @php
            $fou_none = true;
            $fou_everyone = false;
            $fou_pmiid = false;
            $fou_nonexpired = false;
            if(!empty($emailList->foundation)){
                $fou_none = false;
                switch ($emailList->foundation) {
                    case 'none':
                        $fou_none = true;
                        break;
                    case 'everyone':
                        $fou_everyone = true;
                        break;
                    case 'pmiid':
                        $fou_pmiid = true;
                        break;
                    case 'nonexpired':
                        $fou_nonexpired = true;
                        break;
                }
                if($emailList->foundation == 'none'){

                }
            }
            @endphp
            <div class="col-sm-12">
                {!! trans('messages.instructions.email_list_foundation_select') !!}
            </div>
            <div class="col-sm-3">
                {!! Form::radio('foundation', 'none', $fou_none, array('class' => 'flat')) !!}
                    {!! Form::label('none', 'None - Skip') !!}
                <br/>
            </div>
            <div class="col-sm-3">
                {!! Form::radio('foundation', 'everyone', $fou_everyone, array('class' => 'flat')) !!}
                    {!! Form::label('everyone', 'Everyone') !!}
                <br/>
            </div>
            <div class="col-sm-3">
                {!! Form::radio('foundation', 'pmiid', $fou_pmiid, array('class' => 'flat')) !!}
                    {!! Form::label('current_past', 'Current and Past Members') !!}
                <br/>
            </div>
            <div class="col-sm-3">
                {!! Form::radio('foundation', 'nonexpired', $fou_nonexpired, array('class' => 'flat')) !!}
                    {!! Form::label('current_member', 'Current Members') !!}
                <br/>
            </div>
            @include('v1.parts.end_content')
            <!-----------dragable list start ---------->
            <div class="col-md-12 col-xs-12 ">
                <div class="x_panel" ondragover="allowDrop(event)" ondrop="drop(event,this,'include[]')">
                    <div class="x_title" draggable="false">
                        <h2>
                            Event lists
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
                    <div class="x_content">
                        <div class="row">
                            <div class="form-group">
                                <div class="col-md-6">
                                    {!! Form::label('include', 'Filter '.trans('messages.fields.this_year_event')) !!}
                                        {!! Form::text('eventStartDate', null, $attributes = array('class'=>'form-control', 'required', 'id' => 'eventStartDate') ) !!}
                                    <input name="include[]" type="hidden" value="current-year#.{{$ytd_events}}">
                                    </input>
                                </div>
                            </div>
                            {!! trans('messages.instructions.email_list_this_year')!!}
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group list-group-mine draggable-list" id="include-list">
                                <li class="list-group-item" draggable="true" id="this-year-event">
                                    {{trans('messages.headers.email_list_inclusion')}}
                                    {{-- {!! trans('messages.fields.inclusion_list')!!} --}}
                                </li>
                                <li class="list-group-item" draggable="true" id="last-year-event">
                                    <i aria-hidden="true" class="fa fa-arrows">
                                    </i>
                                    {{-- {!! Form::checkbox('include[]', 'last-year#'.$last_year, false, array('class' => 'flat')) !!} --}}
                            {!! Form::label('include', trans('messages.fields.last_year_event')) !!}
                                    <input name="include[]" type="hidden" value="last-year#.{{$last_year}}">
                                    </input>
                                </li>
                                <li class="list-group-item" draggable="true" id="all-event">
                                    <i aria-hidden="true" class="fa fa-arrows">
                                    </i>
                                    {{-- {!! Form::checkbox('include[]', $pddays, false, array('class' => 'flat','onchange'=>'allPDEvent(this)','data-type'=>'all-events')) !!} --}}
                                    <input name="include[]" type="hidden" value="{{$pddays}}">
                                    </input>
                                    {!! Form::label('include', trans('messages.fields.all_pd_day_event')) !!}
                                </li>
                                @foreach($excludes as $e)
                                @php
                                    $name = substr($e->eventName, 0, 60);
                                    if(strlen($name) == 60) {
                                        $name .= "...";
                                    }
                                @endphp
                                <li class="list-group-item" draggable="true" id="event_{{$e->eventID}}">
                                    <i aria-hidden="true" class="fa fa-arrows">
                                    </i>
                                    {{-- {!! Form::checkbox('include[]', $e->eventID, false, array('class' => 'flat')) !!} --}}
                                {!! Form::label('include', $name, array('textWrap' => 'true','class'=>'font-weight-normal')) !!}
                                    <input name="include[]" type="hidden" value="{{$e->eventID}}">
                                    </input>
                                </li>
                                @endforeach
                            </ul>
                            <div class="clearfix" draggable="false">
                                <div class="col-sm-12">
                                    {!! trans('messages.instructions.email_list_inclusion')!!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group list-group-mine draggable-list" id="exclude-list">
                                <li class="list-group-item">
                                    {{ trans('messages.headers.email_list_exclusion')}}
                                    {{-- {!! trans('messages.fields.exclusion_list')!!} --}}
                                </li>
                                @if(!empty($emailList->excluded))
                            @foreach($excluded_list as $e)
                            @php
                                $name = substr($e->eventName, 0, 60);
                                if(strlen($name) == 60) {
                                    $name .= "...";
                                }
                            @endphp
                                <li class="list-group-item" draggable="true" id="event_{{$e->eventID}}">
                                    <i aria-hidden="true" class="fa fa-arrows">
                                    </i>
                                    {{-- {!! Form::checkbox('include[]', $e->eventID, false, array('class' => 'flat')) !!} --}}
                                {!! Form::label('include', $name, array('textWrap' => 'true','class'=>'font-weight-normal')) !!}
                                    <input name="include[]" type="hidden" value="{{$e->eventID}}">
                                    </input>
                                </li>
                                @endforeach
                            @endif
                            </ul>
                            <div class="clearfix">
                                <div class="col-sm-12">
                                    {!! trans('messages.instructions.email_list_exclusion')!!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- x_content -->
                </div>
                <!-- x_panel -->
            </div>
            <div class="col-md-6 col-xs-6 hide">
                <div class="x_panel">
                    <div class="x_title" draggable="false">
                        <h2>
                            {{trans('messages.headers.email_list_exclusion')}}
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
                @if($emailList === null)
                    {!! Form::button(trans('messages.buttons.create_list'), array('style' => 'float:left;', 'class' => 'btn btn-primary btn-sm','onclick'=>'createList(this)')) !!}
                @else
                    {!! Form::button(trans('messages.buttons.edit_list'), array('style' => 'float:left;', 'class' => 'btn btn-primary btn-sm','onclick'=>'updateList(this)')) !!}
                @endif
                
                {!! Form::close() !!}
                {{-- @include('v1.parts.end_content') --}}
            <div class="errors" id="errors">
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js">
</script>
<script>
    //redirection to a specific tab
        $(document).ready(function () {
            $('#myTab a[href="#{{ old('tab') }}"]').tab('show')
        });

        $('#create_email_list_form :input[type=checkbox]').on('ifChecked', function(event){
            // var ths = $(event.currentTarget);
            // if(ths.data('type') === 'all-events'){
            //     console.log('here');
            //     $('#create_email_list_form :input[type=checkbox]').iCheck('check');
            //     // $('#create_email_list_form').find(':input[type=checkbox]').attr('checked','checked');
            // }
            // console.log(ths.data('type'),event.currentTarget);
        });
    function allPDEvent(ths){
        if($(ths).is(':checked')){
            $('#create_email_list_form :input[type=checkbox]').each(function(){
                $(this).attr('checked','checked');
            });
        }
    }
    function createList(ths){
        var formElements = {'include[]':[],'exclude[]':[]};
        $('#create_email_list_form :input[name="include[]"]').each(function(){
            var val  = $(this).val(); 
            formElements['include[]'].push(val);
        });
        $('#create_email_list_form :input[name="exclude[]"]').each(function(){
            var val  = $(this).val(); 
            formElements['exclude[]'].push(val);
        });
        $('#create_email_list_form :input[type=radio],#create_email_list_form :input[type=text]').each(function(){
            var current = $(this);
            var input_name = $(this).attr('name');
            var val  = $(this).val(); 
            if($(this).attr('type') == 'text') {
                formElements[input_name] = val;
            } else if($(this).attr('type') == 'radio' && $(this).prop("checked") == true) {
                formElements[input_name] = val;
            }
        });
        $(ths).attr("disabled", true);
        $('#errors').html('');
        // console.log('here',formElements);
        $.ajax({
            url: '{{route("EmailList.Save")}}', 
            method:'POST',
            dataType:'json',
            data: formElements,
            success: function(result){
                $(ths).removeAttr("disabled");
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
                $(ths).removeAttr("disabled");
                console.log(status);
            }
        });
    }

    function updateList(ths){
        let formElements = {'include[]':[],'exclude[]':[]};
        @if(!empty($emailList->id))
        formElements['id'] = '{{$emailList->id}}';
        @endif
        $('#update_email_list_form :input[name="include[]"]').each(function(){
            let val  = $(this).val(); 
            formElements['include[]'].push(val);
        });
        $('#update_email_list_form :input[name="exclude[]"]').each(function(){
            let val  = $(this).val(); 
            formElements['exclude[]'].push(val);
        });
        $('#update_email_list_form :input[type=radio],#update_email_list_form :input[type=text]').each(function(){
            let current = $(this);
            let input_name = $(this).attr('name');
            let val  = $(this).val(); 
            if($(this).attr('type') == 'text') {
                formElements[input_name] = val;
            } else if($(this).attr('type') == 'radio' && $(this).prop("checked") == true) {
                formElements[input_name] = val;
            }
        });
        $(ths).attr("disabled", true);
        $('#errors').html('');
        // console.log('here',formElements);
        $.ajax({
            url: '{{route("EmailList.Update")}}', 
            method:'POST',
            dataType:'json',
            data: formElements,
            success: function(result){
                $(ths).removeAttr("disabled");
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
                $(ths).removeAttr("disabled");
                console.log(status);
            }
        });
    }

   $("#include-list").sortable({
      connectWith:'#exclude-list',
      containment: 'document',
      placeholder: 'placeholder',
      items: 'li:not(:first)',
      cursor: 'pointer',
      revert: true,
      opacity: 0.4,
      receive:function(event,ui){
        let item = ui.item;
        let c = item.find('input[name="exclude[]"]').attr('name','include[]');
      }
    }).disableSelection();

    $("#exclude-list").sortable({
      connectWith:'#include-list',
      containment: 'document',
      placeholder: 'placeholder',
      items: 'li:not(:first)',
      cursor: 'pointer',
      revert: true,
      opacity: 0.4,
      receive:function(event,ui){
        let item = ui.item;
        let c = item.find('input[name="include[]"]').attr('name','exclude[]');
      }
    }).disableSelection();

    function allowDrop(ev) {
      // ev.preventDefault();
      // if (ev.target.getAttribute("draggable") == "true"){
      //   ev.dataTransfer.dropEffect = "none"; // dropping is not allowed[]
      // } else {
      //   ev.dataTransfer.dropEffect = "all"; // drop it like it's hot
      // }
    }

    function drag(ev) {
      // ev.dataTransfer.setData("text/plain", ev.target.id);
    }

    function drop(ev,el,type) {
      // ev.preventDefault();
      // var data = ev.dataTransfer.getData("text");
      // el.appendChild(document.getElementById(data));
      // $('#'+data).find('input[type=checkbox]').attr('name',type);
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
            },
            minDate: moment().startOf('year'),
            maxDate: moment().endOf('year'),
            open:'right'
        });
        $('#eventStartDate').on('apply.daterangepicker', function(ev, picker) {
            let start = new Date(picker.startDate.format('YYYY-MM-DD'));
            let end = new Date(picker.endDate.format('YYYY-MM-DD'));
            let year_date = @json($ytd_events_date);
            $.each(year_date, function(index,value){
                let event_date = new Date(value['date']);
                if(event_date.getTime() >= start.getTime() && event_date.getTime() <= end.getTime()){
                    //between
                    if($('#event_'+index).length == 0)
                    {
                        let str = '<li class="list-group-item ui-sortable-handle" draggable="true" id="event_'+index+'"><i aria-hidden="true" class="fa fa-arrows">&nbsp</i><label for="include" textwrap="true" class="font-weight-normal"> '+value['name']+'</label><input name="include[]" type="hidden" value="'+index+'"></li>';
                        $('#include-list').append(str);
                    }
                } else {
                    //not between
                    $('#event_'+index).remove();
                }
            });
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
