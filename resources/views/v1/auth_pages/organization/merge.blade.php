<?php
/**
 * Comment:
 * Created: 2/9/2017
 */

$topBits = '';  // remove this if this was set in the controller
if($model1) {
    $columns = Schema::getColumnListing($model1->getTable());
}
if($model1){
    $string = 'm='.$model1->personID.'&';
} else {
    $string = '';
}
$ignore_array = array('firstName', 'lastName', 'login', 'defaultOrgID', 'personID');
$suppress_array = array('creatorID', 'createDate', 'updaterID', 'updateDate', 'defaultOrgID', 'lastLoginDate', 'deleted_at');
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
@include('v1.parts.typeahead')

    @include('v1.parts.start_content', ['header' => 'Record Merging', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

    @if($collection && !isset($model1))
        {!! Form::open(array('url' => env('APP_URL')."/merge/". $letter, 'method' => 'post')) !!}
        <div id="custom-template" class="form-group col-sm-12">
            {!! Form::label('model1', 'Type the name, email or PMI ID of the record that should survive.') !!}<br/>
            {!! Form::text('model1', null, array('id' => 'model', 'class' => 'form-control typeahead input-sm')) !!}
            {!! Form::submit('Retrieve Record', array('class' => 'btn btn-sm btn-primary')) !!}
            <div id="search-results"></div>
        </div>
        {!! Form::close() !!}
    @else
        @if($model2 !== null)
            <div class="col-sm-9">
        @else
            <div class="col-sm-6">
        @endif
                @if($model2 !== null)
                    {!! Form::open(array('url' => env('APP_URL').'/execute_merge', 'method' => 'post')) !!}
                    {!! Form::hidden('model1', $model1->personID, array('id' => 'model1')) !!}
                    {!! Form::hidden('model2', $model2->personID, array('id' => 'model2')) !!}
                    {!! Form::hidden('letter', $letter, array('id' => 'letter')) !!}
                    {!! Form::hidden('ignore_array', implode(",", $ignore_array), array('id' => 'ignore_array')) !!}
                    {!! Form::hidden('columns', implode(",", $columns), array('id' => 'columns')) !!}
                    If there are values in the <b>Merge Candidate</b> you wish to overwrite, select its radio button before submitting.
                @endif
                        <table class="table table-condensed table-striped table-responsive jambo_table">
                            <thead>
                            <tr valign="top">
                                <th style="text-align: left;">Data Fields</th>
                                <th style="text-align: left;">
                                    The Keeper
                                    @if($model1 !== null && $model2 !== null)
                                    <a href="{{ env('APP_URL') }}/merge/{{ $letter }}/{{ $model2->personID }}/{{ $model1->personID }}"
                                       class="btn btn-xs btn-success pull-right">
                                        <i data-toggle="tooltip" title="Swap Candidates" class="fas fa-sync-alt"></i>
                                    </a>
                                    @endif
                                </th>
                                @if($model2 !== null)
                                    <th style="text-align: left;">Merge Candidate</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td style="text-align: right;">
                                    PMI ID:<br />
                                    PMI Type:
                                </td>
                                <td style="text-align: left;">
                                    {{ $model1->orgperson->OrgStat1 }}<br />
                                    {{ $model1->orgperson->OrgStat2 }}
                                </td>
                                @if($model2 !== null)
                                    <td style="text-align: left;">
                                        {{ $model2->orgperson->OrgStat1 }}<br />
                                        {{ $model2->orgperson->OrgStat2 }}
                                    </td>
                                @endif
                            </tr>
                            @foreach($columns as $c)
                                @if(!in_array($c, $suppress_array))
                                <tr>
                                    <td style="text-align: right;">
                                        {{ $c }}:
                                    </td>
                                    <td style="text-align: left;">
                                        {!! $model1->$c or '<i>null</i>' !!}
                                        <br />
                                        @if(!in_array($c, $ignore_array) && isset($model2))
                                            {!! Form::radio($c, 1, true, $attributes=array('required', 'id' => $c.'1')) !!}
                                        @endif
                                    </td>
                                    @if(isset($model2))
                                        <td style="text-align: left;">
                                            {!! $model2->$c or '<i>null</i>' !!}
                                            <br />
                                            @if(!in_array($c, $ignore_array))
                                                {!! Form::radio($c, 2, false, $attributes=array('required', 'id' => $c.'2')) !!}
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                @if($model2)
                {!! Form::submit('Merge Record', array('class' => 'btn btn-sm btn-primary')) !!}
                @endif
            </div>
                    @if(isset($model2))
                        <div class="col-sm-3">
                            @include('v1.parts.start_content',
                            ['header' => 'Merge Notes', 'subheader' => '',
                            'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                            <b>Note:</b> Records with a PMI Type are ALWAYS the one you should keep UNLESS you know for a fact the record from PMI is wrong.

                            <p></p>
                            <b>Note:</b> Keep in mind that the first name, last name and PMI ID number must match PMI's records for PDU reconciliation.

                            @include('v1.parts.end_content')

                            @include('v1.parts.start_content',
                            ['header' => 'Email Addresses', 'subheader' => '',
                            'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                            The following email address are associated with either account.
                            They will all be associated with <b>"The Keeper"</b> post-merge.
                            <p></p>

                            @if($model1 !== null)
                            PersonID {{ $model1->personID }}'s Emails:<br />
                            @foreach($model1->emails as $e)
                                {{ $e->emailADDR }},
                            @endforeach
                            <p></p>
                            @endif

                            @if($model2 !== null)
                            PersonID {{ $model2->personID }}'s Emails:<br />
                            @foreach($model2->emails as $e)
                                {{ $e->emailADDR }},
                            @endforeach
                            @endif

                            @include('v1.parts.end_content')
                        </div>
                    @else
                        <div class="col-sm-6">
                            @if($collection && !isset($model2))
                                {!! Form::open(array('url' => env('APP_URL')."/merge/". $letter, 'method' => 'post')) !!}
                                <div id="custom-template" class="form-group col-sm-12">
                                    {!! Form::label('model2', 'Type the name, email, or PMI ID associated with any duplicate record.') !!}
                                    <br/>
                                    {!! Form::hidden('model1', $model1->personID, array('id' => 'model1')) !!}
                                    {!! Form::text('model2', null, array('id' => 'model2', 'class' => 'form-control typeahead input-sm')) !!}
                                    {!! Form::submit('Retrieve Record', array('class' => 'btn btn-sm btn-primary')) !!}
                                    <div id="search-results"></div>
                                </div>
                                {!! Form::close() !!}
{{--
// Quick form to force view of return from autocomplete
                                {!! Form::open(array('url' => env('APP_URL')."/autocomplete/?" . $string, 'method' => 'get')) !!}
                                {!! Form::text('query', null, array('id' => 'query', 'class' => 'form-control input-sm')) !!}
                                {!! Form::submit('Force Query', array('class' => 'btn btn-sm btn-danger')) !!}
                                {!! Form::close() !!}
--}}

                            @endif
                        </div>
                    @endif
                @endif

                @include('v1.parts.end_content')

@endsection

@section('scripts')
{{--
    <script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
--}}
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

                            $SIDEBAR_MENU.find('a[href="{{ env('APP_URL') }}/merge/p"]').parent('li').addClass('current-page').parents('ul').slideDown(function () {
                                setContentHeight();
                            }).parent().addClass('active');

                        });
                    </script>
@endsection