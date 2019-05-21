<?php
/**
 * Comment:
 * Created: 2/9/2017
 */

$currentPerson = \App\Person::find(auth()->user()->id);
$today = \Carbon\Carbon::now();

$topBits = '';  // remove this if this was set in the controller

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])
@section('content')

    @if(!isset($event))

        @include('v1.parts.start_content', ['header' => $title, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
        @if(count($events) != 0)
            {!! Form::open(array('url' => env('APP_URL')."/group", 'method' => 'POST')) !!}
            {!! Form::label('eventID', trans('messages.instructions.select_event').':', array('class' => 'control-label')) !!}
            {!! Form::select('eventID', $events, old('$event->eventTypeID'), array('id' => 'eventID', 'class' =>'form-control')) !!}
            {!! Form::close() !!}
        @else
            <b>@lang('messages.instructions.no_events')</b>
        @endif
    @else
        @include('v1.parts.typeahead')
        @include('v1.parts.start_content', ['header' => $title, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

        @lang('messages.instructions.group_reg')

        {!! Form::open(array('url' => env('APP_URL')."/group-reg1", 'id' => 'grpreg')) !!}
        {!! Form::hidden('eventID', $event->eventID) !!}

        @for($i=1;$i<=15;$i++)

            <div id="custom-template" class="form-group col-sm-12">
                <div class="col-sm-2">
                    <a data-toggle="tooltip" title="{{ trans('messages.instructions.group_reg_search') }}">
                {!! Form::label('helper-'.$i, trans('messages.headers.search4p').':') !!}
                    </a><br />
                {!! Form::text('helper-'.$i, null, array('id' => 'helper-'.$i, 'class' => 'typeahead input-xs')) !!}<br />
                    <a id="pop-{{ $i }}" onclick="populate({{ $i }});" class="btn btn-primary btn-xs">@lang('messages.buttons.pop_row')</a>
                    <a id="clr-{{ $i }}" onclick="go_clear({{ $i }});" class="btn btn-danger btn-xs invisible">@lang('messages.buttons.clr_row')</a>
                <div id="search-results"></div>
                </div>
                <div class="col-sm-2">
                    {!! Form::label('firstName-'.$i, trans('messages.fields.firstName')) !!}<br/>
                    {!! Form::text('firstName-'.$i, null, array('id' => 'firstName-'.$i, 'class' => 'input-xs', 'onblur' => 'require('. $i .');')) !!}<br />
                </div>
                <div class="col-sm-2">
                    {!! Form::label('lastName-'.$i, trans('messages.fields.lastName')) !!}<br/>
                    {!! Form::text('lastName-'.$i, null, array('id' => 'lastName-'.$i, 'class' => 'input-xs')) !!}<br />
                </div>
                <div class="col-sm-2">
                    {!! Form::label('email-'.$i, trans('messages.headers.email')) !!}<br/>
                    {!! Form::text('email-'.$i, null, array('id' => 'email-'.$i, 'class' => 'input-xs')) !!}<br />
                </div>
                @if(is_numeric($tickets))
                    {!! Form::hidden('ticketID-'.$i, $tickets) !!}
                @else
                    <div class="col-sm-1">
                        {!! Form::label('ticketID-'.$i, trans('messages.fields.ticket')) !!}<br/>
                        {!! Form::select('ticketID-'.$i, $tickets, old('ticketID-'.$i), array('id' => 'ticketID-'.$i, 'class' =>'input-sm', 'style' => 'width:75px;')) !!}
                    </div>
                @endif

                @if(0)
                    {!! Form::hidden('override-'.$i, 0) !!}
                @else
                    <div class="col-sm-1">
                        {!! Form::label('override-'.$i, trans('messages.headers.override')) !!}
                        @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.group_reg')])
                        <br/>
                        {!! Form::number('override-'.$i, null, array('id' => 'override-'.$i, 'class' => 'input-xs', 'style' => 'width:75px;')) !!}<br />
                    </div>
                @endif
                <div class="col-sm-1">
                    {!! Form::label('pmiid-'.$i, trans('messages.fields.pmi_id')) !!}<br/>
                    {!! Form::number('pmiid-'.$i, null, array('id' => 'pmiid-'.$i, 'class' => 'input-xs', 'style' => 'width:75px;')) !!}<br />
                </div>
                <div class="col-sm-1">
                    {!! Form::label('code-'.$i, trans('messages.fields.disc')) !!}<br/>
                    {!! Form::select('code-'.$i, $discounts, array('id' => 'code-'.$i, 'class' => 'input-sm', 'style' => 'width:75px')) !!}<br />
                </div>
                @if($check)
                <div class="col-sm-1">
                    {!! Form::label('checkin-'.$i, trans('messages.buttons.chk_in')) !!}<br/>
                    {!! Form::checkbox('checkin-'.$i, 1, ['checked']) !!}
                    {!! Form::hidden('check', 1) !!}
                </div>
                @endif
                {{--
                <div class="col-sm-offset-8 col-sm-4" id="prices-{{ $i }}">
                    Words
                </div>
                --}}
            </div>

        @endfor

        {!! Form::submit('Submit', array('class' => 'btn btn-primary btn-sm')) !!}
        <div id="validation"></div>
        {!! Form::close() !!}

    @endif
    @include('v1.parts.end_content')

@endsection

@section('scripts')
@if(!isset($event))
    <script>
    $(document).ready(function () {
        var x;
        $('#eventID').on('change', function(){
            x = $(this).val();
            location.href = "{{ env('APP_URL') }}/group/" + x;
            {{--
            //location.href=this.options[this.selectedIndex].value
            //window.location = "{{ env('APP_URL') }}/group/" + x;
            --}}
        })
    });
    </script>
@else
    <script src="{{ env('APP_URL') }}/js/typeahead.bundle.min.js"></script>
    <script>
        $(document).ready(function ($) {
            var people = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                remote: {
                    url: '{{ env('APP_URL') }}/autocomplete/?l=p&q=%QUERY',
                    wildcard: '%QUERY'
                }
            });

            $('#custom-template .typeahead').typeahead(null, {
                name: 'people',
                display: 'value',
                limit: 10,
                source: people
            });
        });
    </script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        function require(row){
            var x;
            x = $("#firstName-"+row).val().length;
            if(x > 0){
                $("#lastName-"+row).attr('required', 'required');
                $("#email-"+row).attr('required', 'required');
                $("#ticketID-"+row).attr('required', 'required');
                {{--
                // Not required.
                $("#pmiid-"+row).attr('required', 'required');
                --}}
            }
        }
        function go_clear(row){
            $("#helper-"+row).val("");
            $("#firstName-"+row).val("");
            $("#lastName-"+row).val("");
            $("#email-"+row).val("");
            $("#pmiid-"+row).val("");
            $("#override-"+row).val("");
            $("#pmiid-"+row).val("");
            $("#ticketID-"+row).val("{{ trans('messages.headers.sel_tkt') }}");
            $("#code-"+row).val("{{ trans('messages.fields.select') }}");
            $("#clr-"+row).addClass("invisible");
        }

        function populate(row){
            var helper;
            helper = $("#helper-"+row).val();
            if(helper.length > 1 ){
                // lookup via ajax the personID in helper and place values into the other fields
                $.ajax({
                    type: 'POST',
                    cache: false,
                    async: true,
                    url: '{{ env('APP_URL') }}/getperson',
                    dataType: 'json',
                    data: {
                        string: helper
                    },
                    success: function (data) {
                        var result = eval(data);
                        console.log(result);
                        {{--
                        // parse output, add a hidden item (person+row), and populate text fields
                        //console.log(result);
                        --}}
                        $('<input />').attr('type', 'hidden')
                            .attr('name', "person-"+row)
                            .attr('value', result.personID)
                            .appendTo('#grpreg');
                        $("#firstName-"+row).val(result.firstName);
                        $("#lastName-"+row).val(result.lastName);
                        $("#email-"+row).val(result.login);
                        $("#pmiid-"+row).val(result.OrgStat1);
                        $("#firstName-"+row).blur();
                        $("#clr-"+row).removeClass("invisible");
                    },
                    error: function (data) {
                        var result = eval(data);
                        //console.log(result);
                    }
                });
            }
        }

        $("#grpreg").on('submit', function(f){
            var fail = 0;
            f.preventDefault();
            for(i=1; i <= 15; i++){
                if($("#firstName-"+i).val().length > 0){
                    if($("#ticketID-"+i).val() == 0){
                        fail = 1;
                    }
                }
            }
            if(fail == 0){
                this.submit();
            } else {
                $("#validation").html("<b style='color:red;'>You must select a ticket for each attendee.</b>");
            }
        });
    </script>
    @include('v1.parts.menu-fix', array('path' => '/group'))
@endif
@endsection