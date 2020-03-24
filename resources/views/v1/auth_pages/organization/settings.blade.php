<?php
/**
 * Comment: Shows Custom Field Labels and Demographic Information for an Organization
 * Created: 3/11/2017
 */

$orgHeader = "<a href='#' id='orgName' data-title='" . trans('messages.directions.org.display') . "' data-value='$org->orgName'></a>" .
    " &nbsp; <a data-toggle='tooltip' title=" . '"' . trans('messages.tooltips.orgName') .
    '"'. " data-placement='top'>" . '<i class="fa fa-info-circle purple"></i></a>';

$fieldnames =
    " &nbsp; <a data-toggle='tooltip' title=" . '"' . trans('messages.tooltips.not_new') .
    '"' . " data-placement='top'>" . '<i class="fa fa-info-circle purple"></i></a>';

$currentPerson = App\Person::find(auth()->user()->id);
$currentOrg    = $currentPerson->defaultOrg;
?>

@extends('v1.layouts.auth', ['topBits' => $topBits])

@if((Entrust::can('settings-management'))
    || Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
@section('content')

    @include('v1.parts.start_content', ['header' => trans('messages.nav.org_set') . " " . trans('messages.headers.for')
             . ': ' . $orgHeader, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <div class="col-xs-12">
        <h4>
            @lang('messages.headers.formal'): <a href='#' id='formalName' data-title='{{ trans('messages.directions.org.fullname') }}' data-value='{{ $org->formalName }}'></a>
        </h4>
    </div>

    @include('v1.parts.start_content', ['header' => trans('messages.headers.demos'), 'subheader' => '',
             'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <table id="demographics" class="table table-striped table-condensed">
        <tr>
            <th colspan="2" style="text-align: left;">@lang('messages.fields.addr')</th>
            <th style="text-align: left;">@lang('messages.fields.city')</th>
            <th style="text-align: left;">@lang('messages.fields.state')</th>
            <th style="text-align: left;">@lang('messages.fields.zip')</th>
        </tr>
        <tr>
            <td colspan="2" style="text-align: left;">
                <a href="#" id="orgAddr1" data-value="{{ $org->orgAddr1 }}"></a><br/>
                <a href="#" id="orgAddr2" data-value="{{ $org->orgAddr2 }}"></a>
            </td>
            <td style="text-align: left;"><a href="#" id="orgCity" data-value="{{ $org->orgCity }}"></a></td>
            <td style="text-align: left;"><a href="#" id="orgState" data-value="{{ $org->orgState }}"></a></td>
            <td style="text-align: left;"><a href="#" id="orgZip" data-value="{{ $org->orgZip }}"></a></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">@lang('messages.headers.main') @lang('messages.headers.email')</th>
            <th style="text-align: left;">@lang('messages.headers.main') @lang('messages.headers.number')</th>
            <th colspan="2" style="text-align: left;">@lang('messages.headers.fax')</th>
        </tr>
        <tr>
            <td colspan="2" style="text-align: left;"><a href="#" id="orgEmail" data-value="{{ $org->orgEmail }}"></a>
            </td>
            <td style="text-align: left;"><a href="#" id="orgPhone" data-value="{{ $org->orgPhone }}"></a></td>
            <td colspan="2" style="text-align: left;"><a href="#" id="orgFax" data-value="{{ $org->orgFax }}"></a></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">@lang('messages.headers.admin') @lang('messages.headers.email')</th>
            <th colspan="1" style="text-align: left;"></th>
            <th colspan="2" style="text-align: left;"><a data-toggle="tooltip" data-placement="top"
                                                         title="{{ trans('messages.tooltips.fb') }}">@lang('messages.headers.facebook') @lang('messages.headers.url')</a></th>
        </tr>
        <tr>
            <td colspan="2" style="text-align: left;"><a href="#" id="adminEmail" data-value="{{ $org->adminEmail }}"></a></td>
            <td colspan="1" style="text-align: left;"></td>
            <td colspan="2" style="text-align: left;"><a href="#" id="facebookURL" data-value="{{ $org->facebookURL }}"></a></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">@lang('messages.headers.website')</th>
            <th colspan="1" style="text-align: left;">@lang('messages.headers.credit')</th>
            <th colspan="2" style="text-align: left;">@lang('messages.headers.twitter')</th>
        </tr>
        <tr>
            <td colspan="2" style="text-align: left;"><a href="#" id="orgURL" data-value="{{ $org->orgURL }}"></a></td>
            <td colspan="1" style="text-align: left;"><a href="#" id="creditLabel" data-value="{{ $org->creditLabel }}"></a></td>
            <td colspan="2" style="text-align: left;"><a href="#" id="orgHandle" data-value="{{ $org->orgHandle }}"></a>
            </td>
        </tr>
        <tr>
            <th colspan="3" style="text-align: left;">@lang('messages.headers.linkedin')</th>
            <th colspan="2" style="text-align: left;">
                {{-- lang('messages.headers.google') --}}
            </th>
        </tr>
        <tr>
            <td colspan="3" style="text-align: left;"><a href="#" id="linkedinURL" data-value="{{ $org->linkedinURL }}"></a></td>
            <td colspan="2" style="text-align: left;">
                <?php
               // <a href="#" id="googleURL" data-value="{{ $org->googleURL }}"></a>
               ?>
            </td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">@lang('messages.headers.admin_stmt')</th>
        </tr>
        <tr>
            <td colspan="5" style="text-align: left;"><a href="#" id="adminContactStatement" data-value="{{ $org->adminContactStatement }}"></a></td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">@lang('messages.headers.tech_stmt')</th>
        </tr>
        <tr>
            <td colspan="5" style="text-align: left;"><a href="#" id="techContactStatement" data-value="{{ $org->techContactStatement }}"></a></td>
        </tr>

    </table>
    Need to add a logo upload/validation here.
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.cdata') . $fieldnames, 'subheader' => '', 'w1' => '3', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <table id="data_fields" class="table table-striped table-condensed">
        <tbody>
        @for($i=1; $i<=10; $i++)
            <tr>
                <td style="text-align: right;">{{ $i }}</td>
                <td style="text-align: left;"><a href="#" id="OSN{{ $i }}" data-value="{{ $org->{'OSN'.$i} }}"></a></td>
            </tr>
        @endfor
        </tbody>
    </table>
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.cdate') . $fieldnames, 'subheader' => '', 'w1' => '3', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <table id="date_fields" class="table table-striped table-condensed">
        <tbody>
        @for($i=1; $i<=10; $i++)
            <tr>
                <td style="text-align: right;">{{ $i }}</td>
                <td style="text-align: left;"><a href="#" id="ODN{{ $i }}" data-value="{{ $org->{'ODN'.$i} }}"></a></td>
            </tr>
        @endfor
        </tbody>
    </table>
    @include('v1.parts.end_content')

    @include('v1.parts.end_content')

@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('[data-toggle="tooltip"]').tooltip({'placement': 'top'});
            //$.fn.editable.defaults.mode = 'inline';
            $.fn.editable.defaults.params = function (params) {
                params._token = $("meta[name=token]").attr("content");
                return params;
            };
            $('#orgName').editable({
                type: 'text',
                placement: 'right',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}',
                title: "Edit Org Name.  This cannot be blank."
            });

            $('#formalName').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            $('#orgAddr1').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            $('#orgAddr2').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            $('#orgCity').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            $('#orgState').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            $('#orgZip').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            $('#orgPhone').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            $('#orgFax').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
            });
            $('#orgEmail').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            $('#orgURL').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            $('#creditLabel').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            $('#orgHandle').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            $('#adminEmail').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            $('#facebookURL').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            $('#adminContactStatement').editable({
                type: 'textarea',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            $('#techContactStatement').editable({
                type: 'textarea',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            $('#linkedinURL').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            $('#googleURL').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });


            @for($i=1; $i<=10; $i++)
                $('#OSN{{ $i }}').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            @endfor

            @for($i=1; $i<=10; $i++)
                $('#ODN{{ $i }}').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/{{ $org->orgID }}',
                pk:  '{{ $org->orgID }}'
            });
            @endfor
        });
    </script>
    @include('v1.parts.menu-fix', array('path' => '/orgsettings'))

@endsection
@endif
