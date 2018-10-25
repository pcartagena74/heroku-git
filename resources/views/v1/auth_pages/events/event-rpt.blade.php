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

/**
 * To Do:
 * 1. Past Event: show a tab that allows for registration confirmations & allows to add registrants (free events only)
 * 2.
 */
$today = \Carbon\Carbon::now();
$topBits = ''; // there should be topBits for this

$rows = []; $reg_rows = []; $notreg_rows = []; $tag_rows = []; $dead_rows = []; $i = 0;
if ($event->eventEndDate->gte($today)) {
    $headers = [trans('messages.fields.ticket'), trans('messages.headers.att_limit'), trans('messages.headers.this'),
                trans('messages.headers.tot_regs'), trans('messages.headers.wait')];
    if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin')){
        foreach ($tkts as $t) {

            $rc = '<form action="' . env('APP_URL') . '/ticket/' .$t->ticketID.'" method="post">' . csrf_field();
            $rc .= '<input type="hidden" name="value" value="1">';
            $rc .= '<input type="hidden" name="name" value="regCount-' . $t->ticketID . '">';
            $rc .= '<button class="btn btn-danger btn-xs" id="launchConfirm">Go</button></form>';

            $rc = '<a href="#" id="regCount-' . $t->ticketID . '" data-name="regCount-' .$t->ticketID.'" data-value="'.$t->regCount.
                '" data-url="' . env('APP_URL') . '/ticket/' .$t->ticketID.
                '" data-pk="'.$t->ticketID.'"></a>';

            $wc = "<a href='#' id='waitCount-$t->ticketID' name='waitCount-$t->ticketID' data-value='$t->waitCount' data-url='" . env('APP_URL') .
                "/ticket/$t->ticketID' data-pk='$t->ticketID'></a>";

            // $t->regCount, $t->waitCount
            array_push($rows, ['<nobr>' . $t->ticketLabel . '</nobr>', $t->maxAttendees, $t->week_sales(), $rc, $wc]);
        }
    } else {
        foreach ($tkts as $t) {
            array_push($rows, ['<nobr>' . $t->ticketLabel . '</nobr>', $t->maxAttendees, $t->week_sales(), $t->regCount, $t->waitCount]);
        }
    }
} else {
    $headers = [trans('messages.fields.ticket'), trans('messages.headers.att_limit'),
                trans('messages.headers.tot_regs'), trans('messages.headers.wait')];
    foreach ($tkts as $t) {
        array_push($rows, ['<nobr>' . $t->ticketLabel . '</nobr>', $t->maxAttendees, $t->regCount, $t->waitCount]);
    }
}

$reg_headers = ['RegID', trans('messages.fields.firstName'), trans('messages.fields.lastName'), trans('messages.fields.ticket'),
                trans('messages.headers.disc_code'), trans('messages.headers.reg_date'), trans('messages.headers.cost'), trans('messages.headers.reg_can')];
$dead_headers = ['RegID', trans('messages.fields.firstName'), trans('messages.fields.lastName'),
                trans('messages.fields.ticket'), trans('messages.headers.disc_code'), trans('messages.headers.reg_date'),
                trans('messages.headers.cost'), trans('messages.headers.pmt')];
$notreg_headers = ['RegID', trans('messages.headers.status'), trans('messages.fields.firstName'), trans('messages.fields.lastName'),
                 trans('messages.fields.ticket'), trans('messages.headers.disc_code'), trans('messages.headers.reg_date'),
                 trans('messages.headers.cost'), trans('messages.headers.reg_can')];

if ($event->eventTypeID == 5) {
    if ($event->hasFood) {
        $tag_headers = ['RegID', trans('messages.fields.prefName'), trans('messages.fields.lastName'), trans('messages.headers.isFirst'),
            trans('messages.headers.email'), trans('messages.fields.ticket'), trans('messages.headers.disc_code'),
            trans('messages.headers.chap'), trans('messages.headers.role'), trans('messages.headers.allergens')];
    } else {
        $tag_headers = ['RegID', trans('messages.fields.prefName'), trans('messages.fields.lastName'), trans('messages.headers.isFirst'),
            trans('messages.headers.email'), trans('messages.fields.ticket'), trans('messages.headers.disc_code'),
            trans('messages.headers.chap'), trans('messages.headers.role')];
    }
    foreach ($regs as $r) {
        $p = Person::find($r->personID);
        if ($event->hasFood) {
            if (strpos($p->allergenInfo, 'Other') !== false) {
                if ($p->allergenNote !== null) {
                    $allergies = $p->allergenInfo . ": " . $p->allergenNote;
                } else {
                    $allergies = $p->allergenInfo;
                }
            } else {
                $allergies = $p->allergenInfo;
            }
            array_push($tag_rows, ["<a href='" . env('APP_URL') . "/profile/" . $p->personID . "'>" . $r->regID . "</a>",
                $p->prefName, $p->lastName, $r->isFirstEvent == 1 ? trans('messages.yesno_check.yes') : trans('messages.yesno_check.no'), $p->login, $r->ticket->ticketLabel,
                $r->discountCode, $p->affiliation, $p->chapterRole, $allergies]);
        } else {
            array_push($tag_rows, ["<a href='" . env('APP_URL') . "/profile/" . $p->personID . "'>" . $r->regID . "</a>",
                $p->prefName, $p->lastName, $r->isFirstEvent == 1 ? trans('messages.yesno_check.yes') : trans('messages.yesno_check.no'), $p->login, $r->ticket->ticketLabel,
                $r->discountCode, $p->affiliation, $p->chapterRole]);
        }
    }
} else {
    if ($event->hasFood) {
        $tag_headers = ['RegID', trans('messages.fields.prefName'), trans('messages.fields.lastName'), trans('messages.headers.isFirst'),
            trans('messages.headers.email'), trans('messages.fields.ticket'), trans('messages.headers.disc_code'),
            trans('messages.headers.comp'), trans('messages.fields.title'), ucwords(trans('messages.headers.ind')),
            trans('messages.headers.allergens')];
    } else {
        $tag_headers = ['RegID', trans('messages.fields.prefName'), trans('messages.fields.lastName'), trans('messages.headers.isFirst'),
            trans('messages.headers.email'), trans('messages.fields.ticket'), trans('messages.headers.disc_code'),
            trans('messages.headers.comp'), trans('messages.fields.title'), ucwords(trans('messages.headers.ind'))];
    }
    foreach ($regs as $r) {
        $p = Person::find($r->personID);
        if ($event->hasFood) {
            if (strpos($p->allergenInfo, 'Other') !== false) {
                if ($p->allergenNote !== null) {
                    $allergies = $p->allergenInfo . ": " . $p->allergenNote;
                } else {
                    $allergies = $p->allergenInfo;
                }
            } else {
                $allergies = $p->allergenInfo;
            }
            array_push($tag_rows, ["<a href='" . env('APP_URL') . "/profile/" . $p->personID . "'>" . $r->regID . "</a>",
                $p->prefName, $p->lastName, $r->isFirstEvent == 1 ? trans('messages.yesno_check.yes') : trans('messages.yesno_check.no'), $p->login, $r->ticket->ticketLabel,
                $r->discountCode, $p->compName, $p->title, $p->indName, $allergies]);
        } else {
            array_push($tag_rows, ["<a href='" . env('APP_URL') . "/profile/" . $p->personID . "'>" . $r->regID . "</a>",
                $p->prefName, $p->lastName, $r->isFirstEvent == 1 ? trans('messages.yesno_check.yes') : trans('messages.yesno_check.no'), $p->login, $r->ticket->ticketLabel,
                $r->discountCode, $p->compName, $p->title, $p->indName]);
        }
    }
}

foreach ($regs as $r) {

    $v = View::make('v1.parts.reg_cancel_button', ['reg' => $r]); $c = $v->render();
    array_push($reg_rows, [$r->regID, $r->person->firstName, $r->person->lastName, $r->ticket->ticketLabel, $r->discountCode,
               $r->createDate->format('Y/m/d'), trans('messages.symbols.cur') . number_format($r->subtotal, 2, '.', ''), $c]);
}

foreach ($deadbeats as $r) {
    $f = '';
    if ($r->subtotal > 0) {
        if (Entrust::hasRole('Admin')) {
            $f = Form::open(['method' => 'post', 'route' => ['accept_payment', $r->regID, $r->rfID], 'data-toggle' => 'validator']);
            $f .= '<button type="submit" name="' . trans('messages.buttons.check') . '" class="btn btn-success btn-sm" data-toggle="tooltip" title="' . trans('messages.tooltips.cash') . '">';
            $f .= trans('messages.symbols.cash').'</button>';

            $f .= '<button type="submit" name="'. trans('messages.buttons.check') . '" class="btn btn-primary btn-sm" data-toggle="tooltip" title="' . trans('messages.tooltips.check') . '">';
            $f .= trans('messages.symbols.check').'</button></form>';
            $f .= Form::open(['method' => 'delete', 'route' => ['cancel_registration', $r->regID, $r->rfID], 'data-toggle' => 'validator']);
            $f .= '<button type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip" title="' . trans('messages.tooltips.reg_cancel') . '">';
            $f .= trans('messages.symbols.trash') .'</button></form>';
        } else {
            $f .= '<button type="submit" class="btn btn-secondary btn-sm" data-toggle="tooltip" title="' . trans('messages.tooltips.no_auth') . '">';
            $f .= '<i class="far fa-money-bill-wave"></i></button>';
            $f .= '<button type="submit" class="btn btn-secondary btn-sm" data-toggle="tooltip" title="' . trans('messages.tooltips.no_auth') . '">';
            $f .= '<i class="far fa-money-check-alt"></i></button>';
        }
        array_push($dead_rows, [$r->regID, $r->person->firstName, $r->person->lastName, $r->ticket->ticketLabel, $r->discountCode, $r->createDate->format('Y/m/d'),
            '<i class="far fa-dollar-sign"></i> ' . number_format($r->subtotal, 2, '.', ''), $f]);
    }
}

foreach ($notregs as $r) {
    //$f = '';
    //$f = Form::open(['method' => 'delete', 'route' => ['cancel_registration', $r->regID, $r->rfID], 'data-toggle' => 'validator']);
    //$f .= '<button type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip" title="'. trans('messages.tooltips.reg_cancel') . '">';
    //$f .= '<i class="far fa-trash-alt"></i></button></form>';

    $v = View::make('v1.parts.reg_cancel_button', ['reg' => $r]); $c = $v->render();
    array_push($notreg_rows, [$r->regID, $r->regStatus, $r->person->firstName, $r->person->lastName,
        $r->ticket->ticketLabel, $r->discountCode, $r->createDate->format('Y/m/d'),
        '<i class="far fa-dollar-sign"></i> ' . number_format($r->subtotal, 2, '.', ''), $c]);
}

if (count($reg_rows) >= 15) {
    $scroll = 1;
} else {
    $scroll = 0;
}

if (count($notreg_rows) >= 15) {
    $notscroll = 1;
} else {
    $notscroll = 0;
}

if (count($tag_rows) >= 15) {
    $tagscroll = 1;
} else {
    $tagscroll = 0;
}

$disc_headers = [trans('messages.headers.code'), trans('messages.fields.count'), trans('messages.headers.cost'),
                 trans('messages.headers.ccfee'), trans('messages.headers.handling'), trans('messages.headers.net')];
$disc_rows = [];

foreach ($discPie as $d) {
    array_push($disc_rows, [$d->discountCode, $d->cnt,
        trans('messages.symbols.cur') . number_format($d->cost, 2, '.', ','),
        trans('messages.symbols.cur') . number_format($d->ccFee, 2, '.', ','),
        trans('messages.symbols.cur') . number_format($d->handleFee, 2, '.', ','),
        trans('messages.symbols.cur') . number_format($d->orgAmt, 2, '.', ',')
    ]);
}

if ($event->hasTracks && $event->isSymmetric) {
    $columns = ($event->hasTracks * 2) + 1;
    $width = (integer)85 / $event->hasTracks;
    $mw = (integer)90 / $event->hasTracks;
    $stats = '<a href="' . env('APP_URL') . '/tracks/' . $event->eventID . '">' . trans('messages.fields.ticket') . " " . trans('messages.headers.stats') .'</a>';
} elseif ($event->hasTracks) {
    $columns = $event->hasTracks * 3;
    $width = (integer)80 / $event->hasTracks;
    $mw = (integer)85 / $event->hasTracks;
    $stats = '<a href="' . env('APP_URL') . '/tracks/' . $event->eventID . '">' . trans('messages.fields.ticket') . " " . trans('messages.headers.stats') .'</a>';
} else {
    $stats = trans('messages.fields.ticket') . " " . trans('messages.headers.stats');
}
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    <div class="col-xs-12">
        <div class="col-xs-6">
            @include('v1.parts.event_buttons', ['event' => $event])
        </div>
    </div>

    @include('v1.parts.start_content', ['header' => $event->eventName, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @include('v1.parts.start_content', ['header' => $stats, 'subheader' => '', 'w1' => '6', 'w2' => '6', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    @include('v1.parts.datatable', ['headers' => $headers, 'data' => $rows, 'scroll' => 0])
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => trans('messages.fields.disc') . ' ' . trans('messages.headers.breakdown'),
                                        'subheader' => '', 'w1' => '6', 'w2' => '6', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <div class="col-md-6 col-sm-6 col-xs-6">
        <canvas id="discPie"></canvas>
    </div>
    <div id="pieLegend" class="col-md-6 col-sm-6 col-xs-6">
    </div>

    @include('v1.parts.end_content')

    <div class="col-md-12 col-sm-12 col-xs-12">
        <ul id="myTab" class="nav nav-tabs bar_tabs nav-justified" role="tablist">
            <li class="active"><a href="#tab_content1" id="attendees-tab" data-toggle="tab"
                                  aria-expanded="true"><b>@lang('messages.headers.reged') {{ trans_choice('messages.headers.att', 2) }}</b></a></li>
            @if(count($deadbeats) > 0)
            <li class=""><a href="#tab_content6" id="pending-tab" data-toggle="tab"
                                  aria-expanded="true"><b>@lang('messages.headers.doored')</b></a></li>
            @endif
            @if(count($notregs) > 0)
            <li class=""><a href="#tab_content4" id="nonreg-tab" data-toggle="tab"
                            aria-expanded="false"><b>@lang('messages.headers.wait') {{ strtolower(__('messages.headers.or')) }} @lang('messages.headers.int_reg')</b></a></li>
            @endif
            <li class=""><a href="#tab_content2" id="finances-tab" data-toggle="tab"
                            aria-expanded="false"><b>@lang('messages.headers.det_fd')</b></a></li>
            @if($event->hasTracks)
                <li class=""><a href="#tab_content3" id="sessions-tab" data-toggle="tab"
                                aria-expanded="false"><b>@lang('messages.buttons.sess_reg')</b></a></li>
            @endif
            <li class=""><a href="#tab_content5" id="nametags-tab" data-toggle="tab"
                            aria-expanded="false"><b>@lang('messages.headers.nametags')</b></a></li>
        </ul>

        <div id="tab-content" class="tab-content">
            <div class="tab-pane active" id="tab_content1" aria-labelledby="attendees-tab">
                &nbsp;<br/>

                @if(count($reg_rows)>0)
                    @include('v1.parts.datatable', ['headers' => $reg_headers, 'data' => $reg_rows, 'scroll' => $scroll])
                @else
                    @lang('messages.instructions.no_regs')
                @endif

            </div>
            <div class="tab-pane fade" id="tab_content4" aria-labelledby="nonreg-tab">
                &nbsp;<br/>

                @if(count($notreg_rows)>0)
                    @include('v1.parts.datatable', ['headers' => $notreg_headers, 'data' => $notreg_rows, 'scroll' => $notscroll])
                @else
                    @lang('messages.instructions.no_waits')
                @endif

            </div>
            <div class="tab-pane fade" id="tab_content6" aria-labelledby="pending-tab">
                &nbsp;<br/>

                @if(count($dead_rows)>0)
                    @include('v1.parts.datatable', ['headers' => $dead_headers, 'data' => $dead_rows, 'scroll' => $notscroll])
                @else
                    @lang('messages.instructions.no_deadbeats')
                @endif

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
                                @lang('messages.fields.track_select')
                            </th>
                        </tr>
                        </thead>
                        <tr>
                            @foreach($tracks as $track)
                                @if($tracks->first() == $track || !$event->isSymmetric)
                                    <th style="text-align:left;">@lang('messages.fields.sess_times')</th>
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
                                                    $sTotal = 0;
                                                    $sRegs =
                                                        RegSession::join('event-registration as er', 'er.regID', '=', 'reg-session.regID')
                                                            ->where([
                                                                ['sessionID', $s->sessionID],
                                                                ['er.eventID', $event->eventID]
                                                            ])->select(DB::raw('er.discountCode, count(*) as cnt'))
                                                            ->groupBy('er.discountCode')->get();
?>
                                                    <ul>
                                                        @foreach($sRegs as $sr)
                                                            <li>{{ $sr->discountCode or 'N/A' }}: {{ $sr->cnt }}</li>
                                                            <?php $sTotal += $sr->cnt; ?>
                                                        @endforeach
                                                        <li><b>@lang('messages.fields.total'): {{ $sTotal }}</b></li>
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

            <div class="tab-pane fade" id="tab_content5" aria-labelledby="nametags-tab">
                &nbsp;<br/>

                @if(count($tag_rows)>0)
                    @include('v1.parts.datatable', ['headers' => $tag_headers, 'data' => $tag_rows, 'scroll' => $tagscroll, 'id' => 'nametags'])
                @else
                    @lang('messages.instructions.no_regs')
                @endif

            </div>
        </div>
    </div>

    @include('v1.parts.end_content')

@endsection

{{--
include('v1.parts.ajax_console')
--}}

@section('scripts')
    @if($scroll)
        @include('v1.parts.footer-datatable')
    @endif
    @if(count($rows) > 15 || count($reg_rows) > 15 || count($tag_rows) > 15)
        <script>
            $(document).ready(function () {
                $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                    $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
                });
                $('#datatable-fixed-header').DataTable().search('').draw();
                @if($tagscroll)
                $('#nametags').DataTable().search('').draw();
                @endif
            });
        </script>
    @endif

    <script>
            $(document).ready(function () {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.fn.editable.defaults.mode = 'popup';

                @foreach ($tkts as $t)
                $('#regCount-{{ $t->ticketID }}').editable({ type: 'text', url: '/post' });
                $('#waitCount-{{ $t->ticketID }}').editable({type: 'text'});
                @endforeach
            });
        </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.min.js"></script>
    <script>
        var ctx = document.getElementById("discPie").getContext('2d');
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
                    @foreach($discountCounts as $d)
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
                        @foreach($discountCounts as $d)
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
    @include('v1.parts.menu-fix', array('path' => '/event/create', 'tag' => '#add', 'newTxt' => trans('messages.nav.ev_rpt')))
@endsection

@section('modals')
@endsection