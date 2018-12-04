<?php
/**
 * Comment:
 * Created: 2/9/2017
 */

$topBits = '';  // remove this if this was set in the controller
$string = 'l='.$letter.'&';
if($model1) {
    $columns = Schema::getColumnListing($model1->getTable());
}

switch($letter){
    case 'p':
        if($model1){
            $string .= 'm='.$model1->personID.'&';
            $id1 = $model1->personID;
        }
        if($model2){
            $id2 = $model2->personID;
        }
        break;
    case 'l':
        if($model1){
            $string .= 'm='.$model1->locID.'&';
            $id1 = $model1->locID;
        }
        if($model2){
            $id2 = $model2->locID;
        }
        break;
}
$ignore_array = array('firstName', 'lastName', 'login', 'defaultOrgID', 'personID', 'locID', 'orgID');
$suppress_array = array('creatorID', 'createDate', 'updaterID', 'updateDate', 'defaultOrgID', 'lastLoginDate', 'deleted_at', 'locNote', 'isVirtual', 'isDeleted');
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
@include('v1.parts.typeahead')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.rec_merge'), 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

    @if($collection && !isset($model1))
        {!! Form::open(array('url' => env('APP_URL')."/merge/". $letter, 'method' => 'post')) !!}
        <div id="custom-template" class="form-group col-sm-12">
            {!! Form::label('model1', trans_choice('messages.instructions.merge_survive', $letter)) !!}<br/>
            {!! Form::text('model1', null, array('id' => 'model', 'class' => 'form-control typeahead input-sm')) !!}
            {!! Form::submit(trans('messages.headers.rec_retrieve'), array('class' => 'btn btn-sm btn-primary')) !!}
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
                    {!! Form::hidden('model1', $id1, array('id' => 'model1')) !!}
                    {!! Form::hidden('model2', $id2, array('id' => 'model2')) !!}
                    {!! Form::hidden('letter', $letter, array('id' => 'letter')) !!}
                    {!! Form::hidden('ignore_array', implode(",", $ignore_array), array('id' => 'ignore_array')) !!}
                    {!! Form::hidden('columns', implode(",", $columns), array('id' => 'columns')) !!}
                    @lang('messages.instructions.merge_overwrite')
                @endif
                        <table class="table table-condensed table-striped table-responsive jambo_table">
                            <thead>
                            <tr valign="top">
                                <th style="text-align: left;">{{ trans_choice('messages.headers.data', 2) }}</th>
                                <th style="text-align: left;">
                                    @lang('messages.headers.keep')
                                    @if($model1 !== null && $model2 !== null)
                                    <a href="{{ env('APP_URL') }}/merge/{{ $letter }}/{{ $id2 }}/{{ $id1 }}"
                                       class="btn btn-xs btn-success pull-right">
                                        <i data-toggle="tooltip" title="{{ trans('messages.headers.swap') }}" class="fas fa-sync-alt"></i>
                                    </a>
                                    @endif
                                </th>
                                @if($model2 !== null)
                                    <th style="text-align: left;">@lang('messages.headers.merge_can')</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @switch($letter)
                                @case('p')
                                <tr>
                                    <td style="text-align: right;">
                                        @lang('messages.fields.pmi_id'):<br />
                                        @lang('messages.fields.pmi_type'):
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
                                @break

                                @case('l')
                                <tr>
                                    <td style="text-align: right;">
                                        @lang('messages.fields.loc_id'):
                                    </td>
                                    <td style="text-align: left;">
                                        {{ $model1->locID }}
                                    </td>
                                    @if($model2 !== null)
                                        <td style="text-align: left;">
                                            {{ $model2->locID }}
                                        </td>
                                    @endif
                                </tr>
                                @break

                            @endswitch

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
                {!! Form::submit(trans('messages.headers.rec_merge'), array('class' => 'btn btn-sm btn-primary')) !!}
                @endif
            </div>
                    @if(isset($model2))
                        <div class="col-sm-3">
                            @include('v1.parts.start_content',
                            ['header' => trans('messages.headers.merge_notes'), 'subheader' => '',
                            'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                            {!! trans_choice('messages.instructions.merge_notes', $letter) !!}

                            @include('v1.parts.end_content')


                            @switch($letter)
                                @case('p')
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
                                @break

                            @endswitch

                        </div>
                    @else
                        <div class="col-sm-6">
                            @if($collection && !isset($model2))
                                {!! Form::open(array('url' => env('APP_URL')."/merge/". $letter, 'method' => 'post')) !!}
                                <div id="custom-template" class="form-group col-sm-12">
                                    {!! Form::label('model2', trans_choice('messages.instructions.merge_dupe', $letter)) !!}
                                    <br/>
                                    {!! Form::hidden('model1', $id1, array('id' => 'model1')) !!}
                                    {!! Form::text('model2', null, array('id' => 'model2', 'class' => 'form-control typeahead input-sm')) !!}
                                    {!! Form::submit(trans('messages.headers.rec_retrieve'), array('class' => 'btn btn-sm btn-primary')) !!}
                                    <div id="search-results"></div>
                                </div>
                                {!! Form::close() !!}

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

    @switch($letter)
        @case('p')
            @include('v1.parts.menu-fix', array('path' => '/merge/p'))
            @break
        @case('l')
            @include('v1.parts.menu-fix', array('path' => '/locations'))
            @break
    @endswitch

@endsection