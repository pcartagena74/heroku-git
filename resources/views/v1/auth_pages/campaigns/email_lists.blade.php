@php
/**
 * Comment: Main Page for Email List Maintenance
 * Created: 9/4/2017
 */

$c = count($lists);
$d = count($defaults);
$default_header = ['List Name', 'Contacts', 'Create Date'];
$list_header = ['List Name', 'Description', 'Contacts', 'Create Date','Actions'];
$today = \Carbon\Carbon::now();
if(!isset($emailList)){
    $emailList = null;
}
$topBits = '';  // remove this if this was set in the controller

$this_year_chk = false;
$last_year_chk = false;
$pd_chk        = false;
$specific_chk  = false;
$specific_exclude_chk = false;
$specific_date = null;
$specific_exclude_date = null;
$specific_exclude_list = [];
$specific_event_list = [];
$exclude_list = [];
$spe_date_ts = 0;
if(!empty($emailList->metadata)){
    $metadata = json_decode($emailList->metadata);
    // ["last-year#102","pd#6,69,97","specific","6","28","109","110","113","118","477","478","479","480","481","482"]
    if(!empty($metadata->include)){
        foreach ($metadata->include as $event_id) {
            if (strpos($event_id, 'this-year#') === 0) {
                $this_year_chk = true;  
            } else if (strpos($event_id, 'last-year#') === 0) {
                $last_year_chk = true;  
            } else if (strpos($event_id, 'pd#') === 0) {
                $pd_chk = true;  
            } else if (strpos($event_id, 'specific') === 0) {
                $specific_chk = true;  
            }
            if(is_numeric($event_id)){
                $specific_event_list[$event_id] = $event_id;
            }
        }
    }
    if(!empty($metadata->exclude)){
        foreach($metadata->exclude as $event_id){
            if ($event_id == 'specific_exclude') {
                $specific_exclude_chk = true;
            }
        }
    }
    if(!empty($metadata->eventStartDate)){
        $specific_date = explode('-',$metadata->eventStartDate);
        $start_date = getJavaScriptDate($specific_date[0]);
        $end_date = getJavaScriptDate($specific_date[1]);
        $specific_date['start'] = $start_date;
        $specific_date['end'] = $end_date;
    }
    if(!empty($metadata->eventStartDateExclude)){
        $specific_exclude_date = explode('-',$metadata->eventStartDateExclude);
        $start_date = getJavaScriptDate($specific_exclude_date[0]);
        $end_date = getJavaScriptDate($specific_exclude_date[1]);
        $specific_exclude_date['start'] = $start_date;
        $specific_exclude_date['end'] = $end_date;
    }
    if(!empty($emailList->excluded)){
        $exclude_list = array_flip(explode(',',$emailList->excluded));
        $specific_exclude_list = $exclude_list;
    }
}

@endphp

@extends('v1.layouts.auth', ['topBits' => $topBits])
@section('content')
<style type="text/css">
    .daterangepicker.opensright .ranges, .daterangepicker.opensright .calendar, .daterangepicker.openscenter .ranges, .daterangepicker.openscenter .calendar {
     float: left; //fix for datepicker
}
.list-group-scroll{
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

.list-group-mine .list-group-item .highlight {
  background-color: #d3d2d2;
}

.list-group-mine .list-group-item:nth-child(1):first hover {
  background-color: #d3d2d2;
}
.list-group-mine .list-group-item.inner:nth-child(1):first hover {
  background-color: #eeeeee;
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
 .list-group-mine > .list-group-item {
    padding-left: 30px;
}
.inline{
    width: auto;
    display: inline-block;    
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
                    {{ html()->form('POST', env('APP_URL') . "/list")->id('create_email_list_form')->open() }}
                @else
                    {{ html()->modelForm($emailList, 'PATCH', env('APP_URL') . "/list/" . $emailList->id)->id('update_email_list_form')->open() }}
                @endif

                @if($emailList === null)
                    {{ html()->button(trans('messages.buttons.create_list'), 'button')->style('float:right;')->class('btn btn-primary btn-sm')->attribute('onclick', 'createList(this)') }}
                @else
                    {{ html()->button(trans('messages.buttons.edit_list'), 'button')->style('float:right;')->class('btn btn-primary btn-sm')->attribute('onclick', 'updateList(this)') }}
                @endif

                {!! trans('messages.instructions.email_list_foundation') !!}
            <div class="form-group">
                <div class="col-sm-6">
                    {{ html()->label("Name*", 'name') }}
                        @if($emailList === null)
                            {{ html()->text('name')->class('form-control')->maxlength(255)->required() }}
                        @else
                            {{ html()->text('name', $emailList->listName)->class('form-control')->maxlength(255)->required() }}
                        @endif
                </div>
                <div class="col-sm-6">
                    {{ html()->label('description') }}
                        @if($emailList === null)
                            {{ html()->text('description')->class('form-control')->maxlength(255) }}
                        @else
                            {{ html()->text('description', $emailList->listDesc)->class('form-control')->maxlength(255) }}
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
                {{ html()->radio('foundation', $fou_none, 'none')->class('flat') }}
                    {{ html()->label('None - Skip', 'none') }}
                <br/>
            </div>
            <div class="col-sm-3">
                {{ html()->radio('foundation', $fou_everyone, 'everyone')->class('flat') }}
                    {{ html()->label('Everyone', 'everyone') }}
                <br/>
            </div>
            <div class="col-sm-3">
                {{ html()->radio('foundation', $fou_pmiid, 'pmiid')->class('flat') }}
                    {{ html()->label('Current and Past Members', 'current_past') }}
                <br/>
            </div>
            <div class="col-sm-3">
                {{ html()->radio('foundation', $fou_nonexpired, 'nonexpired')->class('flat') }}
                    {{ html()->label('Current Members', 'current_member') }}
                <br/>
            </div>
            @include('v1.parts.end_content')
            <!-----------dragable list start ---------->
            <div class="col-md-12 col-xs-12 ">
                <div class="x_panel">
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
                                <div class="col-md-12">
                                    {!! trans('messages.instructions.email_list_general')!!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="clearfix hidden">
                                <div class="col-sm-12">
                                    {!! trans('messages.instructions.email_list_inclusion')!!}
                                </div>
                            </div>
                            <ul class="list-group list-group-mine draggable-list list-group-scroll" id="include-list">
                                <li class="list-group-item highlight">
                                    {{trans('messages.headers.email_list_inclusion')}}
                                    {{-- {!! trans('messages.fields.inclusion_list')!!} --}}
                                </li>
                                <li class="list-group-item" id="this-year-event">
                                    {{--
                                    <i aria-hidden="true" class="fa fa-arrows">
                                    </i>
                                    --}}
                                    {{ html()->checkbox('include[]', $this_year_chk, 'this-year#' . $ytd_events)->class('flat')->id('this-year-event-list') }}
                            {{ html()->label(trans('messages.fields.this_year_event'), 'include') }}
                                </li>
                                <li class="list-group-item" id="last-year-event">
                                    {{--
                                    <i aria-hidden="true" class="fa fa-arrows">
                                    </i>
                                    --}}
                                    {{ html()->checkbox('include[]', $last_year_chk, 'last-year#' . $last_year)->class('flat')->id('last-year-event-list') }}
                            {{ html()->label(trans('messages.fields.last_year_event'), 'include') }}
                                </li>
                                <li class="list-group-item" id="pd-event">
                                    {{--
                                    <i aria-hidden="true" class="fa fa-arrows">
                                    </i>
                                    --}}
                                    {{ html()->checkbox('include[]', $pd_chk, 'pd#' . $pddays)->class('flat')->attribute('onchange', 'allPDEvent(this)')->data('type', 'pd-events')->id('pd-event-list') }}
                                    {{ html()->label(trans('messages.fields.all_pd_day_event'), 'include') }}
                                </li>
                                <li class="list-group-item" id="specific-events">
                                    {{--
                                    <i aria-hidden="true" class="fa fa-arrows">
                                    </i>
                                    --}}
                                    <div class="form-group">
                                        {{ html()->checkbox('include[]', $specific_chk, 'specific')->class('flat')->id('specific-events') }}
                            {{ html()->label(trans('messages.fields.specific_event'), 'include') }}

                             {{ html()->text('eventStartDate')->attributes($attributes = array('class'=>'form-control inline hidden', 'required', 'id' => 'eventStartDate')) }}
                                    </div>
                                    <ul class="list-group list-group-mine" id="specific-events-list">
                                    </ul>
                                </li>
                                {{--  @foreach($excludes as $e)
                                @php
                                    $name = substr($e->eventName, 0, 60);
                                    if(strlen($name) == 60) {
                                        $name .= "...";
                                    }
                                @endphp
                                <li class="list-group-item" id="event_{{$e->eventID}}">
                                    <i aria-hidden="true" class="fa fa-arrows">
                                    </i>
                                    {{ html()->checkbox('include[]', false, $e->eventID)->class('flat') }}
                                {{ html()->label($name, 'include')->attribute('textWrap', 'true')->class('font-weight-normal') }}
                                    <input name="include[]" type="hidden" value="{{$e->eventID}}">
                                    </input>
                                </li>
                                @endforeach
                                --}}
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <div class="clearfix hidden">
                                <div class="col-sm-12">
                                    {!! trans('messages.instructions.email_list_exclusion')!!}
                                </div>
                            </div>
                            <ul class="list-group list-group-mine draggable-list list-group-scroll" id="exclude-list">
                                <li class="list-group-item">
                                    {{ trans('messages.headers.email_list_exclusion')}}
                                    {{-- {!! trans('messages.fields.exclusion_list')!!} --}}
                                </li>
                                @if(empty($emailList))
                                <li class="list-group-item">
                                    {{--
                                    <i aria-hidden="true" class="fa fa-arrows">
                                    </i>
                                    --}}
                                    <div class="form-group">
                                        {{ html()->checkbox('exclude[]', $specific_exclude_chk, 'specific_exclude')->class('flat')->id('specific-events-exclude') }}
                                        {{ html()->label(trans('messages.fields.specific_event'), 'include') }}

                                        {{ html()->text('eventStartDateExclude')->attributes($attributes = array('class'=>'form-control inline hidden', 'required', 'id' => 'eventStartDateExclude')) }}
                                    </div>
                                    <ul class="list-group list-group-mine" id="specific-events-exclude-list">
                                    </ul>
                                </li>
                                @endif
                                {{-- @if(!empty($emailList->excluded))
                                    @foreach($excluded_list as $e)
                                        @php
                                            $name = substr($e->eventName, 0, 60);
                                            if(strlen($name) == 60) {
                                                $name .= "...";
                                            }
                                        @endphp
                                <li class="list-group-item" id="event_{{$e->eventID}}">
                                    <i aria-hidden="true" class="fa fa-arrows">
                                    </i>
                                    {{ html()->checkbox('include[]', false, $e->eventID)->class('flat') }} --}}
                                        {{-- {{ html()->label($name, 'include')->attribute('textWrap', 'true')->class('font-weight-normal') }}
                                    <input name="include[]" type="hidden" value="{{$e->eventID}}">
                                    </input>
                                </li>
                                @endforeach
                                @endif --}}
                            </ul>
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

                {{ html()->checkbox('include[]', false, $ytd_events)->class('flat') }}
                {{ html()->label("This Year's Events", 'include[]') }}
            <br/>
            {{ html()->checkbox('include[]', false, $last_year)->class('flat') }}
                {{ html()->label("Last Year's Events", 'include[]') }}
            <br/>
            {{ html()->checkbox('include[]', false, $pddays)->class('flat') }}
                {{ html()->label('All PD Day Events', 'include[]') }}
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
                {{ html()->checkbox('exclude[]', false, $e->eventID)->class('flat') }}
                        {{ html()->label($name, 'exclude[]')->attribute('textWrap', 'true') }}
                <br/>
            </nobr>
            @endforeach

                @include('v1.parts.end_content') --}}

                {{-- {{ html()->submit('Create List')->style('float:left;')->class('btn btn-primary btn-sm') }} --}}
                @if($emailList === null)
                    {{ html()->button(trans('messages.buttons.create_list'), 'button')->style('float:left;')->class('btn btn-primary btn-sm')->attribute('onclick', 'createList(this)') }}
                @else
                    {{ html()->button(trans('messages.buttons.edit_list'), 'button')->style('float:left;')->class('btn btn-primary btn-sm')->attribute('onclick', 'updateList(this)') }}
                @endif
                
                {{ html()->closeModelForm() }}
                {{-- @include('v1.parts.end_content') --}}
            <div class="errors" id="errors">
            </div>
        </div>
    </div>
</div>
@endsection
@section('modals')
<!--- Modals -->
<div aria-hidden="true" aria-labelledby="delete_email_list" class="modal fade" id="delete_email_list" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" class="close" data-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        ×
                    </span>
                </button>
                <h4 class="modal-title">
                    {{ trans('messages.email_list_popup.delete.title') }}
                </h4>
            </div>
            <div class="modal-body">
                <p>
                    {{ trans('messages.email_list_popup.delete.body') }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-warning" onclick="setExitPopButtonValue('yes')" type="button">
                    {{ trans('messages.email_list_popup.delete.btn_yes') }}
                </button>
                <button class="btn btn-success" onclick="setExitPopButtonValue('no')" type="button">
                    {{ trans('messages.email_list_popup.delete.btn_no') }}
                </button>
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
        function initializeIcheck(){
            $("input.flat").iCheck({checkboxClass:"icheckbox_flat-green",radioClass:"iradio_flat-green"});
        }
        $(document).on('ifChanged','#create_email_list_form :input[type=checkbox],#update_email_list_form :input[type=checkbox] ', function(event){
            let ths = $(event.currentTarget);
            let state = ths.is(':checked');
            if(ths.attr('id') === 'specific-events' && state == true){
                $('#eventStartDate').removeClass('hidden').show();
                $('#specific-events-list').removeClass('hidden').show();
                // let picker = eventStartDate();
                var drp = $('#eventStartDate').data('daterangepicker');
                specificEventsFilter(null,drp);
                initializeIcheck();
            } else if(ths.attr('id') === 'specific-events' && state == false){
                $('#eventStartDate').addClass('hidden').hide();
                $('#specific-events-list').html('');
                $('#specific-events-list').addClass('hidden').hide();
            }

            if(ths.attr('id') === 'this-year-event-list' && state == true){
                let html = '<li class="list-group-item"><ul class="list-group list-group-mine" data-list_name="this-year-event">';
                html += '<label>{{trans('messages.fields.this_year_event')}}</label>';
                let year_date = @json($ytd_events_list);
                $.each(year_date, function(index,value){
                    let checkbox = '<input class="flat" name="exclude[]" type="checkbox" value="'+index+'">&nbsp;';
                    html += '<li class="list-group-item ui-sortable-handle" id="event_'+index+'">'+checkbox+'<label for="exclude" textwrap="true" class="font-weight-normal"> '+value['name']+'</label></li>';
                    // $('#specific-events-list').append(str);
                });
                html += '</ul></li>';
                $('#exclude-list').append(html);
                removeSpecificExcludeList();
                initializeIcheck();
            } else if(ths.attr('id') === 'this-year-event-list' && state == false){
                $.each($('#exclude-list').find('ul'),function(index,value){
                    if($(value).data('list_name') === 'this-year-event'){
                        $(value).parent('li').remove();
                    }
                });
            }
            if(ths.attr('id') === 'last-year-event-list' && state == true){
                let html = '<li class="list-group-item"><ul class="list-group list-group-mine" data-list_name="last-year-event">';
                html += '<label>{{trans('messages.fields.last_year_event')}}</label>';
                let year_date = @json($last_year_events_list);
                $.each(year_date, function(index,value){
                    let checkbox = '<input class="flat" name="exclude[]" type="checkbox" value="'+index+'">&nbsp;';
                    html += '<li class="list-group-item ui-sortable-handle" id="event_'+index+'">'+checkbox+'<label for="exclude" textwrap="true" class="font-weight-normal"> '+value['name']+'</label></li>';
                });
                html += '</ul></li>';
                $('#exclude-list').append(html);
                removeSpecificExcludeList();
                initializeIcheck();
            } else if(ths.attr('id') === 'last-year-event-list' && state == false){
                $.each($('#exclude-list').find('ul'),function(index,value){
                    if($(value).data('list_name') === 'last-year-event'){
                        $(value).parent('li').remove();
                    }
                });
            }

            if(ths.attr('id') === 'pd-event-list' && state == true){
                let html = '<li class="list-group-item"><ul class="list-group list-group-mine" data-list_name="pd-event">';
                html += '<label>{{trans('messages.fields.all_pd_day_event')}}</label>';
                let year_date = @json($pd_day_events_list);
                $.each(year_date, function(index,value){
                    let checkbox = '<input class="flat" name="exclude[]" type="checkbox" value="'+index+'">&nbsp;';
                    html += '<li class="list-group-item ui-sortable-handle" id="event_'+index+'">'+checkbox+'<label for="exclude" textwrap="true" class="font-weight-normal"> '+value['name']+'</label></li>';
                });
                html += '</ul></li>';
                $('#exclude-list').append(html);
                removeSpecificExcludeList();
                initializeIcheck();
            } else if(ths.attr('id') === 'pd-event-list' && state == false){
                $.each($('#exclude-list').find('ul'),function(index,value){
                    if($(value).data('list_name') === 'pd-event'){
                        $(value).parent('li').remove();
                    }
                });
            }

            if(ths.attr('id') === 'specific-events-exclude' && state == true){
                $('#eventStartDateExclude').removeClass('hidden').show();
                $('#specific-events-exclude-list').removeClass('hidden').show();
                // let picker = eventStartDate();
                var drp = $('#eventStartDateExclude').data('daterangepicker');
                specificEventsFilterExclude(null,drp);
                initializeIcheck();
            } else if(ths.attr('id') === 'specific-events-exclude' && state == false){
                $('#eventStartDateExclude').addClass('hidden').hide();
                $('#specific-events-exclude-list').html('');
                $('#specific-events-exclude-list').addClass('hidden').hide();
            }

            if(ths.closest('ul').attr('id') === 'specific-events-list' && state == false) {
                // ths.remove();
                let t = $(ths).closest('li').attr('id');
                $(ths).closest('li').remove()
                // console.log('ere',t,$(ths).closest('li').remove());
            } else {
                // console.log('not false');
            }

            if(ths.closest('ul').attr('id') === 'specific-events-exclude-list' && state == false) {
                // ths.remove();
                let t = $(ths).closest('li').attr('id');
                $(ths).closest('li').remove()
                // console.log('ere',t,$(ths).closest('li').remove());
            } else {
                // console.log('not false');
            }
            let pel = $('#pd-event-list').is(':checked');
            let lyel = $('#last-year-event-list').is(':checked');
            let yel = $('#this-year-event-list').is(':checked');
            if(pel == false && lyel == false && yel == false ){
                addSpecificExcludeList();
            }
            if(ths.closest('ul').data('list_name') == 'this-year-event'){
                let all_check_box = ths.closest('ul').find('input[type="checkbox"]');
                if(all_check_box.length == all_check_box.filter(':checked').length){
                    $('#this-year-event-list').iCheck('uncheck');
                    ths.closest('ul').closest('li').remove();
                }
            }
            if(ths.closest('ul').data('list_name') == 'last-year-event'){
                let all_check_box = ths.closest('ul').find('input[type="checkbox"]');
                if(all_check_box.length == all_check_box.filter(':checked').length){
                    $('#last-year-event-list').iCheck('uncheck');
                    ths.closest('ul').closest('li').remove();
                }
            }
            if(ths.closest('ul').data('list_name') == 'pd-event'){
                let all_check_box = ths.closest('ul').find('input[type="checkbox"]');
                if(all_check_box.length == all_check_box.filter(':checked').length){
                    $('#pd-event-list').iCheck('uncheck');
                    ths.closest('ul').closest('li').remove();
                }
            }

        });
    function removeSpecificExcludeList(){
         $('#specific-events-exclude-list').closest('li').remove()
    }
    function addSpecificExcludeList(){
        if($('#exclude-list #specific-events-exclude-list').length > 0){
            return ;
        }
        let specific_exclude_chk = '{{$specific_exclude_chk}}';
        let checked = '';
        if(specific_exclude_chk == true){
            checked = 'checked';
        }
        let html = '';
        html += '<li class="list-group-item">';
        html += '<div class="form-group">';
        html += '<input class="flat" id="specific-events-exclude" name="exclude[]" type="checkbox" value="specific_exclude" '+checked+'>&nbsp;';
        html += '<label for="exclude">{{trans('messages.fields.specific_event')}}</label>';
        html += '&nbsp;<input class="form-control inline" required="" id="eventStartDateExclude" name="eventStartDateExclude" type="text">';
        html += '</div><ul class="list-group list-group-mine" id="specific-events-exclude-list"></ul></li>';
        $('#exclude-list').append(html);
        eventStartDateExclude();
        initializeIcheck();

    }
    function generateListForEdit(){
        let this_year_chk = '{{$this_year_chk}}';
        let last_year_chk = '{{$last_year_chk}}';
        let pd_chk = '{{$pd_chk}}';
        let specific_chk = '{{$specific_chk}}';
        let specific_exclude_chk = '{{$specific_exclude_chk}}';
        let exclude_list = @json($exclude_list);
        if(specific_chk == true){
            $('#eventStartDate').removeClass('hidden').show();
            $('#specific-events-list').removeClass('hidden').show();
            var drp = $('#eventStartDate').data('daterangepicker');
            specificEventsFilter(null,drp,specific_chk);
            initializeIcheck();
        } else if(this_year_chk == false){
            $('#eventStartDate').addClass('hidden').hide();
            $('#specific-events-list').html('');
            $('#specific-events-list').addClass('hidden').hide();
        }

        if(this_year_chk == true){
            let html = '<li class="list-group-item"><ul class="list-group list-group-mine" data-list_name="this-year-event">';
            html += '<label>{{trans('messages.fields.this_year_event')}}</label>';
            let year_date = @json($ytd_events_list);
            $.each(year_date, function(index,value){
                let checked = '';
                if(exclude_list[index] >= 0){
                    checked = 'checked';
                }
                let checkbox = '<input class="flat" name="exclude[]" type="checkbox" value="'+index+'" '+checked+'>&nbsp;';
                html += '<li class="list-group-item ui-sortable-handle" id="event_'+index+'">'+checkbox+'<label for="exclude" textwrap="true" class="font-weight-normal"> '+value['name']+'</label></li>';
                // $('#specific-events-list').append(str);
            });
            html += '</ul></li>';
            $('#exclude-list').append(html);
            initializeIcheck();
        } else if(this_year_chk == false){
            $.each($('#exclude-list').find('ul'),function(index,value){
                if($(value).data('list_name') === 'this-year-event'){
                    $(value).parent('li').remove();
                }
            });
        }
        if(last_year_chk == true){
            let html = '<li class="list-group-item"><ul class="list-group list-group-mine" data-list_name="last-year-event">';
            html += '<label>{{trans('messages.fields.last_year_event')}}</label>';
            let year_date = @json($last_year_events_list);
            $.each(year_date, function(index,value){
                let checked = '';
                if(exclude_list[index] >= 0){
                    checked = 'checked';
                }
                let checkbox = '<input class="flat" name="exclude[]" type="checkbox" value="'+index+'" '+checked+'>&nbsp;';
                html += '<li class="list-group-item ui-sortable-handle" id="event_'+index+'">'+checkbox+'<label for="exclude" textwrap="true" class="font-weight-normal"> '+value['name']+'</label></li>';
            });
            html += '</ul></li>';
            $('#exclude-list').append(html);
            initializeIcheck();
        } else if(last_year_chk == false){
            $.each($('#exclude-list').find('ul'),function(index,value){
                if($(value).data('list_name') === 'last-year-event'){
                    $(value).parent('li').remove();
                }
            });
        }

        if(pd_chk == true){
            let html = '<li class="list-group-item"><ul class="list-group list-group-mine" data-list_name="pd-event">';
            html += '<label>{{trans('messages.fields.all_pd_day_event')}}</label>';
            let year_date = @json($pd_day_events_list);
            $.each(year_date, function(index,value){
                let checked = '';
                if(exclude_list[index] >= 0){
                    checked = 'checked';
                }
                let checkbox = '<input class="flat" name="exclude[]" type="checkbox" value="'+index+'" '+checked+'>&nbsp;';
                html += '<li class="list-group-item ui-sortable-handle" id="event_'+index+'">'+checkbox+'<label for="exclude" textwrap="true" class="font-weight-normal"> '+value['name']+'</label></li>';
            });
            html += '</ul></li>';
            $('#exclude-list').append(html);
            initializeIcheck();
        } else if(pd_chk == false){
            $.each($('#exclude-list').find('ul'),function(index,value){
                if($(value).data('list_name') === 'pd-event'){
                    $(value).parent('li').remove();
                }
            });
        }

        if(specific_exclude_chk == true){
            addSpecificExcludeList();
            $('#eventStartDateExclude').removeClass('hidden').show();
            $('#specific-events-exclude-list').removeClass('hidden').show();
            eventStartDateExclude();
            var drp = $('#eventStartDateExclude').data('daterangepicker');
            specificEventsFilterExclude(null,drp,true);
            initializeIcheck();
        } else if(specific_exclude_chk == false){
            $('#eventStartDateExclude').addClass('hidden').hide();
            $('#specific-events-exclude-list').html('');
            $('#specific-events-exclude-list').addClass('hidden').hide();
        }

            // if(ths.closest('ul').attr('id') === 'specific-events-list' && state == false) {
            //     // ths.remove();
            //     let t = $(ths).closest('li').attr('id');
            //     $(ths).closest('li').remove()
            //     // console.log('ere',t,$(ths).closest('li').remove());
            // } else {
            //     // console.log('not false');
            // }
    }
    function allPDEvent(ths){
        if($(ths).is(':checked')){
            $('#create_email_list_form :input[type=checkbox]').each(function(){
                $(this).attr('checked','checked');
            });
        }
    }
    function setExitPopButtonValue(btn_press){
        switch(btn_press) {
          case 'yes':
            $('#popup_save_before_exit').modal('hide');
                $.ajax({
                url: '{{route("EmailList.Delete")}}', 
                method:'POST',
                dataType:'json',
                data: {'id':delete_list_id},
                success: function(result){
                    delete_list_id ='';
                    window.location = result.redirect_url;
                },
                error(xhr,status,error){
                    delete_list_id='';
                    console.log(status);
                }
            });
            break;
          case 'no':
            $('#delete_email_list').modal('hide');
            break;
        }
    }
    var delete_list_id = '';
    function confim_delete(id){
        delete_list_id = id;
        $('#delete_email_list').modal('show');
    }
 
    function eventStartDate(){
        let dates = @json($all_event_min_max_date);
        let min = moment().set('year',dates['min']['year']).set('month',dates['min']['month']).set('day',dates['min']['day']);
        let max = moment().set('year',dates['max']['year']).set('month',dates['max']['month']).set('day',dates['max']['day']);
        max = moment().endOf('year');
        let start = min;
        let end = max;
        let dft = @json($specific_date);
        if(dft){
            let st = dft['start'];
            let ed = dft['end'];
            start = new Date(st);
            end = new Date(ed);
        }
        $('#eventStartDate').daterangepicker({
            autoUpdateInput: true,
            showDropdowns: true,
            startDate: start,
            endDate: end,
            timePicker: false,
            locale: {
                format: 'M/D/Y'
            },
            minDate: min,
            maxDate: max,
            open:'right'
        });
        
        eventStartDateExclude();
    }

    function eventStartDateExclude(){
        let dates = @json($all_event_min_max_date);
        let min = moment().set('year',dates['min']['year']).set('month',dates['min']['month']).set('day',dates['min']['day']);
        let max = moment().set('year',dates['max']['year']).set('month',dates['max']['month']).set('day',dates['max']['day']);
        max = moment().endOf('year');
        let start = min;
        let end = max;
        let dft = @json($specific_exclude_date);
        if(dft){
            let st = dft['start'];
            let ed = dft['end'];
            start = new Date(st);
            end = new Date(ed);
        }
        $('#eventStartDateExclude').daterangepicker({
            autoUpdateInput: true,
            showDropdowns: true,
            startDate: start,
            endDate: end,
            timePicker: false,
            locale: {
                format: 'M/D/Y'
            },
            minDate: min,
            maxDate: max,
            open:'right'
        });
    }

    function specificEventsFilter(ev, picker,foredit = false){
        let start = new Date(picker.startDate.format('YYYY-MM-DD'));
        let end = new Date(picker.endDate.format('YYYY-MM-DD'));
        let year_date = @json($all_events_list);
        let html = '';
        if(foredit){
            let specific_event_list = @json($specific_event_list);
            $.each(year_date, function(index,value){
                if(specific_event_list[index] >= 0){
                    let checkbox = '<input class="flat" name="include[]" type="checkbox" value="'+index+'" checked>&nbsp;'
                    html += '<li class="list-group-item ui-sortable-handle" id="event_'+index+'">'+checkbox+'<label for="include" textwrap="true" class="font-weight-normal"> '+value['name']+'</label></li>';
                    $('#specific-events-list').html(html);
                }
            });    
            initializeIcheck();
            return;
        }
        $.each(year_date, function(index,value){
            let event_date = new Date(value['date']);
            if(event_date.getTime() >= start.getTime() && event_date.getTime() <= end.getTime()){
                //between
                let checkbox = '<input class="flat" name="include[]" type="checkbox" value="'+index+'" checked>&nbsp;'
                html += '<li class="list-group-item ui-sortable-handle" id="event_'+index+'">'+checkbox+'<label for="include" textwrap="true" class="font-weight-normal"> '+value['name']+'</label></li>';
                    // $('#specific-events-list').append(str);
            } else {
                //not between
                // console.log('not betweeen');
                // $('#event_'+index).remove();
            }
        });
        $('#specific-events-list').html(html);
        initializeIcheck();
    }

    function specificEventsFilterExclude(ev, picker,foredit = false){
        let start = new Date(picker.startDate.format('YYYY-MM-DD'));
        let end = new Date(picker.endDate.format('YYYY-MM-DD'));
        let year_date = @json($all_events_list);
        let html = '';
        if(foredit){
            let specific_exclude_list = @json($specific_exclude_list);
            $.each(year_date, function(index,value){
                if(specific_exclude_list[index] >=0 ){
                    let checkbox = '<input class="flat" name="exclude[]" type="checkbox" value="'+index+'" checked>&nbsp;'
                    html += '<li class="list-group-item ui-sortable-handle" id="event_'+index+'">'+checkbox+'<label for="exclude" textwrap="true" class="font-weight-normal"> '+value['name']+'</label></li>';
                    $('#specific-events-exclude-list').html(html);
                }
            });    
            initializeIcheck();
            return;
        }
        $.each(year_date, function(index,value){
            let event_date = new Date(value['date']);
            // console.log('here',event_date.getTime(),start.getTime(),end.getTime());
            if(event_date.getTime() >= start.getTime() && event_date.getTime() <= end.getTime()){
                //between
                let checkbox = '<input class="flat" name="exclude[]" type="checkbox" value="'+index+'" checked>&nbsp;'
                html += '<li class="list-group-item ui-sortable-handle" id="event_'+index+'">'+checkbox+'<label for="exclude" textwrap="true" class="font-weight-normal"> '+value['name']+'</label></li>';
                    // $('#specific-events-list').append(str);
            } else {
                //not between
                // console.log('not betweeen');
                // $('#event_'+index).remove();
            }
        });
        $('#specific-events-exclude-list').html(html);
        initializeIcheck();
    }
    $('#eventStartDate').on('apply.daterangepicker', function(ev, picker) {
        specificEventsFilter(ev, picker);
    });
    $('#eventStartDateExclude').on('apply.daterangepicker', function(ev, picker) {
        specificEventsFilterExclude(ev, picker);
    });

    function createList(ths){
        var formElements = {'include[]':[],'exclude[]':[]};
        $('#create_email_list_form :input[name="include[]"]').each(function(){
            var val  = $(this).val();
            if($(this).attr('type') == 'checkbox' && $(this).prop("checked") == true){
                formElements['include[]'].push(val);
            }

        });
        $('#create_email_list_form :input[name="exclude[]"]').each(function(){
            var val  = $(this).val(); 
            if($(this).attr('type') == 'checkbox' && $(this).prop("checked") == true){
                formElements['exclude[]'].push(val);
            }
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
            var val  = $(this).val();
            if($(this).attr('type') == 'checkbox' && $(this).prop("checked") == true){
                formElements['include[]'].push(val);
            }

        });
        $('#update_email_list_form :input[name="exclude[]"]').each(function(){
            var val  = $(this).val(); 
            if($(this).attr('type') == 'checkbox' && $(this).prop("checked") == true){
                formElements['exclude[]'].push(val);
            }
        });
        $('#update_email_list_form :input[type=radio],#update_email_list_form :input[type=text]').each(function(){
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

    $(document).ready(function () {
        eventStartDate();
        @if(!empty($emailList))
            generateListForEdit();
        @endif
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
