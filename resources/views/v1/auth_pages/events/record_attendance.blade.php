<?php
/**
 * Comment: page to replace attendee check-in tab in Event Report
 * Created: 9/17/2019
 */

$topBits = '';  // remove this if this was set in the controller
$header = '';

$expand_msg = trans('messages.subheaders.expand_min');
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @if(( Entrust::hasRole($currentOrg->orgName) &&
         ( Entrust::can('event-management') || Entrust::hasRole('Admin') )
         ) || Entrust::hasRole('Developer'))

        <h2>{{ $event->eventName }}</h2>
        <div class="col-lg-7 col-xs-12">
            @include('v1.parts.event_buttons', ['event' => $event])
        </div>

        @foreach($def_sesses as $es)
            <?php
            if (Lang::has('messages.headers.' . $es->sessionName)) {
                $header = $es->sessionName = trans('messages.headers.' . $es->sessionName);
            } else {
                $header = $es->sessionName;
            }
            $cnt = count($es->regsessions->where('hasAttended', 1));
            if ($cnt > 0) {
                $xx = trans_choice('messages.headers.checkins', $cnt);
                $header .= " ($cnt $xx)";
            }
            if (Entrust::hasRole('Admin') || Entrust::hasRole('Developer')) {
                $header .= " <span class='red'>(sessionID: $es->sessionID)</span>";
            }
            ?>
            @if($es->sessionName != 'def_sess')
                @if($es->sessionID == 646)
                    {{-- dd($event->registrants(646))  --}}
                @endif

                @include('v1.parts.start_content', ['header' => $header, 'subheader' => $expand_msg,
                                                    'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0, 'min' => null])
                <div class="col-xs-12">
                    <p>
                        @lang('messages.instructions.one-at-time')
                    </p>
                </div>
                <div class="col-sm-3">
                    <button data-toggle="modal" class="btn btn-md btn-success" data-target="#dynamic_modal"
                            data-target-id="{{ $es->sessionID }}">
                        <i class="far fa-fw fa-check-square"></i>
                        @lang('messages.headers.check_tab')
                    </button>
                </div>

                {{-- Access for the Mail_Survey activity button ONLY for Developer role --}}
                @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))

                    @if($cnt > 0)
                        <div class="col-sm-3">
                            @include('v1.parts.url_button', [
                                'url' => env('APP_URL')."/mail_surveys/$event->eventID/$es->sessionID",
                                'color' => 'btn-warning', 'tooltip' => trans('messages.tooltips.survey'),
                                'confirm' => trans('messages.messages.survey_confirm'),
                                'text' => trans('messages.buttons.mail_surveys')
                            ])
                            &nbsp; <br/>
                        </div>
                    @endif
                @endif
                <div class="col-sm-3">
                    {{-- Access for the Download_List activity button ONLY for Admin or Developer roles --}}
                    @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                        @if($event->checkin_period() && $cnt > 0)
                            @include('v1.parts.url_button', [
                                'url' => env('APP_URL')."/excel/pdudata/$event->eventID/$es->sessionID",
                                'color' => 'btn-primary', 'tooltip' => trans('messages.buttons.down_PDU_list'),
                                'text' => trans('messages.buttons.down_PDU_list')
                            ])
                        @endif
                    @endif
                </div>
                @include('v1.parts.end_content')
            @endif
        @endforeach

        @if($event->hasTracks > 0)
            @if($event->confDays != 0)
                @for($i=1;$i<=$event->confDays;$i++)
                    <?php
                    try {
                        $tmp = \App\EventSession::where([
                            ['eventID', $event->eventID],
                            ['confDay', $i]
                        ])->first();
                        $t_label = \App\Ticket::find($tmp->ticketID);
                    } catch (Exception $e) {
                        $message = trans('messages.errors.unexpected');
                        request()->session()->flash('alert-danger', $message);
                        return view('v1.public_pages.error_display', compact('message'));
                    }
                    ?>
                    <div class="col-xs-12" style="background-color: rgba(52, 73, 94, 0.94); color: yellow">
                        <b>@lang('messages.headers.day') {{ $i }}: {{ $t_label->ticketLabel }} </b>
                    </div>
                    @for($x=1;$x<=5;$x++)
                        <?php
                        try {
                            $tmp = \App\EventSession::where([
                                ['eventID', $event->eventID],
                                ['confDay', $i],
                                ['order', $x]
                            ])->first();
                        } catch (Exception $e) {
                            $message = trans('messages.errors.unexpected');
                            request()->session()->flash('alert-danger', $message . "<br />$e");
                            return view('v1.public_pages.error_display', compact('message'));
                        }
                        ?>
                        @if(null !== $tmp)
                            @foreach($tracks as $track)
                                <?php
                                try {
                                    $es = \App\EventSession::where([
                                        ['eventID', $event->eventID],
                                        ['confDay', $i],
                                        ['order', $x],
                                        ['trackID', $track->trackID]
                                    ])->first();
                                } catch (Exception $e) {
                                    $message = trans('messages.errors.unexpected');
                                    request()->session()->flash('alert-danger', $message . "<br />$e");
                                    return view('v1.public_pages.error_display', compact('message'));
                                }
                                ?>
                                @if(null !== $es)
                                    <?php
                                    $header = $es->sessionName;
                                    $cnt = count($es->regsessions->where('hasAttended', 1));
                                    if ($cnt > 0) {
                                        $xx = trans_choice('messages.headers.checkins', $cnt);
                                        $header .= " ($cnt $xx)";
                                    }
                                    if (Entrust::hasRole('Admin') || Entrust::hasRole('Developer')) {
                                        $header .= " <span class='red'>(sessionID: $es->sessionID)</span>";
                                    }
                                    ?>
                                    @include('v1.parts.start_content', ['header' => $header, 'subheader' => "$track->trackName: " . $es->start->format('g:i A'),
                                                                        'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0, 'min' => null])
                                    <div>
                                        <p>
                                            @lang('messages.instructions.one-at-time')
                                        </p>
                                    </div>

                                    <div class="col-sm-3">
                                        <button data-toggle="modal" class="btn btn-md btn-success"
                                                data-target="#dynamic_modal"
                                                data-target-id="{{ $es->sessionID }}">
                                            <i class="far fa-fw fa-check-square"></i>
                                            @lang('messages.headers.check_tab')
                                        </button>
                                    </div>

                                    {{-- Access for the Mail_Survey activity button ONLY for Developer role --}}
                                    @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))

                                        @if(count($es->regsessions) > 0)
                                            <div class="col-sm-3">
                                                @include('v1.parts.url_button', [
                                                    'url' => env('APP_URL')."/mail_surveys/$event->eventID/$es->sessionID",
                                                    'color' => 'btn-warning', 'tooltip' => trans('messages.tooltips.survey'),
                                                    'confirm' => trans('messages.messages.survey_confirm'),
                                                    'text' => trans('messages.buttons.mail_surveys')
                                                ])
                                                &nbsp; <br/>
                                            </div>
                                        @endif
                                    @endif
                                    <div class="col-sm-3">
                                        {{-- Access for the Download_List activity button ONLY for Admin or Developer roles --}}
                                        @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                                            @if($event->checkin_period() && count($es->regsessions) > 0)
                                                @include('v1.parts.url_button', [
                                                    'url' => env('APP_URL')."/excel/pdudata/$event->eventID/$es->sessionID",
                                                    'color' => 'btn-primary', 'tooltip' => trans('messages.buttons.down_PDU_list'),
                                                    'text' => trans('messages.buttons.down_PDU_list')
                                                ])
                                            @endif
                                        @endif
                                    </div>

                                    @include('v1.parts.end_content')
                                @endif

                            @endforeach
                        @endif
                    @endfor
                @endfor
            @endif
        @endif

    @endif

@endsection

@section('scripts')
    @include('v1.parts.menu-fix', array('path' => '/event/create', 'tag' => '#add', 'newTxt' => trans('messages.nav.ev_rpt')))
    <script>
        $('.collapsed').css('height', 'auto');
        $('.collapsed').find('.x_content').css('display', 'none');
    </script>
    <script>
        $(".select_all").change(function () {
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
@endsection

@section('modals')
    @include('v1.modals.dynamic', ['header' => trans('messages.headers.check_tab'), 'url' => 'show_record_attendance'])
@endsection

@section('footer')
@endsection
