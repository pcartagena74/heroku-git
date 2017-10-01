<?php
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
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    <div class="col-md-12 col-sm-12 col-xs-12">
        <ul id="myTab" class="nav nav-tabs bar_tabs nav-justified" role="tablist">
            <li class="active"><a href="#tab_content1" id="lists-tab" data-toggle="tab"
                                  aria-expanded="true"><b>Email Lists</b></a></li>
            <li class=""><a href="#tab_content2" id="create-tab" data-toggle="tab"
                            aria-expanded="false">
                    @if($emailList === null)
                        <b>Create New List</b>
                    @else
                        <b>Edit List</b>
                    @endif
                </a></li>
        </ul>

        <div id="tab-content" class="tab-content">
            <div class="tab-pane active" id="tab_content1" aria-labelledby="lists-tab">
                &nbsp;<br/>

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

            <div class="tab-pane fade" id="tab_content2" aria-labelledby="create-tab">
                &nbsp;<br/>
                @include('v1.parts.start_content', ['header' => "Create New List", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
                @if($emailList === null)
                    {!! Form::open(array('url' => env('APP_URL')."/list", 'method' => 'post')) !!}
                @else
                    {!! Form::model($emailList, array('url' => env('APP_URL')."/list/".$emailList->id, 'method' => 'patch')) !!}
                @endif

                @if($emailList === null)
                    {!! Form::submit('Create List', array('style' => 'float:right;', 'class' => 'btn btn-primary btn-sm')) !!}
                @else
                    {!! Form::submit('Edit List', array('style' => 'float:right;', 'class' => 'btn btn-primary btn-sm')) !!}
                @endif

                To create an email list, you can select a foundation from which to start your list or filter it.<br/>
                You can then choose events from which to include or exclude people.<br/>
                &nbsp;
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

                &nbsp;
                @include('v1.parts.start_content', ['header' => "Foundation: Start with or filter by...", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
                <div class="col-sm-12">
                    Selecting a foundation either gives you a starting point for your list or will filter from the event
                    lists you include (below).
                    <p>
                </div>
                <div class="col-sm-3">
                    {!! Form::radio('foundation', 'none', true, array('class' => 'flat')) !!}
                    {!! Form::label('foundation', 'None - Skip') !!}<br/>
                </div>

                <div class="col-sm-3">
                    {!! Form::radio('foundation', 'everyone', false, array('class' => 'flat')) !!}
                    {!! Form::label('foundation', 'Everyone') !!}<br/>
                </div>

                <div class="col-sm-3">
                    {!! Form::radio('foundation', 'pmiid', false, array('class' => 'flat')) !!}
                    {!! Form::label('foundation', 'Current and Past Members') !!}<br/>
                </div>


                <div class="col-sm-3">
                    {!! Form::radio('foundation', 'nonexpired', false, array('class' => 'flat')) !!}
                    {!! Form::label('foundation', 'Current Members') !!}<br/>
                </div>

                @include('v1.parts.end_content')


                @include('v1.parts.start_content', ['header' => "Inclusions: Include attendees of...", 'subheader' => '', 'w1' => '6', 'w2' => '6', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                {!! Form::checkbox('include[]', $ytd_events, false, array('class' => 'flat')) !!}
                {!! Form::label('include[]', "This Year's Events") !!}<br/>

                {!! Form::checkbox('include[]', $last_year, false, array('class' => 'flat')) !!}
                {!! Form::label('include[]', "Last Year's Events") !!}<br/>

                {!! Form::checkbox('include[]', $pddays, false, array('class' => 'flat')) !!}
                {!! Form::label('include[]', 'All PD Day Events') !!}<br/>

                @include('v1.parts.end_content')

                @include('v1.parts.start_content', ['header' => "Exclusions: Exclude attendees of...", 'subheader' => '', 'w1' => '6', 'w2' => '6', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                @foreach($excludes as $e)
                    <?php
                    $name = substr($e->eventName, 0, 60);
                    if(strlen($name) == 60) {
                        $name .= "...";
                    }
                    ?>
                    <nobr>
                        {!! Form::checkbox('exclude[]', $e->eventID, false, array('class' => 'flat')) !!}
                        {!! Form::label('exclude[]', $name, array('textWrap' => 'true')) !!}<br/>
                    </nobr>
                @endforeach

                @include('v1.parts.end_content')

                {!! Form::submit('Create List', array('style' => 'float:left;', 'class' => 'btn btn-primary btn-sm')) !!}
                {!! Form::close() !!}
                @include('v1.parts.end_content')
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
    </script>
    <script>
        $(document).ready(function () {
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