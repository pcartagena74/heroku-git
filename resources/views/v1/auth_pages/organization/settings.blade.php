<?php
/**
 * Comment: Shows Custom Field Labels and Demographic Information for an Organization
 * Created: 3/11/2017
 */

$title = '"You can change the Organization\'s name. Click it."';
$orgHeader = "<a href='#' id='orgName' data-title='Enter Org Name' data-value='$org->orgName'></a>" .
    " &nbsp; <a data-toggle='tooltip' title=" . $title .
    " data-placement='top'>" . '<i class="fa fa-info-circle purple"></i></a>';
$title = '"Adding a new field label here does NOT introduce new data for your members.  Work with mCentric on incorporating new workflow."';
$fieldnames =
    " &nbsp; <a data-toggle='tooltip' title=" . $title .
    " data-placement='top'>" . '<i class="fa fa-info-circle purple"></i></a>';

$currentPerson = App\Person::find(auth()->user()->id);
$currentOrg    = $currentPerson->defaultOrg;
?>

@extends('v1.layouts.auth', ['topBits' => $topBits])

@if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('settings-management'))
    || Entrust::hasRole('Development'))
@section('content')

    @include('v1.parts.start_content', ['header' => 'Organizational Settings for: ' . $orgHeader, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @include('v1.parts.start_content', ['header' => 'Demographics & Contact Information', 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <table id="demographics" class="table table-striped table-condensed">
        <tr>
            <th colspan="2" style="text-align: left;">Address</th>
            <th style="text-align: left;">City</th>
            <th style="text-align: left;">State</th>
            <th style="text-align: left;">Zip</th>
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
            <th colspan="2" style="text-align: left;">Main Email</th>
            <th style="text-align: left;">Main Number</th>
            <th colspan="2" style="text-align: left;">Fax</th>
        </tr>
        <tr>
            <td colspan="2" style="text-align: left;"><a href="#" id="orgEmail" data-value="{{ $org->orgEmail }}"></a>
            </td>
            <td style="text-align: left;"><a href="#" id="orgPhone" data-value="{{ $org->orgPhone }}"></a></td>
            <td colspan="2" style="text-align: left;"><a href="#" id="orgFax" data-value="{{ $org->orgFax }}"></a></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Admin Email</th>
            <th colspan="1" style="text-align: left;"></th>
            <th colspan="2" style="text-align: left;"><a data-toggle="tooltip" data-placement="top"
                                                         title="The bit after fb.com/">Facebook URL</a></th>
        </tr>
        <tr>
            <td colspan="2" style="text-align: left;"><a href="#" id="adminEmail"
                                                         data-value="{{ $org->adminEmail }}"></a></td>
            <td colspan="1" style="text-align: left;"></td>
            <td colspan="2" style="text-align: left;"><a href="#" id="facebookURL"
                                                         data-value="{{ $org->facebookURL }}"></a></td>
        </tr>
        <tr>
            <th colspan="2" style="text-align: left;">Website URL</th>
            <th colspan="1" style="text-align: left;">Credit Label</th>
            <th colspan="2" style="text-align: left;">Twitter Handle</th>
        </tr>
        <tr>
            <td colspan="2" style="text-align: left;"><a href="#" id="orgURL" data-value="{{ $org->orgURL }}"></a></td>
            <td colspan="1" style="text-align: left;"><a href="#" id="creditLabel"
                                                         data-value="{{ $org->creditLabel }}"></a></td>
            <td colspan="2" style="text-align: left;"><a href="#" id="orgHandle" data-value="{{ $org->orgHandle }}"></a>
            </td>
        </tr>
        <tr>
            <th colspan="3" style="text-align: left;">LinkedIn URL</th>
            <th colspan="2" style="text-align: left;">Google URL</th>
        </tr>
        <tr>
            <td colspan="3" style="text-align: left;"><a href="#" id="linkedinURL"
                                                         data-value="{{ $org->linkedinURL }}"></a></td>
            <td colspan="2" style="text-align: left;"><a href="#" id="googleURL" data-value="{{ $org->googleURL }}"></a>
            </td>
        </tr>
        <tr>
            <th colspan="5" style="text-align: left;">Admin Contact Statement</th>
        </tr>
        <tr>
            <td colspan="5" style="text-align: left;"><a href="#" id="adminStatement"
                                                         data-value="{{ $org->adminContactStatement }}"></a></td>
        </tr>

    </table>
    Need to add a logo upload/validation here.
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Custom Data Field Names' . $fieldnames, 'subheader' => '', 'w1' => '3', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
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

    @include('v1.parts.start_content', ['header' => 'Custom Date Field Names' . $fieldnames, 'subheader' => '', 'w1' => '3', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
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
                pk:  {{ $org->orgID }},
                placement: 'right',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                title: "Edit Org Name.  This cannot be blank."
            });

            $('#orgAddr1').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
            });
            $('#orgAddr2').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
            });
            $('#orgCity').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
            });
            $('#orgState').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
            });
            $('#orgZip').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
            });
            $('#orgPhone').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
            });
            $('#orgFax').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
            });
            $('#orgEmail').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
            });
            $('#orgURL').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
            });
            $('#creditLabel').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
            });
            $('#orgHandle').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
            });
            $('#adminEmail').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
            });
            $('#facebookURL').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
            });
            $('#adminStatement').editable({
                type: 'textarea',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
            });
            $('#linkedinURL').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
            });
            $('#googleURL').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
            });


            @for($i=1; $i<=10; $i++)
                $('#OSN{{ $i }}').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk: {{ $org->orgID }}
            });
            @endfor

            @for($i=1; $i<=10; $i++)
                $('#ODN{{ $i }}').editable({
                type: 'text',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk: {{ $org->orgID }}
            });
            @endfor
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
            $SIDEBAR_MENU.find('a[href="{!! env('APP_URL') !!}/orgsettings"]').parent('li').addClass('current-page').parents('ul').slideDown(function () {
                setContentHeight();
            }).parent().addClass('active');

        });
    </script>

@endsection
@endif
