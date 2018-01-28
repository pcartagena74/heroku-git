<?php
/**
 * Comment:
 * Created: 2/9/2017
 */


$headers = ['#', 'Name', 'PMI ID', 'PMI Classification', 'Company', 'Title', 'Industry', 'Expiration', 'Buttons'];

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
    <div class="col-md-12 col-sm-12 col-xs-12">
        <ul id="myTab" class="nav nav-tabs bar_tabs nav-justified" role="tablist">
            <li class="active"><a href="#tab_content1" id="member_tab" data-toggle="tab"
                                  aria-expanded="true"><b>Report: Members Only</b></a></li>
            <li class=""><a href="#tab_content2" id="everyone_tab" data-toggle="tab"
                            aria-expanded="false"><b>Report: All People</b></a></li>
{{--
            @if(Entrust::hasRole('Speaker'))
                <li class=""><a href="#tab_content3" id="other-tab" data-toggle="tab"
                                aria-expanded="false"><b>Third Thing</b></a></li>
            @endif
--}}
        </ul>

        <div id="tab-content" class="tab-content">
            <div class="tab-pane active" id="tab_content1" aria-labelledby="member_tab">
                &nbsp;<br/>
                @include('v1.parts.start_content', ['header' => 'Number of Events Attended by Year', 'subheader' => '',
                         'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

                <div id="canvas"></div>

                @include('v1.parts.end_content')

                @include('v1.parts.start_content', ['header' => 'Industry Breakdown', 'subheader' => '',
                         'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])A
                Pie Chart of Industries
                @include('v1.parts.end_content')


            </div>
            <div class="tab-pane fade" id="tab_content2" aria-labelledby="everyone_tab">
                <br />
            </div>
        </div>
    </div>

@endsection

{{--
{!! $datastring  or '' !!}
--}}

@section('scripts')
    @include('v1.parts.footer-chart')
    <script>
        $(document).ready(function () {
            Morris.Bar({
                element: 'canvas',
                data: [
                    { Events: '1 Event', a: 518, b: 533},
                    { Events: '2 Events', a: 153, b: 200},
                    { Events: '3 Events', a: 57, b: 77},
                    { Events: '4 Events', a: 23, b: 25},
                    { Events: '5 Events', a: 16, b: 16},
                    { Events: '6 Events', a: 10, b: 2},
                    { Events: '7 Events', a: 5, b: 1},
                    { Events: '8+ Events', a: 15, b: 20},
        ],
                xkey: 'Events',
                ykeys: ['a', 'b'],
                labels: ['2017', '2016'],
                barRatio: 0.1,
            {{--
                barColors: function (row, series, type) {
                    if (1) {
                        return "#26B99A";
                    } else {
                        return "#f00";
                    }
                },
            --}}
                xLabelAngle: 35,
                hideHover: 'auto',
                resize: true
            });
        });
    </script>
{{--
    @include('v1.parts.footer-datatable')
    <script>
        $(document).ready(function() {
            $('#member_table').DataTable({
                "fixedHeader": true,
                "order": [[ 0, "asc" ]]
            });
        });
    </script>
--}}
@endsection