<?php
/**
 * Comment: Shows the Event Defaults for an Organization
 * Created: 3/11/2017
 */

$cats = DB::table('event-category')
    ->select('catID', 'catTXT')
    ->where([
        ['isActive', 1],
        ['orgID', $current_person->defaultOrgID]
    ])->get();

$tz = DB::table('timezone')->select('zoneName', 'zoneOffset')->get();

$tagArray = explode(',', $org->nearbyChapters);

$discount_headers = ['#', 'Discount Code', 'Discount Percent'];

$topBits = '';
$string = '';

$currentPerson = App\Person::find(auth()->user()->id);
$currentOrg = $currentPerson->defaultOrg;
?>

@extends('v1.layouts.auth', ['topBits' => $topBits])

@if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('settings-management'))
    || Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))

@section('content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.defs') . $org->orgName, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <table id="demographics" class="table table-striped table-condensed">
        <tr>
            <th style="text-align: left; width: 33%;">
                @lang('messages.headers.def_label')
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.def_label')])
            </th>
            <th style="text-align: left; width: 33%;">@lang('messages.headers.tz')</th>
            <th style="text-align: left; width: 33%;">@lang('messages.headers.disc_chap')
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.disc_chap')])
            </th>
        </tr>
        <tr>
            <td style="text-align: left;"><a href="#" id="defaultTicketLabel" data-value="{{ $org->defaultTicketLabel }}"></a></td>
            <td style="text-align: left;"><a href="#" id="orgZone" data-value="{{ $org->orgZone }}"></a></td>
            <td style="text-align: left;"><a href="#" id="discountChapters" data-value="{{ $org->discountChapters }}"></a></td>
        </tr>
        <tr>
            <th style="text-align: left;">
                @lang('messages.headers.early')
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.eb_percent')])
            </th>
            <th style="text-align: left;">@lang('messages.headers.contact_email')</th>
            <th style="text-align: left;">
                @lang('messages.headers.near_chap')
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.near_chap')])
            </th>
        </tr>
        <tr>
            <td style="text-align: left;">
                <a href="#" id="earlyBirdPercent" data-value="{{ $org->earlyBirdPercent }}"></a> &nbsp;
                <i class="fa fa-percent"></i>
            </td>
            <td style="text-align: left;"><a href="#" id="eventEmail" data-value="{{ $org->eventEmail }}"></a></td>
            <td style="text-align: left;"><a href="#" id="nearbyChapters" data-value="{{ $org->nearbyChapters }}"></a></td>
        </tr>
        <tr>
            <th style="text-align: left;">
                @lang('messages.headers.ref_days')
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.ref_days')])
            </th>
            <th style="text-align: left; width: 33%;">
                @lang('messages.headers.cat')
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.event_cat')])
            </th>
            <th style="text-align: left;">
                @lang('messages.headers.reg_chap')
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.region_chap')])
            </th>
        </tr>
        <tr>
            <td style="text-align: left;">
                <a href="#" id="refundDays" data-value="{{ $org->refundDays }}"></a>
                &nbsp; @lang('messages.headers.days')
            </td>
            <td style="text-align: left;"><a href="#" id="orgCategory" data-value="{{ $org->orgCategory }}"></a></td>
            <td style="text-align: left;">
                <a href="#" id="regionChapters" data-value="{{ $org->regionChapters }}"></a>
            </td>
        </tr>
        <tr>
            <th style="text-align: left;">
                @lang('messages.headers.anoncat')
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.anoncat')])
            </th>
            <th style="text-align: left;">
                @lang('messages.headers.no_switch')
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.no_switch')])
            </th>
            <th style="text-align: left;">
                @lang('messages.fields.postEventEditDays')
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.postEventEditDays')])
            </th>
        </tr>
        <tr>
            <td style="text-align: left;">
                <a href="#" id="anonCats" data-value="{{ $org->anonCats }}" data-placement="right"></a>
            </td>
            <td style="text-align: left;">
                <a href="#" id="noSwitchTEXT" data-value="{{ $org->noSwitchTEXT }}" data-placement="right"></a>
            </td>
            <td style="text-align: left;">
                <a href="#" id="postEventEditDays" data-value="{{ $org->postEventEditDays }}"></a>
            </td>
        </tr>
    </table>

    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Organizational Discount Codes' , 'subheader' => '',
             'w1' => '6', 'w2' => '6', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    @lang('messages.instructions.org_disc')
    <br/> &nbsp; <br/>

    <?php
    // @include('v1.parts.datatable', ['headers'=>$discount_headers, 'data'=>$discount_codes, 'scroll'=>0])
    ?>
    <table class="table table-bordered table-striped table-condensed">
        <thead>
        <tr>
            <th style="text-align: left;">#</th>
            <th style="text-align: left;">@lang('messages.headers.disc_code')</th>
            <th style="text-align: left;">@lang('messages.headers.disc_percent')</th>
        </tr>
        </thead>
        <tbody>
        @foreach($discount_codes as $dCode)
            <tr>
                <td style="text-align: left;">{{ $dCode->discountID }}</td>
                <td style="text-align: left;">
                    <a data-pk="{{ $dCode->discountID }}" id="discountCODE{{ $dCode->discountID }}"
                       data-value="{{ $dCode->discountCODE }}"
                       data-url="{{ env('APP_URL') }}/orgdiscounts/{{ $dCode->discountID }}"
                       data-type="text" data-placement="top"></a>
                </td>
                <td style="text-align: left;">
                    <a data-pk="{{ $dCode->discountID }}" id="percent{{ $dCode->discountID }}"
                       data-value="{{ $dCode->percent }}"
                       data-url="{{ env('APP_URL') }}/orgdiscounts/{{ $dCode->discountID }}"
                       data-type="text" data-placement="top"></a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' =>  trans('messages.headers.def_cust_et'), 'subheader' => '',
             'w1' => '6', 'w2' => '6', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <table class="table table-bordered table-striped table-condensed">
        <thead>
        <tr>
            <th colspan="3" style="text-align: left;">{{ trans_choice('messages.headers.et', 2) }}
                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.etID')])
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach($event_types as $et)
            <tr>
                @if($et->orgID != 1)
                    <td style="text-align: left; width: 15px;">
                        {!! Form::open(array('url' => env('APP_URL')."/eventtype/" . $et->etID . "/delete", 'method' => 'delete')) !!}
                        <input type="hidden" name="personID" value="{{ $current_person->personID }}">
                        <button class="btn btn-danger btn-sm"
                                {{--
                                data-toggle="confirmation"
                                --}}
                                data-btn-ok-label="Continue"
                                data-btn-ok-icon="glyphicon glyphicon-share-alt"
                                data-btn-ok-class="btn-success btn-sm"
                                data-btn-cancel-label="Stop!"
                                data-btn-cancel-icon="glyphicon glyphicon-ban-circle"
                                data-btn-cancel-class="btn-danger btn-sm"
                                data-title="Are you sure?" data-content="This cannot be undone.">
                            <i class="far fa-trash-alt fa-fw"></i>
                        </button>
                        {{ Form::close() }}
                    </td>
                    <td style="text-align: left;"><b>{{ $et->etID }}</b></td>
                    <td style="text-align: left;">
                        <a data-pk="{{ $et->etID }}" id="etName-{{ $et->etID }}"
                           data-value="{{ $et->etName }}"
                           data-url="{{ env('APP_URL') }}/eventtype/{{ $et->etID }}"
                           data-type="text" data-placement="top"></a>
                    </td>
                @else
                    <td style="text-align: left;"></td>
                    <td style="text-align: left;">
                        <b>{{ $et->etID }}</b>
                    </td>
                    <td style="text-align: left;">
                        {{ $et->etName }}
                        @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.nope')])
                    </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
    <button class="btn btn-success btn-sm" data-toggle="modal"
            data-target="#et_modal">{{ trans_choice('messages.headers.add', 1) }}</button>
    @include('v1.parts.end_content')

@endsection

@section('scripts')
    @include('v1.parts.footer-datatable')
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                }
            });

            @foreach($discount_codes as $dCode)
            $('#discountCODE{{ $dCode->discountID }}').editable();
            $('#percent{{ $dCode->discountID }}').editable();
            @endforeach
        });
    </script>
    <script>
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip({'placement': 'top'});
            //$.fn.editable.defaults.mode = 'inline';
            $.fn.editable.defaults.params = function (params) {
                params._token = $("meta[name=token]").attr("content");
                return params;
            };
            $('[data-toggle=confirmation]').confirmation({
                rootSelector: '[data-toggle=confirmation]',
            });
            $('#defaultTicketLabel').editable({
                type: 'text',
                pk: '{{ $org->orgID }}',
                placement: 'right',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}'
            });

            $('#orgZone').editable({
                type: 'select',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk: '{{ $org->orgID }}',
                source: [
                    @foreach($tz as $zone)
                    {!! "{ value: '" . $zone->zoneOffset . "', text: '" . $zone->zoneName . "' }," !!}
                    @endforeach
                ]
            });
            $('#orgCategory').editable({
                type: 'select',
                pk: '{{ $org->orgID }}',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                source: [
                    @foreach($cats as $category)
                    {!! "{ value: '" . $category->catID . "', text: '" . $category->catTXT . "' }," !!}
                    @endforeach
                ]
            });

            $('#earlyBirdPercent').editable({
                type: 'number',
                pk: '{{ $org->orgID }}',
                placement: 'right',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}'
            });

            $('#eventEmail').editable({
                type: 'text',
                pk: '{{ $org->orgID }}',
                placement: 'right',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}'
            });

            $("#discountChapters").editable({
                pk: '{{ $org->orgID }}',
                placement: 'top',
                url: '{{ env('APP_URL') }}/orgsettings/' + '{{ $org->orgID }}',
                select2: {
                    tags: ["None of the above"],
                    multiple: true
                },
                maximumInputLength: 5
            });

            $("#nearbyChapters").editable({
                pk: '{{ $org->orgID }}',
                placement: 'top',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                select2: {
                    tags: ["None of the above"],
                    multiple: true
                },
                maximumInputLength: 5
            });

            $("#regionChapters").editable({
                pk: '{{ $org->orgID }}',
                type: 'text',
                placement: 'top',
                url: '{{ env('APP_URL') }}/orgsettings/' + '{{ $org->orgID }}',
                select2: {
                    tags: ["None of the above"],
                    multiple: true
                },
                maximumInputLength: 5
            });

            $('#refundDays').editable({
                type: 'number',
                pk: '{{ $org->orgID }}',
                placement: 'right',
                url: '{{ env('APP_URL') }}/orgsettings/' + '{{ $org->orgID }}'
            });

            $("#anonCats").editable({
                type: 'checklist',
                pk: '{{ $org->orgID }}',
                url: '{{ env('APP_URL') }}/orgsettings/' + '{{ $org->orgID }}',
                value: '{{ $org->anonCats }}',
                source: [
                    <?php
                    foreach ($event_types as $x) {
                        $string .= "{ value: '" . $x->etID . "' , text: '" . $x->etName . "' },";
                    }
                    ?>
                    {!!  rtrim($string, ",") !!}  <?php $string = ''; ?>
                ],
            });

            $('#noSwitchTEXT').editable({
                type: 'text',
                pk: '{{ $org->orgID }}',
                placement: 'right',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}'
            });

            $('#postEventEditDays').editable({
                type: 'number',
                pk: '{{ $org->orgID }}',
                placement: 'right',
                url: '{{ env('APP_URL') }}/orgsettings/' + '{{ $org->orgID }}'
            });

            @foreach($event_types as $et)
            @if($et->orgID != 1)
            $('#etName-{{ $et->etID }}').editable({
                placement: 'top'
            });
            @endif
            @endforeach
        });
    </script>
    <script>
        $(document).ready(function () {
            var i = 2;
            var x;
            $('#add_row').click(function () {
                if (i <= 5) {
                    $('#delete_row').show();
                    $('#et_submit').show();
                    x = "et_" + i + "_row";
                    $('#' + x).show();
                    i++;
                }
                if (i >= 3) {
                    $('#et_submit').text("{{ trans_choice('messages.buttons.save_et', 2) }}");
                }
                if (i == 6) {
                    $('#add_row').prop('disabled', true);
                }
            });
            $('#delete_row').click(function () {
                if (i >= 3) {
                    y = i - 1;
                    x = "et_" + y + "_row";
                    $('#' + x).hide();
                    i--;
                    $('#add_row').prop('disabled', false);
                }

                if (i <= 2) {
                    $('#et_submit').text("{{ trans_choice('messages.buttons.save_et', 1) }}");
                    $('#delete_row').hide();
                }
            });
        });
    </script>
@endsection

@section('modals')
    <div class="modal fade" id="et_modal" tabindex="-1" role="dialog" aria-labelledby="et_label"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="et_label">{{ trans_choice('messages.headers.add', 2) }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form name="eventtypes" method="post" action="{{ env('APP_URL') }}/eventtype/create">
                        {{ csrf_field() }}
                        <input type="hidden" name="personID" value="{{ $current_person->personID }}">
                        <table id="new_et_fields" class="table table-striped">
                            <thead>
                            <tr>
                                <th style="width: 10%">Event Type</th>
                            </tr>
                            </thead>
                            <tbody>
                            @for($n=1; $n<=5; $n++)
                                <tr id="et_{{ $n }}_row"<?php if ($n > 1) echo(' style="display:none"'); ?>>
                                    <td><input name='eventType-{{ $n }}' type='text' placeholder='Event Type'
                                               class='form-control input-sm'>
                                    </td>
                                </tr>
                            @endfor
                            </tbody>
                        </table>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <button type="button" id="add_row"
                                    class="btn btn-sm btn-warning">@lang('messages.buttons.another')</button>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12" style="text-align: right">
                            <button type="button" style="display: none" id="delete_row" class="btn btn-sm btn-danger">
                                @lang('messages.buttons.delete')
                            </button>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm"
                            data-dismiss="modal">@lang('messages.buttons.close')</button>
                    <button type="submit" id="et_submit"
                            class="btn btn-sm btn-success">{{ trans_choice('messages.buttons.save_et', 1) }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@endif