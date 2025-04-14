@php
    /**
     * Comment: Shows the Event Defaults for an Organization
     * Created: 3/11/2017
     *
     * @var $current_person
     * @var $org
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

    $currentPerson = App\Models\Person::find(auth()->user()->id);
    $currentOrg = $currentPerson->defaultOrg;
@endphp

@extends('v1.layouts.auth', ['topBits' => $topBits])


@section('content')
    @if((Entrust::can('settings-management') && Entrust::can('event-management'))
         || Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))

        @include('v1.parts.start_content', ['header' => trans('messages.headers.defs') . $org->orgName, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
        <table class="table table-striped table-condensed" id="demographics">
            <tr>
                <th style="text-align: left; width: 33%;">
                    @lang('messages.headers.def_label')
                    @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.def_label')])
                </th>
                <th style="text-align: left; width: 33%;">
                    @lang('messages.headers.tz')
                </th>
                <th style="text-align: left; width: 33%;">
                    @lang('messages.headers.disc_chap')
                    @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.disc_chap')])
                </th>
            </tr>
            <tr>
                <td style="text-align: left;">
                    <a data-value="{{ $org->defaultTicketLabel }}" href="#" id="defaultTicketLabel">
                    </a>
                </td>
                <td style="text-align: left;">
                    <a data-value="{{ $org->orgZone }}" href="#" id="orgZone">
                    </a>
                </td>
                <td style="text-align: left;">
                    <a data-value="{{ $org->discountChapters }}" href="#" id="discountChapters">
                    </a>
                </td>
            </tr>
            <tr>
                <th style="text-align: left;">
                    @lang('messages.headers.early')
                    @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.eb_percent')])
                </th>
                <th style="text-align: left;">
                    @lang('messages.headers.contact_email')
                </th>
                <th style="text-align: left;">
                    @lang('messages.headers.near_chap')
                    @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.near_chap')])
                </th>
            </tr>
            <tr>
                <td style="text-align: left;">
                    <a data-value="{{ $org->earlyBirdPercent }}" href="#" id="earlyBirdPercent">
                    </a>
                    <i class="fa fa-percent">
                    </i>
                </td>
                <td style="text-align: left;">
                    <a data-value="{{ $org->eventEmail }}" href="#" id="eventEmail">
                    </a>
                </td>
                <td style="text-align: left;">
                    <a data-value="{{ $org->nearbyChapters }}" href="#" id="nearbyChapters">
                    </a>
                </td>
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
                    <a data-value="{{ $org->refundDays }}" href="#" id="refundDays">
                    </a>
                    @lang('messages.headers.days')
                </td>
                <td style="text-align: left;">
                    <a data-value="{{ $org->orgCategory }}" href="#" id="orgCategory">
                    </a>
                </td>
                <td style="text-align: left;">
                    <a data-value="{{ $org->regionChapters }}" href="#" id="regionChapters">
                    </a>
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
                    <a data-placement="right" data-value="{{ $org->anonCats }}" href="#" id="anonCats">
                    </a>
                </td>
                <td style="text-align: left;">
                    <a data-placement="right" data-value="{{ $org->noSwitchTEXT }}" href="#" id="noSwitchTEXT">
                    </a>
                </td>
                <td style="text-align: left;">
                    <a data-value="{{ $org->postEventEditDays }}" href="#" id="postEventEditDays">
                    </a>
                </td>
            </tr>
        </table>
        @include('v1.parts.end_content')

        @include('v1.parts.start_content', ['header' => 'Organizational Discount Codes' , 'subheader' => '',
                 'w1' => '6', 'w2' => '6', 'r1' => 0, 'r2' => 0, 'r3' => 0])
        @lang('messages.instructions.org_disc')
        <br/>
        <br/>
        @php
            // @include('v1.parts.datatable', ['headers'=>
        // $discount_headers, 'data'=>$discount_codes, 'scroll'=>0])
        @endphp
        <table class="table table-bordered table-striped table-condensed">
            <thead>
            <tr>
                <th style="text-align: left;">
                    #
                </th>
                <th style="text-align: left;">
                    @lang('messages.headers.disc_code')
                </th>
                <th style="text-align: left;">
                    @lang('messages.headers.disc_percent')
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($discount_codes as $dCode)
                <tr>
                    <td style="text-align: left;">
                        {{ $dCode->discountID }}
                    </td>
                    <td style="text-align: left;">
                        <a data-pk="{{ $dCode->discountID }}" data-placement="top" data-type="text"
                           data-url="{{ env('APP_URL') }}/orgdiscounts/{{ $dCode->discountID }}"
                           data-value="{{ $dCode->discountCODE }}" id="discountCODE{{ $dCode->discountID }}">
                        </a>
                    </td>
                    <td style="text-align: left;">
                        <a data-pk="{{ $dCode->discountID }}" data-placement="top" data-type="text"
                           data-url="{{ env('APP_URL') }}/orgdiscounts/{{ $dCode->discountID }}"
                           data-value="{{ $dCode->percent }}" id="percent{{ $dCode->discountID }}">
                        </a>
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
                <th colspan="3" style="text-align: left;">
                    {{ trans_choice('messages.headers.et', 2) }}
                    @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.etID')])
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach($event_types as $et)
                <tr>
                    @if($et->orgID != 1)
                        <td style="text-align: left; width: 15px;">
                            {{ html()->form('DELETE', env('APP_URL') . "/eventtype/" . $et->etID . "/delete")->open() }}
                            <input name="personID" type="hidden" value="{{ $current_person->personID }}">
                            <button class="btn btn-danger btn-sm" data-btn-cancel-class="btn-danger btn-sm"
                                    data-btn-cancel-icon="glyphicon glyphicon-ban-circle" data-btn-cancel-label="Stop!"
                                    data-btn-ok-class="btn-success btn-sm"
                                    data-btn-ok-icon="glyphicon glyphicon-share-alt" data-btn-ok-label="Continue"
                                    data-content="This cannot be undone." data-title="Are you sure?">
                                <i class="far fa-trash-alt fa-fw">
                                </i>
                            </button>
                            </input>
                            {{ html()->form()->close() }}
                        </td>
                        <td style="text-align: left;">
                            <b>
                                {{ $et->etID }}
                            </b>
                        </td>
                        <td style="text-align: left;">
                            <a data-pk="{{ $et->etID }}" data-placement="top" data-type="text"
                               data-url="{{ env('APP_URL') }}/eventtype/{{ $et->etID }}" data-value="{{ $et->etName }}"
                               id="etName-{{ $et->etID }}">
                            </a>
                        </td>
                    @else
                        <td style="text-align: left;">
                        </td>
                        <td style="text-align: left;">
                            <b>
                                {{ $et->etID }}
                            </b>
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
        <button class="btn btn-success btn-sm" data-target="#et_modal" data-toggle="modal">
            {{ trans_choice('messages.headers.add', 1) }}
        </button>
        @include('v1.parts.end_content')

    @else
        @lang("messages.messages.admin_req")
    @endif
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
                    @php
                        foreach ($event_types as $x) {
                            $string .= "{ value: '" . $x->etID . "' , text: '" . $x->etName . "' },";
                        }
                    @endphp
                            {!!  rtrim($string, ",") !!}  @php $string = ''; @endphp
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
    <div aria-hidden="true" aria-labelledby="et_label" class="modal fade" id="et_modal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="et_label">
                        {{ trans_choice('messages.headers.add', 2) }}
                    </h5>
                    <button aria-label="Close" class="close" data-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        ×
                    </span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ env('APP_URL') }}/eventtype/create" method="post" name="eventtypes">
                        {{ csrf_field() }}
                        <input name="personID" type="hidden" value="{{ $current_person->personID }}">
                        <table class="table table-striped" id="new_et_fields">
                            <thead>
                            <tr>
                                <th style="width: 10%">
                                    Event Type
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @for($n=1; $n<=5; $n++)
                                <tr id="et_{{ $n }}_row"@php
                                    if ($n > 1) echo(' style="display:none"');
                                @endphp>
                                    <td><input name='eventType-{{ $n }}' type='text' placeholder='Event Type'
                                               class='form-control input-sm'>
                                    </td>
                                </tr>
                            @endfor
                            </tbody>
                        </table>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <button class="btn btn-sm btn-warning" id="add_row" type="button">
                                @lang('messages.buttons.another')
                            </button>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12" style="text-align: right">
                            <button class="btn btn-sm btn-danger" id="delete_row" style="display: none" type="button">
                                @lang('messages.buttons.delete')
                            </button>
                        </div>
                        </input>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" data-dismiss="modal" type="button">
                        @lang('messages.buttons.close')
                    </button>
                    <button class="btn btn-sm btn-success" id="et_submit" type="submit">
                        {{ trans_choice('messages.buttons.save_et', 1) }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection