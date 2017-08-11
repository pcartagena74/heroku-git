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
?>

@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @include('v1.parts.start_content', ['header' => 'Event Defaults for ' . $org->orgName, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <table id="demographics" class="table table-striped table-condensed">
        <tr>
            <th style="text-align: left; width: 33%;"><a data-toggle="tooltip" data-title="This is the label that will be given to the default ticket
                             that is automatically created when events are created.">Default
                    Ticket Label</a></th>
            <th style="text-align: left; width: 33%;">Time Zone</th>
            <th style="text-align: left; width: 33%;">Event Category</th>
        </tr>
        <tr>
            <td style="text-align: left;"><a href="#" id="defaultTicketLabel" data-value="{{ $org->defaultTicketLabel }}"></a>
                <p>&nbsp;</p></td>
            <td style="text-align: left;"><a href="#" id="orgZone" data-value="{{ $org->orgZone }}"></a></td>
            <td style="text-align: left;"><a href="#" id="orgCategory" data-value="{{ $org->orgCategory }}"></a></td>
        </tr>
        <tr>
            <th style="text-align: left;">Early Bird Percent</th>
            <th style="text-align: left;">Event Contact Email</th>
            <th style="text-align: left;"><a data-toggle="tooltip" data-title="We suggest putting your chapter first, followed by
            other chapters, and ending with 'Other' and 'None of the above'">Nearby Chapters</a></th>
        </tr>
        <tr>
            <td style="text-align: left;"><a href="#" id="earlyBirdPercent" data-value="{{ $org->earlyBirdPercent }}"></a> &nbsp;
                <i class="fa fa-percent"></i></td>
            <td style="text-align: left;"><a href="#" id="eventEmail" data-value="{{ $org->eventEmail }}"></a></td>
            <td style="text-align: left;"><a href="#" id="nearbyChapters" data-value="{{ $org->nearbyChapters }}"></a>
            </td>
        </tr>
    </table>

    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Organizational Discount Codes' , 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    The <b style="color: red;">non-empty discount codes</b> here will be applied to all new events as they are created.<br>
    They can be deleted from events or adjusted, individually, as needed. <br>&nbsp;<br>

    <?php
    // @include('v1.parts.datatable', ['headers'=>$discount_headers, 'data'=>$discount_codes, 'scroll'=>0])
    ?>
    <table class="table table-bordered table-striped table-condensed">
        <thead>
        <tr>
            <th style="text-align: left;">#</th>
            <th style="text-align: left;">Discount Code</th>
            <th style="text-align: left;">Discount Percent</th>
        </tr>
        </thead>
        <tbody>
        @foreach($discount_codes as $dCode)
            <tr>
                <td style="text-align: left;">{{ $dCode->discountID }}</td>
                <td style="text-align: left;">
                    <a data-pk="{{ $dCode->discountID }}" id="discountCODE{{ $dCode->discountID }}"
                       data-value="{{ $dCode->discountCODE }}" data-url="{{ env('APP_URL') }}/orgdiscounts/{{ $dCode->discountID }}"
                       data-type="text" data-placement="top"></a>
                </td>
                <td style="text-align: left;">
                    <a data-pk="{{ $dCode->discountID }}" id="percent{{ $dCode->discountID }}"
                       data-value="{{ $dCode->percent }}" data-url="{{ env('APP_URL') }}/orgdiscounts/{{ $dCode->discountID }}"
                       data-type="text" data-placement="top"></a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
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
            $('#defaultTicketLabel').editable({
                type: 'text',
                pk:  {{ $org->orgID }},
                placement: 'right',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }}
            });

            $('#orgZone').editable({
                type: 'select',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                pk:  {{ $org->orgID }},
                source: [
                    @foreach($tz as $zone)
                    {!! "{ value: '" . $zone->zoneOffset . "', text: '" . $zone->zoneName . "' }," !!}
                    @endforeach
                ]
            });
            $('#orgCategory').editable({
                type: 'select',
                pk:  {{ $org->orgID }},
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                source: [
                    @foreach($cats as $category)
                    {!! "{ value: '" . $category->catID . "', text: '" . $category->catTXT . "' }," !!}
                    @endforeach
                ]
            });

            $('#earlyBirdPercent').editable({
                type: 'text',
                pk:  {{ $org->orgID }},
                placement: 'right',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }}
            });

            $('#eventEmail').editable({
                type: 'text',
                pk:  {{ $org->orgID }},
                placement: 'right',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }}
            });

            $("#nearbyChapters").editable({
                pk:  {{ $org->orgID }},
                placement: 'top',
                url: '{{ env('APP_URL') }}/orgsettings/' + {{ $org->orgID }},
                select2: {
                    tags: ["None of the above"],
                    multiple: true
                },
                maximumInputLength: 5
            });
        });
    </script>

@endsection
