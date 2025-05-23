@php
    /**
     * Comment: Report showing member activity by year and industry breakdown
     * Created: 2/9/2017
     * -----------------
     * Updated: 10/4/2019 for configurable graph (display years)
     * Updated: 5/6/2022 for chart data limits (3Y) and member-only data display
     *
     * @var $id null or 1 to show all attendee data or members only data
     */

if($id == 1){
    $mbr_color = 'green';
    $mbr_url = '';
    $all_color = 'gray';
    $all_url = 'href="/mbrreport"';
} else {
    $id = 0;
    $mbr_color = 'gray';
    $mbr_url = 'href="/mbrreport/1"';
    $all_color = 'green';
    $all_url = '';
}
@endphp
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet"/>
    <div class="col-md-12 col-sm-12 col-xs-12">
        <ul class="nav nav-tabs bar_tabs nav-justified" id="myTab" role="tablist">
            <li class="active">
                <a aria-expanded="true" data-toggle="tab" href="#tab_content1" id="member_tab">
                    <b>@lang('messages.tabs.mem_demo')</b>
                </a>
            </li>
            <li class="">
                <a aria-expanded="false" data-toggle="tab" href="#tab_content2" id="heatmap_tab">
                    <b>@lang('messages.tabs.heat_map')</b>
                </a>
            </li>
        </ul>
        <div class="tab-content" id="tab-content">
            <div aria-labelledby="member_tab" class="tab-pane active" id="tab_content1">
                <br/>
                <b>@lang('messages.reports.show'):</b>
                <a {!! $all_url !!} class="btn btn-{!! $all_color !!} btn-sm">
                    <b>@lang('messages.reports.show_all')</b>
                </a>
                <a {!! $mbr_url !!} class="btn btn-{!! $mbr_color !!} btn-sm">
                    <b>@lang('messages.reports.show_mbr')</b>
                </a>
                <br />
                @include('v1.parts.start_content', ['header' => trans('messages.reports.ev_by_year'), 'subheader' => '',
                         'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

                <b> @lang('messages.reports.graph_years'): </b>
                {{--
                <a data-pk="{{ 1 }}" data-title="{{ trans('messages.reports.select_years') }}" data-type="select2" id="tags">
                    {!! $year_string !!}
                </a>
                --}}
                <input class="form-group select2" name="" type="text" value="{{ $year_string }}"/>
                <br/>

                <div id="canvas">
                </div>
                @include('v1.parts.end_content')

                @include('v1.parts.start_content', ['header' => trans('messages.reports.ind_brk'), 'subheader' => '',
                         'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
                <div class="col-md-3 pull-right">
                    <button class="btn" id="pie_chart_reset" onclick="resetChart()" style="display: none">
                        {{ trans('messages.buttons.reset') }}
                    </button>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <canvas id="indPie">
                    </canvas>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12" id="pieLegend">
                </div>
                @include('v1.parts.end_content')
            </div>
            <div aria-labelledby="heatmap_tab" class="tab-pane fade" id="tab_content2">
                @include('v1.parts.start_content', ['header' => trans('messages.reports.person_address'), 'subheader' => '',
                         'w1' => '12', 'w2' => '0', 'r1' => 0, 'r2' => 0, 'r3' => 0])
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div id="floating-panel">
                            @php
                                $home = ($heat_map_home_count);
                                $work = ($heat_map_work_count);
                                $other = ($heat_map_other_count);
                                $total = $home + $work + $other;
                            @endphp
                            <button class="btn btn-primary btn-sm active" onclick="initMap('all',this)">
                                {{trans('messages.buttons.mbr_total',['count'=>$total])}}
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="initMap('home',this)">
                                {{trans('messages.buttons.mbr_home',['count'=>$home])}}
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="initMap('work',this)">
                                {{trans('messages.buttons.mbr_work',['count'=>$work])}}
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="initMap('other',this)">
                                {{trans('messages.buttons.mbr_other',['count'=>$other])}}
                            </button>
                        </div>
                        <div id="map" style="height: 600px">
                        </div>
                    </div>
                </div>
                @include('v1.parts.end_content')
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @include('v1.parts.menu-fix', array('path' => '/mbrreport'))
    @include('v1.parts.footer-chart')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.min.js">
    </script>
    <script>
        // $.fn.editable.defaults.ajaxOptions = {type: "POST"};
        var result;
        $.ajaxSetup({
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.fn.editable.defaults.mode = 'popup';

        {{--
        $('#tags').editable({
                placement: 'right',
                inputclass: 'input-large',
                select2: {
                    tags: [{!! $quote_string !!}],
                    tokenSeparators: [",", " "]
                },
                success: function (data) {
                    // updateMorrisChart(data);
                    // window.location = "{{ env('APP_URL') . "/mbrreport/$orgID" }}";
                    result = eval(data);
                },
            });
        --}}

        var yearSelect = $('.select2').select2({
            tags: [{!! $quote_string !!}],
            tokenSeparators: [",", " "],
            allowClear: false,
            multiple: true,
            sorter: data => data.sort((a, b) => a.text.localeCompare(b.text)),
        });
        yearSelect.val([{!! $limit_string !!}]).trigger("change");
        //yearSelect.trigger("change");
        // yearSelect.find('option')[0].selected=true;

        yearSelect.on("change", function (e) {
            // console.log("on change called");
            // console.log(e.val);
            // var data = e.params;
            updateMorrisChart(e.val);
        });

        yearSelect.on('select', function (e) {
            console.log("on select called");
            var id = e.params.data.id;
            var option = $(e.target).children('[value=' + id + ']');
            option.detach();
            $(e.target).append(option).change();
        });

        var morris = '';
        $(document).ready(function () {
            morris = Morris.Bar({
                element: 'canvas',
                data: [{!! $datastring !!}],
                xkey: '{{ trans_choice('messages.headers.events', 2) }}',
                ykeys: [{!! $labels !!}],
                labels: [{!! $labels !!}],
                barRatio: 0.1,
                xLabelAngle: 35,
                hideHover: 'auto',
                resize: true,
                stacked: false,
            });

            $('[data-toggle="tooltip"]').tooltip();
            updateMorrisChart([{!! $limit_string !!}]);
        });

        function updateMorrisChart(list) {
            list.sort();
            var morris_data_real = [{!! $datastring !!}];
            $.each(morris_data_real, function (key, value) {
                $.each(value, function (key_in, value_in) {
                    if (list.indexOf(key_in) === -1 && key_in != 'Events') {
                        delete morris_data_real[key][key_in];
                    }
                });
            });
            morris.options.ykeys = list;
            morris.options.labels = list;
            morris.setData(morris_data_real);
        }

        var ctx = document.getElementById("indPie").getContext('2d');
        var options = {
            responsive: true,
            legend: {
                display: false,
                position: "bottom"
            },
            legendCallback: function (chart) {
                //console.log(chart.data);
                generateIndPieChartLegend(chart);
            }
        };
        var myChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: [
                    @php
                        // if industry not found in transalation show realname
                        foreach($indPie as $i){
                            $ind_name = trim($i->indName);
                            if(trans('messages.fields.industries.' . $ind_name) == 'messages.fields.industries.' . $ind_name){
                                echo '"% '.$ind_name.'",';
                            } else {
                                echo '"% '.trans('messages.fields.industries.' . $ind_name).'",';
                            }
                        }
                    @endphp
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
                        "#0000ff",
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
                        "#0000ff",
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
                    position: "bottom",
                },
                legendCallback: function (chart) {
                    //console.log(chart.data);
                    return generateIndPieChartLegent(chart);
                },

            }
        });

        document.getElementById('pieLegend').innerHTML = myChart.generateLegend();

        $('body').on('click', '#pieLegend ul li', function () {
            var index_remove = $(this).data('dataset');
            let to_add = myChart.data.datasets[0].data[index_remove];
            let other = '% {{ trans('messages.fields.industries.Other') }}';
            let other_key = Object.values(myChart.data.labels).indexOf(other);
            if (index_remove == other_key) {
                return;
            }
            $('#pie_chart_reset').show();
            myChart.data.datasets[0].data[other_key] = myChart.data.datasets[0].data[other_key] + to_add;
            let total = Number(Math.round(myChart.data.datasets[0].data[other_key] + 'e' + 2) + 'e-' + 2)
            myChart.data.datasets[0].data[other_key] = total;
            myChart.data.labels.splice(index_remove, 1);
            myChart.data.datasets[0].data.splice(index_remove, 1);
            let legend = generateIndPieChartLegent(myChart);
            document.getElementById('pieLegend').innerHTML = legend;
            $(this).tooltip('hide');
            $('[data-toggle="tooltip"]').tooltip();
            myChart.update();
        });

        function resetChart() {
            $('#pie_chart_reset').hide();
            let labels = [@foreach($indPie as $i)
                '% {{ trans('messages.fields.industries.' . $i->indName) }}',
                @endforeach];
            let data = [
                @foreach($indPie as $i)
                        @if($i->indName == 'Total')
                        @else
                        {{ $i->cnt }},
                @endif
                @endforeach
            ];
            myChart.data.datasets[0].data = data;
            myChart.data.labels = labels;
            let legend = generateIndPieChartLegent(myChart);
            document.getElementById('pieLegend').innerHTML = legend;
            $('[data-toggle="tooltip"]').tooltip();
            myChart.update();
        }

        function generateIndPieChartLegent(chart) {
            var text = [];
            var tooltip = '{{trans("messages.fields.mbr_report_chart_tooltip")}}';
            text.push('<ul>');
            for (var i = 0; i < chart.data.datasets[0].data.length; i++) {
                text.push('<li data-dataset="' + i + '" data-toggle="tooltip" title="' + tooltip + '" data-placement="left">');
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

        var map, heatmap;

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var target = $(e.target).attr("href") // activated tab
            if (target == '#tab_content2') {
                setTimeout(initMap('all'), 1);
            }
        });

        function initMap(type = 'all', ths) {
            var bounds = new google.maps.LatLngBounds();
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: {{ empty($org->heatMapZoomLevel) ? 7 : $org->heatMapZoomLevel }},
                center: {lat: {{$org_lat_lng['lati']}}, lng: {{$org_lat_lng['longi']}}},
                mapTypeId: 'roadmap',
                styles: [{"stylers": [{"saturation": -100}, {"gamma": 1}]}, {
                    "elementType": "labels.text.stroke",
                    "stylers": [{"visibility": "off"}]
                }, {
                    "featureType": "poi.business",
                    "elementType": "labels.text",
                    "stylers": [{"visibility": "off"}]
                }, {
                    "featureType": "poi.business",
                    "elementType": "labels.icon",
                    "stylers": [{"visibility": "off"}]
                }, {
                    "featureType": "poi.place_of_worship",
                    "elementType": "labels.text",
                    "stylers": [{"visibility": "off"}]
                }, {
                    "featureType": "poi.place_of_worship",
                    "elementType": "labels.icon",
                    "stylers": [{"visibility": "off"}]
                }, {
                    "featureType": "road",
                    "elementType": "geometry",
                    "stylers": [{"visibility": "simplified"}]
                }, {
                    "featureType": "water",
                    "stylers": [{"visibility": "on"}, {"saturation": 50}, {"gamma": 0}, {"hue": "#50a5d1"}]
                }, {
                    "featureType": "administrative.neighborhood",
                    "elementType": "labels.text.fill",
                    "stylers": [{"color": "#333333"}]
                }, {
                    "featureType": "road.local",
                    "elementType": "labels.text",
                    "stylers": [{"weight": 0.5}, {"color": "#333333"}]
                }, {
                    "featureType": "transit.station",
                    "elementType": "labels.icon",
                    "stylers": [{"gamma": 1}, {"saturation": 50}]
                }]
            });

            var all_points = getPoints(type);
            for (var i = 0; i < all_points.length; i++) {
                bounds.extend(all_points[i]);
            }
            if (bounds.isEmpty()) {
                map.setCenter({lat: {{$org_lat_lng['lati']}}, lng: {{$org_lat_lng['longi']}}});
            } else {
                // map.fitBounds(bounds); removed as now we have org based zoom
                // var listener = google.maps.event.addListener(map, "bounds_changed", function() {
                //     console.log('here1',map.getZoom());
                //   if (map.getZoom()){
                //     map.setZoom(8);
                //     console.log('here2');
                //   }
                //   // google.maps.event.removeListener(listener);
                // });
                // setTimeout(function(){google.maps.event.removeListener(listener)}, 2000);
            }

            heatmap = new google.maps.visualization.HeatmapLayer({
                data: all_points,
                map: map,
                opacity: 1
            });
            heatmap.setMap(map);
            if (ths) {
                $('#floating-panel').find('.btn').removeClass('active');
                $(ths).addClass('active');
            }
        }

        function toggleHeatmap() {
            heatmap.setMap(heatmap.getMap() ? null : map);
        }

        function changeGradient() {
            var gradient = [
                'rgba(0, 255, 255, 0)',
                'rgba(0, 255, 255, 1)',
                'rgba(0, 191, 255, 1)',
                'rgba(0, 127, 255, 1)',
                'rgba(0, 63, 255, 1)',
                'rgba(0, 0, 255, 1)',
                'rgba(0, 0, 223, 1)',
                'rgba(0, 0, 191, 1)',
                'rgba(0, 0, 159, 1)',
                'rgba(0, 0, 127, 1)',
                'rgba(63, 0, 91, 1)',
                'rgba(127, 0, 63, 1)',
                'rgba(191, 0, 31, 1)',
                'rgba(255, 0, 0, 1)'
            ]
            heatmap.set('gradient', heatmap.get('gradient') ? null : gradient);
        }

        function changeRadius() {
            heatmap.set('radius', heatmap.get('radius') ? null : 20);
        }

        function changeOpacity() {
            heatmap.set('opacity', heatmap.get('opacity') ? null : 0.2);
        }

        function getPoints(type) {
            var points = [];
            var home = @json($heat_map_home);
            var work = @json($heat_map_work);
            var other = @json($heat_map_other);
            switch (type) {
                case 'all':
                    $.each(home, function (index, value) {
                        points.push(new google.maps.LatLng(value['lati'], value['longi']));
                    });
                    $.each(work, function (index, value) {
                        points.push(new google.maps.LatLng(value['lati'], value['longi']));
                    });
                    $.each(other, function (index, value) {
                        points.push(new google.maps.LatLng(value['lati'], value['longi']));
                    });
                    return points;
                    break;
                case 'home':
                    $.each(home, function (index, value) {
                        points.push(new google.maps.LatLng(value['lati'], value['longi']));
                    });
                    return points;
                    break;
                case 'work':
                    $.each(work, function (index, value) {
                        points.push(new google.maps.LatLng(value['lati'], value['longi']));
                    });
                    return points;
                    break;
                case 'other':
                    $.each(other, function (index, value) {
                        points.push(new google.maps.LatLng(value['lati'], value['longi']));
                    });
                    return points;
                    break;
            }
            // return [ sample
            //   new google.maps.LatLng(37.776772, -122.438498),
            // ];
        }

    </script>
    <script async="" defer=""
            src="https://maps.googleapis.com/maps/api/js?key={{env('GOOGLE_API_KEY')}}&callback=initMap&libraries=visualization"
            type="text/javascript">
    </script>
@endsection
