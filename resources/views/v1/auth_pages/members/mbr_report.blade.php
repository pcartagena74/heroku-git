@php
/**
 * Comment: Report showing member activity by year and industry breakdown
 * Created: 2/9/2017
 * -----------------
 * Updated: 10/4/2019 for configurable graph (display years)
 *
 */
@endphp
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet"/>
<div class="col-md-12 col-sm-12 col-xs-12">
    <ul class="nav nav-tabs bar_tabs nav-justified" id="myTab" role="tablist">
        <li class="active">
            <a aria-expanded="true" data-toggle="tab" href="#tab_content1" id="member_tab">
                <b>
                    @lang('messages.tabs.mem_demo')
                </b>
            </a>
        </li>
        <li class="">
            <a aria-expanded="false" data-toggle="tab" href="#tab_content2" id="everyone_tab">
                <b>
                    @lang('messages.tabs.heat_map')
                </b>
            </a>
        </li>
        {{--
                    @if(Entrust::hasRole('Speaker'))
        <li class="">
            <a aria-expanded="false" data-toggle="tab" href="#tab_content3" id="other-tab">
                <b>
                    Third Thing
                </b>
            </a>
        </li>
        @endif
        --}}
    </ul>
    <div class="tab-content" id="tab-content">
        <div aria-labelledby="member_tab" class="tab-pane active" id="tab_content1">
            <br/>
            @include('v1.parts.start_content', ['header' => trans('messages.reports.ev_by_year'), 'subheader' => '',
                     'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
            <b>
                @lang('messages.reports.graph_years'):
            </b>
            {{--
            <a data-pk="{{ 1 }}" data-title="{{ trans('messages.reports.select_years') }}" data-type="select2" id="tags">
                {!! $year_string !!}
            </a>
            --}}
            <input class="form-group select2" name="" type="text" value="{{$year_string}}"/>
            <br/>
            <div id="canvas">
            </div>
            @include('v1.parts.end_content')

            @include('v1.parts.start_content', ['header' => trans('messages.reports.ind_brk'), 'subheader' => '',
                     'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
            <div class="col-md-12 col-sm-12 col-xs-12">
                <canvas id="indPie">
                </canvas>
            </div>
            <div class="col-md-12 col-sm-12 col-xs-12" id="pieLegend">
            </div>
            @include('v1.parts.end_content')
        </div>
        <div aria-labelledby="everyone_tab" class="tab-pane fade" id="tab_content2">
            <div id="floating-panel">
                <button onclick="toggleHeatmap()">
                    Toggle Heatmap
                </button>
                <button onclick="changeGradient()">
                    Change gradient
                </button>
                <button onclick="changeRadius()">
                    Change radius
                </button>
                <button onclick="changeOpacity()">
                    Change opacity
                </button>
            </div>
            <div id="map">
            </div>
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
        $('#tags').editable({
            placement: 'right',
            inputclass: 'input-large',
            select2: {
                tags: [{!! $quote_string !!}],
                tokenSeparators: [",", " "]
            },
            success: function (data) {
                // updateMorrisChart(data);
                result = eval(data);
                // window.location = "{{ env('APP_URL') . "/mbrreport/$orgID" }}";
            },
        });
        var yearSelect = $('.select2').select2({
            tags: [{!! $quote_string !!}],
            tokenSeparators: [",", " "],
            allowClear: false,
            sorter: data => data.sort((a, b) => a.text.localeCompare(b.text)),
        });
        yearSelect.on("change", function (e) { 
            var data = e.params;
            console.log("change",e.val); 
            updateMorrisChart(e.val);
        });
        yearSelect.on('select', function(e){
          var id = e.params.data.id;
          var option = $(e.target).children('[value='+id+']');
          option.detach();
          $(e.target).append(option).change();
        });

    var morris = '';
    $(document).ready(function () {
        morris = Morris.Bar({
            element: 'canvas',
            data: [{!! $datastring !!}],
            xkey: '{{ trans_choice('messages.headers.events', 2) }}',
            ykeys: [ {!! $labels !!} ],
            labels: [ {!! $labels !!} ],
            barRatio: 0.1,
            xLabelAngle: 35,
            hideHover: 'auto',
            resize: true,
        });
    });
    function updateMorrisChart(list){
        list.sort();
        var morris_data_real = [{!! $datastring !!}];
        $.each(morris_data_real,function(key,value){
            $.each(value,function(key_in,value_in){
                if(list.indexOf(key_in) === -1 && key_in != 'Events'){
                    delete morris_data_real[key][key_in];
                }
            });
        });
        morris.options.ykeys = list;
        morris.options.labels = list;
        morris.setData(morris_data_real);
    }
    var map, hesatmap;
    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
          zoom: 13,
          center: {lat: 37.775, lng: -122.434},
          mapTypeId: 'satellite'
        });

        heatmap = new google.maps.visualization.HeatmapLayer({
          data: getPoints(),
          map: map
        });
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

      // Heatmap data: 500 Points
      function getPoints() {
        return [
          new google.maps.LatLng(37.776772, -122.438498),
          new google.maps.LatLng(37.776787, -122.438389),
          new google.maps.LatLng(37.776848, -122.438283),
          new google.maps.LatLng(37.776870, -122.438239),
          new google.maps.LatLng(37.777015, -122.438198),
          new google.maps.LatLng(37.777333, -122.438256),
          new google.maps.LatLng(37.777595, -122.438308),
          new google.maps.LatLng(37.777797, -122.438344),
          new google.maps.LatLng(37.778160, -122.438442),
          new google.maps.LatLng(37.778414, -122.438508),
          new google.maps.LatLng(37.778445, -122.438516),
          new google.maps.LatLng(37.778503, -122.438529),
          new google.maps.LatLng(37.778607, -122.438549),
          new google.maps.LatLng(37.778670, -122.438644),
          new google.maps.LatLng(37.778847, -122.438706),
          new google.maps.LatLng(37.779240, -122.438744),
          new google.maps.LatLng(37.779738, -122.438822),
          new google.maps.LatLng(37.758182, -122.405695),
          new google.maps.LatLng(37.757676, -122.405118),
          new google.maps.LatLng(37.757039, -122.404346),
          new google.maps.LatLng(37.756335, -122.403719),
          new google.maps.LatLng(37.755503, -122.403406),
          new google.maps.LatLng(37.754665, -122.403242),
          new google.maps.LatLng(37.753837, -122.403172),
          new google.maps.LatLng(37.752986, -122.403112),
          new google.maps.LatLng(37.751266, -122.403355)
        ];
      }
</script>
{{--
<script async="" defer="" src="https://maps.googleapis.com/maps/api/js?key={{env('GOOGLE_API_KEY')}}&callback=initMap" type="text/javascript">
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{env('GOOGLE_API_KEY')}}&libraries=visualization">
</script>
--}}
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
                        '% {{ trans('messages.fields.industries.' . $i->indName) }}',
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
                    position: "bottom"
                },
                legendCallback: function (chart) {
                    //console.log(chart.data);
                    var text = [];
                    text.push('<ul>');
                    for (var i = 0; i < chart.data.datasets[0].data.length; i++) {
                        text.push('<li data-dataset="'+i+'">');
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
        $('#pieLegend ul li').on('click',function(){
            if(myChart.getDatasetMeta(0).data[$(this).data('dataset')].hidden == true){
                myChart.getDatasetMeta(0).data[$(this).data('dataset')].hidden = false;
                $(this).unwrap();
            } else {
                myChart.getDatasetMeta(0).data[$(this).data('dataset')].hidden = true;
                $(this).wrap("<strike>");
            }
            myChart.update();
        });
</script>
@endsection
