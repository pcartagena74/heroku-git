<?php
/**
 * Comment:
 * Created: 2/9/2017
 */

$currentPerson = \App\Person::find(auth()->user()->id);
$today = \Carbon\Carbon::now();
$string = '';

$topBits = '';  // remove this if this was set in the controller
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
{{--
    {!! Form::open(array('url' => env('APP_URL')."/getperson", 'method' => 'POST')) !!}
    {!! Form::hidden('string', '357-blah') !!}
    {!! Form::submit('test') !!}
    {!! Form::close() !!}
--}}
    @include('v1.parts.typeahead')

    @include('v1.parts.start_content', ['header' => $title, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    @if(!isset($event))
        {!! Form::open(array('url' => env('APP_URL')."/group", 'method' => 'POST')) !!}
        {!! Form::label('eventID', 'Select the event for registration:', array('class' => 'control-label')) !!}
        {!! Form::select('eventID', $events, old('$event->eventTypeID'), array('id' => 'eventID', 'class' =>'form-control')) !!}
        {!! Form::close() !!}
    @else
        You can register up to 15 attendees for this event at a time.<br />
        <b>Please keep the following in mind:</b>
        <ul>
            <li>Attendees will need to have records in this system.  You can find them typing the name, email or PMI ID of the desired attendee in the search box.</li>
            <li>If a record doesn't already exist, enter the information in the fields and it will be created.</li>
            <li>If this event has sessions, you will <b>NOT</b> be able to select them for the attendees.</li>
            <li>An email confirmation will be sent to each attendee, with a link to select sessions if applicable.</li>
        </ul>

        {!! Form::open(array('url' => env('APP_URL')."/group-reg1", 'id' => 'grpreg')) !!}
        {!! Form::hidden('eventID', $event->eventID) !!}

        @for($i=1;$i<=15;$i++)

            {{-- Form::open(array('url' => env('APP_URL')."/merge/". $letter, 'method' => 'post')) --}}
            <div id="custom-template" class="form-group col-sm-12">
                <div class="col-sm-2">
                {!! Form::label('helper-'.$i, 'Search for person:') !!}<br/>
                {!! Form::text('helper-'.$i, null, array('id' => 'helper-'.$i, 'class' => 'typeahead input-xs')) !!}<br />
                    <a id="pop-{{ $i }}" onclick="populate({{ $i }});" class="btn btn-primary btn-xs">Populate Row</a>
                <div id="search-results"></div>
                </div>
                <div class="col-sm-2">
                    {!! Form::label('firstName-'.$i, 'First Name') !!}<br/>
                    {!! Form::text('firstName-'.$i, null, array('id' => 'firstName-'.$i, 'class' => 'input-xs', 'onblur' => 'require('. $i .');')) !!}<br />
                </div>
                <div class="col-sm-2">
                    {!! Form::label('lastName-'.$i, 'Last Name') !!}<br/>
                    {!! Form::text('lastName-'.$i, null, array('id' => 'lastName-'.$i, 'class' => 'input-xs')) !!}<br />
                </div>
                <div class="col-sm-2">
                    {!! Form::label('email-'.$i, 'Email') !!}<br/>
                    {!! Form::text('email-'.$i, null, array('id' => 'email-'.$i, 'class' => 'input-xs')) !!}<br />
                </div>
                <div class="col-sm-2">
                    {!! Form::label('ticketID-'.$i, 'Ticket') !!}<br/>
                    {!! Form::select('ticketID-'.$i, $tickets, old('ticketID-'.$i), array('id' => 'ticketID-'.$i, 'class' =>'input-sm', 'style' => 'width:150px;')) !!}
                </div>
                <div class="col-sm-1">
                    {!! Form::label('pmiid-'.$i, 'PMI ID') !!}<br/>
                    {!! Form::text('pmiid-'.$i, null, array('id' => 'pmiid-'.$i, 'class' => 'input-xs', 'style' => 'width:75px;')) !!}<br />
                </div>
                <div class="col-sm-1">
                    {!! Form::label('code-'.$i, 'Discount') !!}<br/>
                    {!! Form::select('code-'.$i, $discounts, array('id' => 'code-'.$i, 'class' => 'input-sm', 'style' => 'width:75px')) !!}<br />
                </div>
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
            //location.href=this.options[this.selectedIndex].value
            //window.location = "{{ env('APP_URL') }}/group/" + x;
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
                    url: '{{ env('APP_URL') }}/autocomplete/?{!! $string !!}q=%QUERY',
                    wildcard: '%QUERY'
                }
            });

            $('#custom-template .typeahead').typeahead(null, {
                name: 'people',
                display: 'value',
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
                $("#pmiid-"+row).attr('required', 'required');
            }
        }
        function populate(row){
            var helper;
            helper = $("#helper-"+row).val();
            if(helper != ''){
                // lookup via ajax the personID in helper and shove the values into the other fields
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
                        // parse output, add a hidden item (person+row), and populate text fields
                        var result = eval(data);
                        //console.log(result);
                        $('<input />').attr('type', 'hidden')
                            .attr('name', "person-"+row)
                            .attr('value', result.personID)
                            .appendTo('#grpreg');
                        $("#firstName-"+row).val(result.firstName);
                        $("#lastName-"+row).val(result.lastName);
                        $("#email-"+row).val(result.login);
                        $("#pmiid-"+row).val(result.OrgStat1);
                        $("#firstName-"+row).blur();
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

            $SIDEBAR_MENU.find('a[href="/group"]').parent('li').addClass('current-page').parents('ul').slideDown(function () {
                setContentHeight();
            }).parent().addClass('active');

            @if($event->eventID !== null)
            $("#grp").text('Group Registration');
            @endif
        });
    </script>
@endif
@endsection