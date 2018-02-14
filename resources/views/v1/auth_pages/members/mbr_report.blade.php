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
                                  aria-expanded="true"><b>Member Demographics</b></a></li>
            <li class=""><a href="#tab_content2" id="everyone_tab" data-toggle="tab"
                            aria-expanded="false"><b>Heat Map [Coming Soon]</b></a></li>
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

                @if(Entrust::hasRole('Developer'))
                <b>Graph Years:</b> <a id="tags"
                                        data-pk="{{ 1 }}" data-type="select2" data-title="Select Years to Display"
                                        data-url="{{ env('APP_URL') }}/reg_verify/{{ 1 }}">2013, 2014, 2015, 2016, 2017, 2018</a><br/>
                @endif
                <div id="canvas"></div>

                @include('v1.parts.end_content')

                @include('v1.parts.start_content', ['header' => 'Identified Industry Breakdown', 'subheader' => '',
                         'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <canvas id="indPie"></canvas>
                </div>
                <div id="pieLegend" class="col-md-12 col-sm-12 col-xs-12">
                </div>
                @include('v1.parts.end_content')


            </div>
            <div class="tab-pane fade" id="tab_content2" aria-labelledby="everyone_tab">
                <br />
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.fn.editable.defaults.mode = 'popup';
        $('#tags').editable({
            placement: 'right',
            inputclass: 'input-large',
            select2: {
                tags: ['2013', '2014', '2015', '2016', '2017', '2018'],
                tokenSeparators: [",", " "]
            }
        });
    </script>
    @include('v1.parts.footer-chart')
    <script>
        $(document).ready(function () {
            Morris.Bar({
                element: 'canvas',
                data: [
                    {!! $datastring !!}
                ],
                xkey: 'Events',
                ykeys: [ {!! $labels !!} ],
                labels: [ {!! $labels !!} ],
                barRatio: 0.1,
                xLabelAngle: 35,
                hideHover: 'auto',
                resize: true
            });
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.min.js"></script>
    <script>
        var ctx = document.getElementById("indPie").getContext('2d');
        var options = {
            responsive: true,
            legend: {
                display: false,
                position: "bottom"
            },
            legendCallback: function (chart) {
                //console.log(chart.data);
                var text = [];
                text.push('<ul>');
                for (var i = 0; i < chart.data.datasets[0].data.length; i++) {
                    text.push('<li>');
                    text.push('<span style="background-color:' + chart.data.datasets[0].backgroundColor[i]
                        + '">' + chart.data.datasets[0].data[i] + '</span>');
                    if (chart.data.labels[i]) {
                        text.push(chart.data.labels[i]);
                    }
                    text.push('</li>');
                }
                text.push('</ul>');
                return text.join("");
            }
        };
        var myChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: [
                    @foreach($indPie as $i)
                        '{{ $i->indName }}',
                    @endforeach
                ],
                datasets: [{
                    backgroundColor: [
                        "#2ecc71",
                        "#3498db",
                        "#95a5a6",
                        "#9b59b6",
                        "#f1c40f",
                        "#e74c3c",
                        "#34495e",
                        "#b7ad6c",
                        "#CCFDFF",
                        "#d7ccff",
                        "#ffccf3",
                        "#ff9651",
                        "#ff0000",
                        "#00ff00",
                        "#ccffde",
                        "#ffcccc",
                        "#0000ff"
                    ],

                    data: [
                        @foreach($indPie as $i)
                        @if($i->indName == 'Total')
                        @else
                        {{ $i->cnt }},
                        @endif
                        @endforeach
                    ]
                }]
            },
            options: {
                responsive: true,
                legend: {
                    display: false,
                    position: "bottom"
                },
                legendCallback: function (chart) {
                    //console.log(chart.data);
                    var text = [];
                    text.push('<ul>');
                    for (var i = 0; i < chart.data.datasets[0].data.length; i++) {
                        text.push('<li>');
                        text.push('<span style="color:white; background-color:'
                            + chart.data.datasets[0].backgroundColor[i] + '">&nbsp;'
                            + chart.data.datasets[0].data[i] + ' </span> &nbsp;');
                        if (chart.data.labels[i]) {
                            text.push(chart.data.labels[i]);
                        }
                        text.push('</li>');
                    }
                    text.push('</ul>');
                    return text.join("");
                }
            }
        });
        document.getElementById('pieLegend').innerHTML = myChart.generateLegend();
    </script>
@endsection