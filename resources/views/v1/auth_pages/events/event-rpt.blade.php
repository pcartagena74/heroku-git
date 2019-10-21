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
$currentOrg = \App\Org::find($event->orgID);
$topBits = ''; // there should be topBits for this

$post_event = $today->gte($event->eventEndDate);


function get_survey_comments($session)
{
    $surveys = \App\RSSurvey::where('sessionID', '=', $session->sessionID);
    $ft = trans('messages.surveys.favorite');
    $faves = $surveys->whereNotNull('favoriteResponse')->selectRaw("concat('<li>', favoriteResponse, '</li>') as 'favoriteResponse'")->pluck('favoriteResponse');
    $st = trans('messages.surveys.suggestions');
    $suggest = $surveys->whereNotNull('suggestResponse')->selectRaw("concat('<li>', suggestResponse, '</li>') as 'suggestResponse'")->pluck('suggestResponse');
    $ct = trans('messages.surveys.contact');
    $contact = $surveys->whereNotNull('contactResponse')->selectRaw("concat('<li>', contactResponse, '</li>') as 'contactResponse'")->pluck('contactResponse');
    $content = view('v1.parts.session_comments', ['list' => $faves, 'title' => $ft])->render();
    $content .= view('v1.parts.session_comments', ['list' => $suggest, 'title' => $st])->render();
    $content .= view('v1.parts.session_comments', ['list' => $contact, 'title' => $ct])->render();
    return $content;
}

$rows = []; $reg_rows = []; $notreg_rows = []; $tag_rows = []; $dead_rows = []; $i = 0;
if ($event->eventEndDate->gte($today)) {
    $headers = [trans('messages.fields.ticket'), trans('messages.headers.att_limit'), trans('messages.headers.this'),
        trans('messages.headers.tot_regs'), trans('messages.headers.wait')];
    if (Entrust::hasRole('Developer') || Entrust::hasRole('Admin')) {
        foreach ($tkts as $t) {
            $rc = '<a href="#" id="regCount-' . $t->ticketID . '" data-name="regCount-' . $t->ticketID . '" data-value="' . $t->regCount .
                '" data-url="' . env('APP_URL') . '/ticket/' . $t->ticketID . '" data-pk="' . $t->ticketID . '"></a>';

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

    // This keeps the fields editable, even after the event, for a Developer
    if (Entrust::hasRole('Developer')) {
        foreach ($tkts as $t) {
            $rc = '<a href="#" id="regCount-' . $t->ticketID . '" data-name="regCount-' . $t->ticketID . '" data-value="' . $t->regCount .
                '" data-url="' . env('APP_URL') . '/ticket/' . $t->ticketID . '" data-pk="' . $t->ticketID . '"></a>';

            $wc = "<a href='#' id='waitCount-$t->ticketID' name='waitCount-$t->ticketID' data-value='$t->waitCount' data-url='" . env('APP_URL') .
                "/ticket/$t->ticketID' data-pk='$t->ticketID'></a>";

            // $t->regCount, $t->waitCount
            array_push($rows, ['<nobr>' . $t->ticketLabel . '</nobr>', $t->maxAttendees, $rc, $wc]);
        }
    } else {
        foreach ($tkts as $t) {
            array_push($rows, ['<nobr>' . $t->ticketLabel . '</nobr>', $t->maxAttendees, $t->regCount, $t->waitCount]);
        }
    }
}

$reg_headers = ['RegID', trans('messages.fields.firstName'), trans('messages.fields.lastName'), trans('messages.fields.ticket'),
    trans('messages.headers.disc_code'), trans('messages.headers.reg_date'), trans('messages.headers.cost'), trans('messages.fields.buttons')];
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

    foreach ($nametags as $r) {
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
            array_push($tag_rows, [plink($r->regID, $p->personID), $p->prefName, $p->lastName,
                $r->isFirstEvent == 1 ? trans('messages.yesno_check.yes') : trans('messages.yesno_check.no'), $p->login,
                $r->ticket->ticketLabel, $r->discountCode, $p->affiliation, $p->chapterRole, $allergies]);
        } else {
            array_push($tag_rows, [plink($r->regID, $p->personID), $p->prefName, $p->lastName,
                $r->isFirstEvent == 1 ? trans('messages.yesno_check.yes') : trans('messages.yesno_check.no'), $p->login,
                $r->ticket->ticketLabel, $r->discountCode, $p->affiliation, $p->chapterRole]);
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

    foreach ($nametags as $r) {
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
            array_push($tag_rows, [plink($r->regID, $p->personID), $p->prefName, $p->lastName,
                $r->isFirstEvent == 1 ? trans('messages.yesno_check.yes') : trans('messages.yesno_check.no'), $p->login,
                $r->ticket->ticketLabel, $r->discountCode, $p->compName, $p->title, $p->indName, $allergies]);
        } else {
            array_push($tag_rows, [plink($r->regID, $p->personID), $p->prefName, $p->lastName,
                $r->isFirstEvent == 1 ? trans('messages.yesno_check.yes') : trans('messages.yesno_check.no'), $p->login,
                $r->ticket->ticketLabel, $r->discountCode, $p->compName, $p->title, $p->indName]);
        }
    }
}

$p = null;

foreach ($regs as $r) {
    $v = View::make('v1.parts.reg_cancel_button', ['reg' => $r]); $c = $v->render();
    array_push($reg_rows, [plink($r->regID, $r->person->personID), $r->person->firstName, $r->person->lastName, $r->ticket->ticketLabel, $r->discountCode,
        $r->createDate->format('Y/m/d'), trans('messages.symbols.cur') . number_format($r->subtotal, 2, '.', ''), $c]);
}

$c = null;

if (!$post_event) {
    foreach ($deadbeats as $r) {
        $f = '';
        if ($r->subtotal > 0) {
            $v = View::make('v1.parts.deadbeat_buttons', ['regID' => $r->regID, 'rfID' => $r->rfID]);
            $f = $v->render();

            array_push($dead_rows, [plink($r->regID, $r->person->personID), $r->person->firstName, $r->person->lastName, $r->ticket->ticketLabel, $r->discountCode, $r->createDate->format('Y/m/d'),
                trans('messages.symbols.cur') . ' ' . number_format($r->subtotal, 2, '.', ''), $f]);
        }
    }
}

foreach ($notregs as $r) {
    if(!$post_event){
        $v = View::make('v1.parts.reg_cancel_button', ['reg' => $r]);
        $c = $v->render();
    } else {
        $c = null;
    }

    array_push($notreg_rows, [plink($r->regID, $r->person->personID),
        trans('messages.reg_status.' . $r->regStatus) ? trans('messages.reg_status.' . $r->regStatus) : $r->regStatus,
        $r->person->firstName, $r->person->lastName,
        $r->ticket->ticketLabel, $r->discountCode, $r->createDate->format('Y/m/d'),
        trans('messages.symbols.cur') . ' ' . number_format($r->subtotal, 2, '.', ''), $c]);
}
$c = null;

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
    $stats = '<a href="' . env('APP_URL') . '/tracks/' . $event->eventID . '">' . trans('messages.fields.ticket') . " " . trans('messages.headers.stats') . '</a>';
} elseif ($event->hasTracks) {
    $columns = $event->hasTracks * 3;
    $width = (integer)80 / $event->hasTracks;
    $mw = (integer)85 / $event->hasTracks;
    $stats = '<a href="' . env('APP_URL') . '/tracks/' . $event->eventID . '">' . trans('messages.fields.ticket') . " " . trans('messages.headers.stats') . '</a>';
} else {
    $stats = trans('messages.fields.ticket') . " " . trans('messages.headers.stats');
}

$es = $event->default_session();
$count = 0;
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('header')
    <style>
        .popover {
            max-width: 50%;
        }
    </style>
@endsection

@section('content')

    <h2>{{ $event->eventName }}</h2>
    @if(null === $format)
        <div class="col-lg-7 col-xs-12">
            @include('v1.parts.event_buttons', ['event' => $event])
        </div>
    @endif

    @include('v1.parts.start_content', ['header' => $stats, 'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    @include('v1.parts.datatable', ['headers' => $headers, 'data' => $rows, 'scroll' => 0])
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => trans('messages.fields.disc') . ' ' . trans('messages.headers.breakdown'),
                                        'subheader' => '', 'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <div class="col-md-6 col-sm-6 col-xs-6">
        <canvas id="discPie"></canvas>
    </div>
    <div id="pieLegend" class="col-md-6 col-sm-6 col-xs-6">
    </div>

    @include('v1.parts.end_content')

    <div class="col-md-12 col-sm-12 col-xs-12">
        <ul id="myTab" class="nav nav-tabs bar_tabs nav-justified" role="tablist">
            @if(null === $format)
                <li class="active"><a href="#tab_content1" id="attendees-tab" data-toggle="tab"
                                      aria-expanded="true"><b>@lang('messages.headers.reged') {{ trans_choice('messages.headers.att', 2) }}</b></a>
                </li>
                <li class="hidden-sm hidden-xs"><a href="#tab_content5" id="nametags-tab" data-toggle="tab"
                                                   aria-expanded="false"><b>@lang('messages.headers.nametags')</b></a>
                </li>
                @if(count($deadbeats) > 0)
                    <li class="hidden-sm hidden-xs"><a href="#tab_content6" id="pending-tab" data-toggle="tab"
                                                       aria-expanded="true"><b>@lang('messages.headers.doored')</b></a>
                    </li>
                @endif
                @if(count($notregs) > 0)
                    <li class="hidden-sm hidden-xs">
                        <a href="#tab_content4" id="nonreg-tab" data-toggle="tab" aria-expanded="false">
                            <b>
                                @lang('messages.headers.wait')
                                {{ strtolower(__('messages.headers.or')) }}
                                @lang('messages.headers.int_reg')
                            </b>
                        </a>
                    </li>
                @endif

                @if($event->hasTracks > 0)
                    <li class=""><a href="#tab_content3" id="sessions-tab" data-toggle="tab"
                                    aria-expanded="false"><b>@lang('messages.tabs.sessions')</b></a></li>
                @endif

                @if($event->hasTracks == 0)
                    @if($event->checkin_time() && ( Entrust::hasRole($currentOrg->orgName) &&
                            ( Entrust::hasRole('Board')|| Entrust::hasRole('Admin') || Entrust::can('event-management') ))
                        || Entrust::hasRole('Developer'))

                        <li class=""><a href="#tab_content7" id="checkin-tab" data-toggle="tab"
                                        aria-expanded="false"><b>@lang('messages.headers.check_tab')</b></a></li>
                    @endif
                @endif
            @endif
            @if(Entrust::hasRole('Board') || Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                <li class=""><a href="#tab_content2" id="finances-tab" data-toggle="tab"
                                aria-expanded="false"><b>@lang('messages.headers.det_fd')</b></a></li>
            @endif
        </ul>

        <div id="tab-content" class="tab-content">
            @if(null === $format)
                <div class="tab-pane active" id="tab_content1" aria-labelledby="attendees-tab">
                    &nbsp;<br/>

                    @if(count($reg_rows)>0 && Entrust::hasRole('Admin'))
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="col-sm-3">
                                @include('v1.parts.url_button', [
                                    'url' => env('APP_URL')."/excel/emails/".$event->eventID,
                                    'color' => 'btn-primary', 'tooltip' => trans('messages.buttons.down_emails'),
                                    'text' => trans('messages.buttons.down_emails')
                                ])
                            </div>
                        </div>

                        @include('v1.parts.datatable', ['headers' => $reg_headers, 'data' => $reg_rows, 'scroll' => $scroll])
                    @else
                        @lang('messages.instructions.no_regs')
                    @endif

                </div>
                <div class="tab-pane fade" id="tab_content4" aria-labelledby="nonreg-tab">
                    &nbsp;<br/>

                    <div class="hidden-lg hidden-md hidden-sm col-xs-12"><p>&nbsp;</p></div>

                    @if(count($notreg_rows)>0)
                        @include('v1.parts.datatable', ['headers' => $notreg_headers, 'data' => $notreg_rows, 'scroll' => $notscroll])
                    @else
                        @lang('messages.instructions.no_waits')
                    @endif

                </div>
                <div class="tab-pane fade" id="tab_content6" aria-labelledby="pending-tab">
                    &nbsp;<br/>

                    <div class="hidden-lg hidden-md hidden-sm col-xs-12"><p>&nbsp;</p></div>

                    @if(count($dead_rows)>0)
                        @include('v1.parts.datatable', ['headers' => $dead_headers, 'data' => $dead_rows, 'scroll' => $notscroll])
                    @else
                        @lang('messages.instructions.no_deadbeats')
                    @endif

                </div>

                @if($event->hasTracks)
                    <div class="tab-pane fade" id="tab_content3" aria-labelledby="sessions-tab">
                        <br/>


                        <table class="table table-bordered jambo_table table-striped">
                            <thead>
                            <tr>
                                <th colspan="{{ $columns }}" style="text-align: left;">
                                    @lang('messages.fields.sessions')
                                </th>
                            </tr>
                            </thead>
                            @if($post_event)
                                @foreach($event->default_sessions() as $session)
                                    <tr>
                                        <td colspan="{{ $columns }}" style="text-align: left;">
                                            <b>{!! $session->sessionName !!}:</b>
                                            @include('v1.parts.session_stats', ['es' => $session->sessionID])
                                            @include('v1.parts.popup_content_button', ['color' => 'btn-primary',
                                                     'title' => trans('messages.surveys.popup'),
                                                     'content' => get_survey_comments($session), 'placement' => 'right',
                                                     'button_text' => trans('messages.surveys.popup')])
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
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

                                                if ($s !== null && $s->isLinked) {
                                                    $count = EventSession::where([
                                                        ['trackID', $track->trackID],
                                                        ['eventID', $event->eventID],
                                                        ['confDay', $j],
                                                        ['isLinked', $s->isLinked]
                                                    ])->withTrashed()->count();
                                                } else {
                                                    $count = 0;
                                                }
                                                if($track == $tracks->first()){
                                                    $placement = 'right';
                                                } elseif ($track == $tracks->last()) {
                                                    $placement = 'left';
                                                } else {
                                                    $placement = 'top';
                                                }
                                                ?>
                                                @if($s !== null)
                                                    @if($tracks->first() == $track || !$event->isSymmetric)
                                                        <td rowspan="{{ $count>0 ? $count+1 : 1 }}"
                                                            style="text-align:left;">
                                                            <nobr> {{ $s->start->format('g:i A') }} </nobr>
                                                            &dash;
                                                            <nobr> {{ $s->end->format('g:i A') }} </nobr>
                                                        </td>
                                                    @endif
                                                    <td colspan="2" rowspan="{{ $count>0 ? $count+1 : 1 }}"
                                                        style="text-align:left; min-width:150px;
                                                                width: {{ $width }}%; max-width: {{ $mw }}%;">
                                                        @if($post_event)
                                                            <b>{!! $s->sessionName !!}:</b>
                                                            @include('v1.parts.session_stats', ['es' => $s->sessionID])
                                                            @include('v1.parts.popup_content_button', ['color' => 'btn-primary',
                                                                     'title' => trans('messages.surveys.popup'),
                                                                     'content' => get_survey_comments($s),
                                                                     'placement' => $placement,
                                                                     'button_text' => trans('messages.surveys.popup')])
                                                        @else
                                                            <?php
                                                            // Find the counts of people for $s->sessionID broken out by discountCode in 'event-registration'.regID
                                                            $sTotal = 0;
                                                            $sRegs = RegSession::join('event-registration as er', 'er.regID', '=', 'reg-session.regID')
                                                                ->where([
                                                                    ['sessionID', $s->sessionID],
                                                                    ['er.eventID', $event->eventID]
                                                                ])->select(DB::raw('er.discountCode, count(*) as cnt'))
                                                                ->groupBy('er.discountCode')->get();
                                                            ?>
                                                            <ul>
                                                                @foreach($sRegs as $sr)
                                                                    <li>{{ $sr->discountCode ?? 'N/A' }}
                                                                        : {{ $sr->cnt }}</li>
                                                                    <?php $sTotal += $sr->cnt; ?>
                                                                @endforeach
                                                                <li><b>@lang('messages.fields.total'): {{ $sTotal }}</b>
                                                                </li>
                                                            </ul>
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

                    <div class="hidden-lg hidden-md hidden-sm col-xs-12"><p>&nbsp;</p></div>

                    @if(count($tag_rows)>0)
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="col-sm-3">
                                @include('v1.parts.url_button', [
                                    'url' => env('APP_URL')."/excel/nametags/".$event->eventID,
                                    'color' => 'btn-primary', 'tooltip' => trans('messages.buttons.down_name_tags'),
                                    'text' => trans('messages.buttons.down_name_tags')
                                ])
                            </div>
                        </div>
                        @include('v1.parts.datatable', ['headers' => $tag_headers, 'data' => $tag_rows, 'scroll' => $tagscroll, 'id' => 'nametags'])
                    @else
                        @lang('messages.instructions.no_regs')
                    @endif

                </div>
                <div class="tab-pane fade" id="tab_content7" aria-labelledby="checkin-tab">
                    &nbsp;<br/>

                    @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                        @if(count($event->main_reg_sessions()) > 0)
                            <div class="col-sm-3 col-xs-offset-2">
                                @include('v1.parts.url_button', [
                                    'url' => env('APP_URL')."/mail_surveys/".$event->eventID,
                                    'color' => 'btn-warning', 'tooltip' => trans('messages.tooltips.survey'),
                                    'confirm' => trans('messages.messages.survey_confirm'),
                                    'text' => trans('messages.buttons.mail_surveys')
                                ])
                                &nbsp; <br/>
                            </div>
                        @endif
                    @endif
                    <div class="col-sm-3">
                        @if($event->checkin_time() && count($event->main_reg_sessions()) > 0)
                            @include('v1.parts.url_button', [
                                'url' => env('APP_URL')."/excel/pdudata/".$event->eventID,
                                'color' => 'btn-success', 'tooltip' => trans('messages.buttons.down_PDU_list'),
                                'text' => trans('messages.buttons.down_PDU_list')
                            ])
                        @endif
                    </div>
                    @if(count($nametags)>0 && $event->hasTracks == 0 && null !== $es)
                        {!! Form::open(array('url' => '/event_checkin/'.$event->eventID, 'method' => 'post')) !!}
                        {!! Form::hidden('sessionID', $es->sessionID) !!}
                        {!! Form::hidden('eventID', $event->eventID) !!}
                        <div class="col-xs-12">
                            <div class="col-xs-2" style="text-align: right;">
                                @include('v1.parts.tooltip', ['title' => trans('messages.tooltips.select_all')])
                                {!! Form::checkbox('', 1, 0, array('id' => 'select_all')) !!}
                            </div>
                            <div class="col-xs-2">
                                <b>@lang('messages.fields.pmi_id')</b>
                            </div>
                            <div class="col-xs-8">
                                <b>{{ trans_choice('messages.headers.att', 1) }}</b>
                            </div>
                        </div>
                        @foreach($nametags as $row)
                            <div class="col-xs-12">
                                <div class="col-xs-2" style="text-align: right;">
                                    <?php
                                    $checked = '';
                                    if (null !== $row->regsessions && count($row->regsessions) > 0) {
                                        if ($row->is_session_attended($es->sessionID)) {
                                            $checked = 'checked';
                                        }
                                    }
                                    ?>
                                    {!! Form::checkbox('p-'.$row->person->personID.'-'.$row->regID, 1, 0, array('class' => 'allcheckbox', $checked => $checked)) !!}
                                </div>
                                <div class="col-xs-2">
                                    {{ $row->person->orgperson->OrgStat1 ?? 'N/A' }}
                                </div>
                                <div class="col-xs-8">
                                    {{ $row->person->prefName }} {{ $row->person->lastName }}
                                </div>
                            </div>
                        @endforeach
                        <div class="col-xs-10 col-xs-offset-2 form-group">
                            {!! Form::submit(trans('messages.buttons.chk_att'), ["class" => "btn btn-success btn-sm", 'name' => 'chk_att']) !!}
                            {!! Form::submit(trans('messages.buttons.chk_walk'), ["class" => "btn btn-primary btn-sm", 'name' => 'chk_walk']) !!}
                        </div>
                        {!! Form::close() !!}

                    @else
                        @lang('messages.instructions.no_regs')
                    @endif

                </div>
            @endif
            <div class="tab-pane {{ $format == 'fin' ? 'active' : 'fade' }}" id="tab_content2"
                 aria-labelledby="finances-tab">
                &nbsp;<br/>
                @include('v1.parts.datatable', ['headers' => $disc_headers, 'data' => $disc_rows, 'scroll' => 0])
            </div>
        </div>
    </div>


@endsection

{{--
    @include('v1.parts.end_content')
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
        $('[data-toggle="popover"]').popover({
            container: 'body',
        });
    </script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.fn.editable.defaults.mode = 'popup';

            var url = document.location.toString();
            if (url.match('#')) {
                $('.nav-tabs a[href="#' + url.split('#')[1] + '"]').tab('show');
            }

            @foreach ($tkts as $t)
            $('#regCount-{{ $t->ticketID }}').editable({type: 'text', url: '/post'});
            $('#waitCount-{{ $t->ticketID }}').editable({type: 'text'});
            @endforeach
        });
    </script>

    <script>
        //redirection to a specific tab
        $(document).ready(function () {
            $('#myTab a[href="#{{ old('tab') }}"]').tab('show')
        });
    </script>
    <script>
        $("#select_all").change(function () {
            $(".allcheckbox").prop("checked", $(this).prop("checked"))
        });
        $(".allcheckbox").change(function () {
            if ($(this).prop("checked") == false) {
                $("#select-all").prop("checked", false)
            }
            if ($(".allcheckbox:checked").length == $(".allcheckbox").length) {
                $("#select-all").prop("checked", true)
            }
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
                            @if($d->discountCode == '' || $d->discountCode == ' ')
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