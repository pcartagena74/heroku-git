<?php
/**
 * Comment: The page to show all Event-related statistics
 * Created: 5/11/2017
 */

use App\Person;
use App\Ticket;
use App\RegFinance;
use App\EventSession;
use App\RegSession;

$topBits = ''; // there should be topBits for this

$headers = ['Ticket', 'Attendance Limit', 'Registrations', 'Wait List'];
$rows = [];

foreach($tkts as $t) {
    array_push($rows, ['<nobr>' . $t->ticketLabel . '</nobr>', $t->maxAttendees, $t->regCount, $t->waitCount]);
}

$reg_headers = ['First Name', 'Last Name', 'Ticket', 'Code', 'Register Date', 'Cost'];
$reg_rows = [];

foreach($regs as $r) {
    $p = Person::find($r->personID);
    array_push($reg_rows, [$p->firstName, $p->lastName, $r->ticket->ticketLabel, $r->discountCode, $r->createDate->format('Y/m/d'),
        '<i class="fa fa-dollar"></i>' . $r->subtotal]);
}

if(count($reg_rows) >= 15) {
    $scroll = 1;
} else {
    $scroll = 0;
}

$disc_headers = ['Code', 'Count', 'Cost', 'CC Fee', 'Handle Fee', 'Net'];
$disc_rows = [];

foreach($discPie as $d) {
    array_push($disc_rows, [$d->discountCode, $d->cnt,
        '<i class="fa fa-dollar"></i> ' . number_format($d->cost, 2, '.', ','),
        '<i class="fa fa-dollar"></i> ' . number_format($d->ccFee, 2, '.', ','),
        '<i class="fa fa-dollar"></i> ' . number_format($d->handleFee, 2, '.', ','),
        '<i class="fa fa-dollar"></i> ' . number_format($d->orgAmt, 2, '.', ',')
    ]);
}

if($event->hasTracks && $event->isSymmetric) {
    $columns = ($event->hasTracks * 2) + 1;
    $width   = (integer)85 / $event->hasTracks;
    $mw      = (integer)90 / $event->hasTracks;
} elseif($event->hasTracks) {
    $columns = $event->hasTracks * 3;
    $width   = (integer)80 / $event->hasTracks;
    $mw      = (integer)85 / $event->hasTracks;
}
$stats = '<a href="'.env('APP_URL').'/tracks/'.$event->eventID.'">Ticket Statistics</a>';
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
    @include('v1.parts.start_content', ['header' => $event->eventName, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @include('v1.parts.start_content', ['header' => $stats, 'subheader' => '', 'w1' => '6', 'w2' => '6', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    @include('v1.parts.datatable', ['headers' => $headers, 'data' => $rows, 'scroll' => 0])
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Discount Breakdown', 'subheader' => '', 'w1' => '6', 'w2' => '6', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <div class="col-md-6 col-sm-6 col-xs-6">
        <canvas id="discPie"></canvas>
    </div>
    <div id="pieLegend" class="col-md-6 col-sm-6 col-xs-6">
    </div>

    @include('v1.parts.end_content')

    <div class="col-md-12 col-sm-12 col-xs-12">
        <ul id="myTab" class="nav nav-tabs bar_tabs nav-justified" role="tablist">
            <li class="active"><a href="#tab_content1" id="attendees-tab" data-toggle="tab"
                                  aria-expanded="true"><b>Registered Attendees</b></a></li>
            <li class=""><a href="#tab_content2" id="finances-tab" data-toggle="tab"
                            aria-expanded="false"><b>Detailed Financial Data</b></a></li>
            @if($event->hasTracks)
                <li class=""><a href="#tab_content3" id="sessions-tab" data-toggle="tab"
                                aria-expanded="false"><b>Session Registration</b></a></li>
            @endif
        </ul>

        <div id="tab-content" class="tab-content">
            <div class="tab-pane active" id="tab_content1" aria-labelledby="attendees-tab">
                &nbsp;<br/>

                @include('v1.parts.datatable', ['headers' => $reg_headers, 'data' => $reg_rows, 'scroll' => $scroll])

            </div>
            <div class="tab-pane fade" id="tab_content2" aria-labelledby="finances-tab">
                &nbsp;<br/>
                @include('v1.parts.datatable', ['headers' => $disc_headers, 'data' => $disc_rows, 'scroll' => 0])
            </div>

            @if($event->hasTracks)
                <div class="tab-pane fade" id="tab_content3" aria-labelledby="sessions-tab">
                    <br/>

                    <table class="table table-bordered jambo_table table-striped">
                        <thead>
                        <tr>
                            <th colspan="{{ $columns }}" style="text-align: left;">
                                Track Selection
                            </th>
                        </tr>
                        </thead>
                        <tr>
                            @foreach($tracks as $track)
                                @if($tracks->first() == $track || !$event->isSymmetric)
                                    <th style="text-align:left;">Session Times</th>
                                @endif
                                <th colspan="2" style="text-align:center;"> {{ $track->trackName }} </th>
                            @endforeach
                        </tr>
                        @for($j=1;$j<=$event->confDays;$j++)
<?php
                            $z = EventSession::where([
                                ['confDay', '=', $j],
                                ['eventID', '=', $event->eventID]
                            ])->first();
                            $y = Ticket::find($z->ticketID);
?>

                            <tr>
                                <th style="text-align:center; color: yellow; background-color: #2a3f54;"
                                    colspan="{{ $columns }}">Day {{ $j }}:
                                    {{ $y->ticketLabel  }}
                                </th>
                            </tr>
                            @for($x=1;$x<=5;$x++)
<?php
                                // Check to see if there are any events for $x (this row)
                                $s = EventSession::where([
                                    ['eventID', $event->eventID],
                                    ['confDay', $j],
                                    ['order', $x]
                                ])->first();

                                // As long as there are any sessions, the row will be displayed
?>
                                @if($s !== null)
                                    <tr>
                                        @foreach($tracks as $track)
<?php
                                            $s = EventSession::where([
                                                ['trackID', $track->trackID],
                                                ['eventID', $event->eventID],
                                                ['confDay', $j],
                                                ['order', $x]
                                            ])->first();
?>
                                            @if($s !== null)
                                                @if($tracks->first() == $track || !$event->isSymmetric)

                                                    <td rowspan="1" style="text-align:left;">
                                                        <nobr> {{ $s->start->format('g:i A') }} </nobr>
                                                        &dash;
                                                        <nobr> {{ $s->end->format('g:i A') }} </nobr>
                                                    </td>
                                                @else

                                                @endif
                                                <td colspan="2" style="text-align:left; min-width:150px;
                                                        width: {{ $width }}%; max-width: {{ $mw }}%;">
<?php
                                                    // Find the counts of people for $s->sessionID broken out by discountCode in 'event-registration'.regID
                                                    $sRegs =
                                                        RegSession::join('event-registration as er', 'er.regID', '=', 'reg-session.regID')
                                                                  ->where([
                                                                      ['sessionID', $s->sessionID],
                                                                      ['er.eventID', $event->eventID]
                                                                  ])->select(DB::raw('er.discountCode, count(*) as cnt'))
                                                                  ->groupBy('er.discountCode')->get();
                                                    /*
                                                    $sRegs = DB::table('reg-session as rs')
                                                        ->where([
                                                            ['sessionID', $s->sessionID],
                                                            ['rs.eventID', $event->eventID]
                                                        ])
                                                        ->join('event-registration as er', 'er.regID', '=', 'rs.regID')
                                                        ->select(DB::raw('er.discountCode, count(*) as total'))
                                                        ->groupBy('er.discountCode')
                                                        ->get();
                                                    */
?>
                                                    <ul>
                                                    @foreach($sRegs as $sr)
                                                        <li>{{ $sr->discountCode or 'N/A' }}: {{ $sr->cnt }}</li>
                                                    @endforeach
                                                    </ul>
                                                </td>
                                            @else
                                                @if($tracks->first() == $track || !$event->isSymmetric)
                                                    <td colspan="3" style="text-align:left;">
                                                @else
                                                    <td colspan="2" style="text-align:left;">
                                                        @endif
                                                    </td>
                                                @endif
                                                @endforeach
                                    </tr>
                                @endif

                            @endfor

                        @endfor  {{-- this closes confDays loop --}}

                    </table>


                </div>
            @endif

        </div>
    </div>

    @include('v1.parts.end_content')

@endsection


@section('scripts')
    @if($scroll)
        @include('v1.parts.footer-datatable')
    @endif
    @if(count($rows) > 15 || count($reg_rows) > 15)
        <script>
            $(document).ready(function () {
                $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                    $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
                });
                $('#datatable-fixed-header').DataTable().search('').draw();
            });
        </script>
    @endif

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.min.js"></script>
    <script>
        var ctx = document.getElementById("discPie").getContext('2d');
        var options = {
            responsive: true,
            legend: {
                display: false,
                position: "bottom",
            },
            legendCallback: function (chart) {
                console.log(chart.data);
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
                    @foreach($discPie as $d)
                            @if($d->discountCode == '' or $d->discountCode == ' ')
                        'N/A',
                    @elseif($d->discountCode == 'Total')
                            @else
                        '{{ $d->discountCode }}',
                    @endif
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
                        "#ccffde",
                        "#d7ccff",
                        "#ffccf3",
                        "#ffcccc",
                        "#ff9651",
                        "#ff0000",
                        "#00ff00",
                        "#0000ff"
                    ],

                    data: [
                        @foreach($discPie as $d)
                        @if($d->discountCode == 'Total')
                        @else
                        {{ $d->cnt }},
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
                    console.log(chart.data);
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

            $SIDEBAR_MENU.find('a[href="{{ env('APP_URL') }}/event/create"]').parent('li').addClass('current-page').parents('ul').slideDown(function () {
                setContentHeight();
            }).parent().addClass('active');

            $("#add").text('Event Reporting');
        });
    </script>
@endsection


@section('modals')
@endsection