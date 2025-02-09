@php
    /**
     * Comment: Event Display with Tabs to see Session Information
     * Created: 5/5/2017; Updated 10/13/2024 for Laravel 9.x - AWS S3 V3
     * Modified: 6/10/2019 to be more mobile-friendly
     *
     * @var Event $event
     * @var Ticket $bundles
     * @var Ticket $tickets
     * @var Track $tracks
     * @var Array $orgLogoPath
     */

    use App\Models\EventSession;
    use App\Models\Ticket;
//    use GrahamCampbell\Flysystem\Facades\Flysystem;
    use League\Flysystem\Filesystem;
    use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
    use Aws\S3\S3Client;
    use Illuminate\Support\Arr;

    // ['orgID', $event->orgID],     // orgID does not have to be specified because the catID selection in add_event
    // allows for selection from this orgID or the default orgID of 1
    $category = DB::table('event-category')
    ->where([
        ['catID', $event->catID]
    ])->select('catTXT')->first();

    $locale = App::getLocale();

    // -----------------
    // Early Bird-ism
    // 1. Get today's date
    // 2. Compare to early bird dates per ticket
    // 3. Calculate new price and display

    $today = Carbon\Carbon::now();
    if ($event->hasTracks) {
        if ($event->isSymmetric) {
            $columns = ($event->hasTracks * 2) + 1;
            $width = (integer)85 / $event->hasTracks;
            $mw = (integer)90 / $event->hasTracks;
        } else {
            $columns = $event->hasTracks * 3;
            $width = (integer)80 / $event->hasTracks;
            $mw = (integer)85 / $event->hasTracks;
        }
    }

    // This is a rejiggering of the order by which tracks/sessions could be displayed.
    // I don't think I ever made use of it.

    if ($event->hasTracks && !$event->isSymmetric) {
        $mda = array('days' => $event->confDays, 'sym' => $event->isSymmetric, 'tracks' => count($tracks));
        for ($d = 1; $d <= $event->confDays; $d++) {
            $t = 0;
            ${'d' . $d} = array();
            foreach ($tracks as $track) {
                $t++;
                ${'t' . $t} = array();
                for ($x = 1; $x <= 5; $x++) {
                    $es = EventSession::where([
                        ['trackID', '=', $track->trackID],
                        ['order', '=', $x],
                        ['confDay', '=', $d]
                    ])->first();
                    if ($es !== null) {
                        ${'t' . $t} = Arr::add(${'t' . $t}, $x, $es);
                    }
                }
                ${'d' . $d} = Arr::add(${'d' . $d}, 't' . $t, ${'t' . $t});
            }
            $mda = Arr::add($mda, 'd' . $d, ${'d' . $d});
        }
    }

/*
    $client = Storage::disk('s3_media')->getClient();
    $client = new S3Client([
        'credentials' => [
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET')
        ],
        'region' => env('AWS_REGION'),
        'version' => 'latest',
    ]);
    $adapter = new AwsS3V3Adapter($client, env('AWS_BUCKET3'));
    $s3fs = new Filesystem($adapter);
*/
    $logoFilePath = $orgLogoPath->orgPath . "/" . $orgLogoPath->orgLogo;
    try {
        if(Storage::disk('s3_media')->exists($logoFilePath)){
            $logo = Storage::disk('s3_media')->url($logoFilePath);
        }
    } catch(Exception $e) {
        $logo = '';
    }

    $mbr_price = trans('messages.instructions.mbr_price');
@endphp
@extends('v1.layouts.no-auth')

@if($event->ok_to_display() || $override)
    @section('content')
        @include('v1.parts.not-table_header')
        <style>
            .popover {
                max-width: 50%;
            }
        </style>
        @include('v1.parts.start_content', ['header' => $event->eventName, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

        @include('v1.parts.start_content', ['header' => trans('messages.fields.org'), 'subheader' => '', 'w1' => '0', 'w2' => '12',
                                            'class' => 'hidden-lg hidden-md hidden-sm ', 'r1' => 0, 'r2' => 0, 'r3' => 0])
        <p>
            <img alt="{!! $currentOrg->orgName !!} {{ trans('messages.headers.logo') }}" src="{{ $logo }}"/>
        </p>
        {{ $event->contactOrg }}
        <br/>
        {{ $event->contactEmail }}
        <br/>
        <br/>
        @include('v1.parts.end_content')

        @include('v1.parts.start_content', ['header' => trans('messages.fields.detail'), 'subheader' => '', 'w1' => '9', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

        {!! Form::open(['url' => env('APP_URL').'/regstep1/'.$event->eventID, 'method' => 'post', 'id' => 'start_registration']) !!}
        <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
            <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
                {!! $event->eventDescription !!}
            </div>
            <div class="form-group has-feedback col-md-12 col-sm-12 col-xs-12">
                {{ trans('messages.fields.category') }}: {{ $category->catTXT }}
            </div>
            @if($event->valid_earlyBird())
                <div class="col-md-12 col-sm-12 col-xs-12" style="display:flex;">
                    <div class="col-md-2 col-sm-2 col-xs-2">
                        <img src="{{ env('APP_URL') }}/images/earlybird.jpg" style="float:right; width:75px;"/>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-6" style="margin-top: auto; word-break: break-all;">
                        <h2>
                            @lang('messages.instructions.early_bird') {{ $event->earlyBirdDate->format('M d') }}
                        </h2>
                    </div>
                </div>
            @endif

            @if($event->hasTracks)
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <ul class="nav nav-tabs bar_tabs nav-justified hidden-xs" id="myTab" role="tablist">
                        <li class="active hidden-xs">
                            <a aria-expanded="true" data-toggle="tab" href="#tab_content1" id="ticketing-tab">
                                <b> @lang('messages.tabs.ticketing') </b>
                            </a>
                        </li>
                        <li class="hidden-xs">
                            <a aria-expanded="false" data-toggle="tab" href="#tab_content2" id="sessions-tab">
                                <b> @lang('messages.tabs.sessions') </b>
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content" id="tab-content">
                        <div aria-labelledby="ticketing-tab" class="tab-pane active" id="tab_content1">
                            <br/>
                            @else
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    @endif

                                    @if(count($bundles) + count($tickets) > 0)
                                        <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback" id="not">
                                            <table class="table table-striped jambo_table" id="datatable">
                                                <thead id="cf">
                                                <tr>
                                                    <th colspan="2" style="width: 40%">
                                                        @lang('messages.fields.ticket')
                                                    </th>
                                                    <th style="width: 20%">
                                                        @lang('messages.fields.memcost')
                                                        @include('v1.parts.tooltip', ['title' => $mbr_price, 'c' => 'text-warning'])
                                                    </th>
                                                    <th style="width: 20%">
                                                        @lang('messages.fields.noncost')
                                                    </th>
                                                    <th style="width: 20%">
                                                        @lang('messages.fields.availability')
                                                    </th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($bundles as $bundle)
                                                    <tr>
                                                        <td data-title="{{ trans('messages.fields.quantity') }}"
                                                            style="text-align: left;">
                                                            <div class="form-group">
                                                                <input data-error="{{ trans('messages.errors.numeric') }}"
                                                                       @if($bundle->soldout())
                                                                           disabled
                                                                       @endif
                                                                       id="q-{{ $bundle->ticketID }}"
                                                                       name="q-{{ $bundle->ticketID }}" pattern="[0-5]"
                                                                       required="" size="2" style="width:30px"
                                                                       type="number" min="1"
                                                                       max="{!! $bundle->available_for_purchase() !!}"
                                                                       value="0">
                                                                <div class="help-block with-errors">
                                                                </div>
                                                                </input>
                                                            </div>
                                                        </td>
                                                        <td data-title="{{ trans('messages.fields.ticket') }}">
                                                            {{ $bundle->ticketLabel }}
                                                            @include('v1.parts.tooltip',
                                                            ['title' => trans('messages.tooltips.bundles'),
                                                             'c' => 'text-danger'])

                                                            <ul>
                                                                @foreach($bundle->bundle_members() as $tkt)
                                                                    <li>
                                                                        @if($tkt->soldout())
                                                                            <b style="color:red;">
                                                                                @lang('messages.labels.soldout'):</b>
                                                                        @else
                                                                        @endif
                                                                        {{ $tkt->ticketLabel }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                            <b class="red">
                                                                @choice('messages.instructions.sold_out2', $bundle->available_for_purchase())
                                                            </b>
                                                        </td>
                                                        <td data-title="{{ trans('messages.fields.memprice') }}">
                                                            @lang('messages.symbols.cur')
                                                            @if($bundle->valid_earlyBird())
                                                                <strike style="color:red;">
                                                                    {{ number_format($bundle->memberBasePrice, 2, '.', ',') }}
                                                                </strike>
                                                                <br/>
                                                                @lang('messages.symbols.cur')
                                                                {{ number_format($bundle->memberBasePrice - ( $bundle->memberBasePrice * $bundle->earlyBirdPercent / 100), 2, '.', ',') }}
                                                            @else
                                                                {{ number_format($bundle->memberBasePrice, 2, '.', ',') }}
                                                            @endif
                                                        </td>
                                                        <td data-title="{{ trans('messages.fields.nonprice') }}">
                                                            @lang('messages.symbols.cur')
                                                            @if($bundle->valid_earlyBird())
                                                                <strike style="color:red;">
                                                                    {{ number_format($bundle->nonmbrBasePrice, 2, '.', ',') }}
                                                                </strike>
                                                                <br/>
                                                                @lang('messages.symbols.cur')
                                                                {{ number_format($bundle->nonmbrBasePrice - ( $bundle->nonmbrBasePrice * $bundle->earlyBirdPercent / 100), 2, '.', ',') }}
                                                            @else
                                                                {{ number_format($bundle->nonmbrBasePrice, 2, '.', ',') }}
                                                            @endif
                                                        </td>
                                                        <td data-title="{{ trans('messages.fields.availability') }}">
                                                            {{ $bundle->availabilityEndDate->format('n/j/Y g:i A') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                @foreach($tickets as $ticket)
                                                    <tr>
                                                        <td data-title="{{ trans('messages.fields.quantity') }}"
                                                            style="text-align: left; width:15px;">
                                                            <div class="form-group">
                                                                <input
                                                                        @if(!$ticket->available_for_purchase())
                                                                            disabled @endif
                                                                        data-error="{{ trans('messages.errors.numeric') }}"
                                                                        id="q-{{ $ticket->ticketID }}"
                                                                        name="q-{{ $ticket->ticketID }}" pattern="[0-5]"
                                                                        required="" size="2" style="width:30px"
                                                                        type="number" min="1"
                                                                        max="{!! $ticket->available_for_purchase() !!}"
                                                                        value="0">
                                                                <div class="help-block with-errors">
                                                                </div>
                                                                </input>
                                                            </div>
                                                        </td>
                                                        <td data-title="{{ trans('messages.fields.ticket') }}">
                                                            {{ $ticket->ticketLabel }}
                                                            <br/>
                                                            <b class="red">
                                                                @choice('messages.instructions.sold_out2', $ticket->available_for_purchase())
                                                            </b>
                                                        </td>
                                                        <td data-title="{{ trans('messages.fields.memprice') }}">
                                                            @lang('messages.symbols.cur')
                                                            @if($ticket->valid_earlyBird())
                                                                <strike style="color:red;">
                                                                    {{ number_format($ticket->memberBasePrice, 2, '.', ',') }}
                                                                </strike>
                                                                <br/>
                                                                @lang('messages.symbols.cur')
                                                                {{ number_format($ticket->memberBasePrice - ($ticket->memberBasePrice * $ticket->earlyBirdPercent / 100), 2, '.', ',') }}
                                                            @else
                                                                {{ number_format($ticket->memberBasePrice, 2, '.', ',') }}
                                                            @endif
                                                        </td>
                                                        <td data-title="{{ trans('messages.fields.nonprice') }}">
                                                            @lang('messages.symbols.cur')
                                                            @if($ticket->valid_earlyBird())
                                                                <strike style="color:red;">
                                                                    {{ number_format($ticket->nonmbrBasePrice, 2, '.', ',') }}
                                                                </strike>
                                                                <br/>
                                                                @lang('messages.symbols.cur')
                                                                {{ number_format($ticket->nonmbrBasePrice - ( $ticket->nonmbrBasePrice * $ticket->earlyBirdPercent / 100), 2, '.', ',') }}
                                                            @else
                                                                {{ number_format($ticket->nonmbrBasePrice, 2, '.', ',') }}
                                                            @endif
                                                        </td>
                                                        <td data-title="{{ trans('messages.fields.availability') }}">
                                                            {{ $ticket->availabilityEndDate->format('n/j/Y g:i A') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-12 col-sm-12 col-xs-12" id="status_msg">
                                        </div>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <div class="col-md-9 col-sm-9 col-xs-9 form-group"
                                                 style="text-align: right">
                                                <input class="form-control input-sm" id="discount_code"
                                                       name="discount_code"
                                                       placeholder="  {{ trans('messages.codes.empty') }}" type="text">
                                            </div>
                                            <div class="col-md-3 col-sm-3 col-xs-3">
                                                <a class="btn btn-sm btn-primary" id="btn-validate">
                                                    @lang('messages.fields.validate')
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6 col-xs-12"
                                             style="text-align: left; vertical-align: top;">
                                            <img alt="Visa Logo" src="{{ env('APP_URL') }}/images/visa.png"/>
                                            <img alt="MasterCard Logo"
                                                 src="{{ env('APP_URL') }}/images/mastercard.png"/>
                                            <button class="btn btn-success btn-sm" id="purchase" style="height: 32px;"
                                                    type="submit">
                                                <b> @lang('messages.buttons.buy') </b>
                                            </button>
                                        </div>
                                        <br/>
                                        @if(count($bundles)>0)
                                            <div class="col-xs-12">
                                                <sup style="color: red"> ** </sup> @lang('messages.tooltips.bundles')
                                            </div>
                                        @endif

                                    @else
                                        <b class="red"> {{ trans('messages.headers.no_tickets') }} </b>
                                    @endif
                                </div>
                                @if($event->hasTracks)
                                    <div aria-labelledby="sessions-tab" class="tab-pane fade hidden-xs"
                                         id="tab_content2">
                                        <br/>
                                        @if($event->confDays != 0)
                                            <div class="col-sm-12 col-md-12 col-xs-12" id="not">
                                                <p>
                                                    @lang('messages.instructions.tracks', ['n' => count($tracks)])
                                                </p>
                                                <table class="table table-bordered table-striped table-condensed table-responsive">
                                                    <thead class="cf">
                                                    <tr>
                                                        @foreach($tracks as $track)

                                                            @if($tracks->first() == $track || !$event->isSymmetric)
                                                                <th style="text-align:left;">
                                                                    @lang('messages.fields.s_times')
                                                                </th>
                                                            @endif
                                                            <th colspan="2" style="text-align:center;">
                                                                {{ $track->trackName }}
                                                            </th>
                                                        @endforeach
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @for($i=1;$i<=$event->confDays;$i++)
                                                        <tr>
                                                            @php
                                                                $z = EventSession::where([
                                                                    ['confDay', '=', $i],
                                                                    ['eventID', '=', $event->eventID]
                                                                ])->first();
                                                                $y = Ticket::find($z->ticketID);
                                                            @endphp
                                                            <th colspan="{{ $columns }}"
                                                                style="text-align:center; color: yellow; background-color: #2a3f54;">
                                                                {{ trans('messages.headers.day') }} {{ $i }}
                                                                :
                                                                {{ $y->ticketLabel  }}
                                                            </th>
                                                        </tr>
                                                        @for($x=1;$x<=5;$x++)
                                                            @php
                                                                // Check to see if there are any events for $x (this row)
                                                                $check = EventSession::where([
                                                                    ['eventID', $event->eventID],
                                                                    ['confDay', $i],
                                                                    ['order', $x]
                                                                ])->first();

                                                                // As long as there are any sessions, the row will be displayed
                                                            @endphp
                                                            @if($check !== null)
                                                                <tr>
                                                                    @foreach($tracks as $track)
                                                                        @php
                                                                            $s = EventSession::where([
                                                                                ['trackID', $track->trackID],
                                                                                ['eventID', $event->eventID],
                                                                                ['confDay', $i],
                                                                                ['order', $x]
                                                                            ])->first();

                                                                            if ($s !== null && $s->isLinked) {
                                                                                $count = EventSession::where([
                                                                                    ['trackID', $track->trackID],
                                                                                    ['eventID', $event->eventID],
                                                                                    ['confDay', $i],
                                                                                    ['isLinked', $s->isLinked]
                                                                                ])->withTrashed()->count();
                                                                            } else {
                                                                                $count = 0;
                                                                            }
                                                                        @endphp
                                                                        @if($s !== null)
                                                                            @if($tracks->first() == $track || !$event->isSymmetric)
                                                                                <td data-title="{{ trans('messages.fields.times') }}"
                                                                                    rowspan="{{ $count>0 ? $count*3 : 3 }}"
                                                                                    style="text-align:left;{{ $x%2?:'background-color:lightgray;' }}">
                                                                                    <nobr>
                                                                                        {{ $s->start->format('g:i A') }}
                                                                                    </nobr>
                                                                                    &dash;
                                                                                    <nobr>
                                                                                        {{ $s->end->format('g:i A') }}
                                                                                    </nobr>
                                                                                </td>
                                                                            @endif
                                                                            <td colspan="2"
                                                                                data-title="{{ trans('messages.fields.session') }}"
                                                                                style="text-align:left; min-width:150px; width: {{ $width }}%; max-width: {{ $mw }}%;{{ $x%2?:'background-color:lightgray;' }}">
                                                                                <b> {{ $s->sessionName }} </b>
                                                                                @if($s->sessionAbstract !== null)
                                                                                    <a class="btn btn-xs btn-primary pull-right"
                                                                                       data-content="{!! $s->sessionAbstract !!}"
                                                                                       data-html="true"
                                                                                       data-placement="left"
                                                                                       data-toggle="popover"
                                                                                       data-trigger="focus" tabindex="0"
                                                                                       title="{!! $s->sessionName !!}">
                                                                                        @lang('messages.fields.abstract')
                                                                                    </a>
                                                                                    <br/>
                                                                                @endif
                                                                            </td>
                                                                        @else

                                                                        @endif
                                                                    @endforeach
                                                                </tr>
                                                            @endif
                                                            @php
                                                                // Check to see if there are any events for $x (this row)
                                                                $check = EventSession::where([
                                                                    ['eventID', $event->
                                                                    eventID],
                                                                    ['confDay', $i],
                                                                    ['order', $x]
                                                                ])->first();

                                                                // As long as there are any sessions, the row will be displayed
                                                            @endphp
                                                            @if($check !== null)
                                                                <tr>
                                                                    @foreach($tracks as $track)
                                                                        @php
                                                                            $s = EventSession::where([
                                                                                ['trackID', $track->trackID],
                                                                                ['eventID', $event->eventID],
                                                                                ['confDay', $i],
                                                                                ['order', $x]
                                                                            ])->first();

                                                                            if ($s !== null && $s->isLinked) {
                                                                                $count = EventSession::where([
                                                                                    ['trackID', $track->trackID],
                                                                                    ['eventID', $event->eventID],
                                                                                    ['confDay', $i],
                                                                                    ['isLinked', $s->isLinked]
                                                                                ])->withTrashed()->count();
                                                                            } else {
                                                                                $count = 0;
                                                                            }
                                                                        @endphp
                                                                        @if($s !== null)
                                                                            <td colspan="2"
                                                                                data-title="{{ trans('messages.fields.speakers') }}"
                                                                                style="text-align:left;{{ $x%2?'':'background-color:lightgray;' }}">
                                                                                <b> {{ trans('messages.fields.speakers') }} </b>
                                                                                <br/>
                                                                                {{ $s->show_speakers() }}

                                                                            </td>
                                                                        @endif
                                                                    @endforeach
                                                                </tr>
                                                            @endif
                                                            @php
                                                                // Check to see if there are any events for $x (this row)
                                                                $check = EventSession::where([
                                                                    ['eventID', $event->
                                                                    eventID],
                                                                    ['confDay', $i],
                                                                    ['order', $x]
                                                                ])->first();

                                                                // As long as there are any sessions, the row will be displayed
                                                            @endphp
                                                            @if($check !== null)
                                                                <tr>
                                                                    @foreach($tracks as $track)
                                                                        @php
                                                                            $s = EventSession::where([
                                                                                ['trackID', $track->
                                                                                trackID],
                                                                                ['eventID', $event->eventID],
                                                                                ['confDay', $i],
                                                                                ['order', $x]
                                                                            ])->first();

                                                                            if ($s !== null && $s->isLinked) {
                                                                                $count = EventSession::where([
                                                                                    ['trackID', $track->trackID],
                                                                                    ['eventID', $event->eventID],
                                                                                    ['confDay', $i],
                                                                                    ['isLinked', $s->isLinked]
                                                                                ])->withTrashed()->count();
                                                                            } else {
                                                                                $count = 0;
                                                                            }
                                                                        @endphp
                                                                        @if($s !== null && $s->leadAmt+$s->stratAmt+$s->techAmt > 0 && $s->maxAttendees > 0)
                                                                            <td data-title="{{ trans('messages.fields.credit') }}"
                                                                                rowspan="{{ $count>0 ? ($count-1)*3+1 : 1 }}"
                                                                                style="text-align:left;{{ $x%2?:'background-color:lightgray;' }}">
                                                                                @if($s->leadAmt > 0)
                                                                                    <b>
                                                                                        {{ $s->leadAmt }}
                                                                                        @lang('messages.pdus.lead')
                                                                                        {{ $s->event->org->creditLabel }}{{ $s->leadAmt != 1?'s':'' }}
                                                                                    </b>
                                                                                    <br/>
                                                                                @endif
                                                                                @if($s->stratAmt > 0)
                                                                                    <b>
                                                                                        {{ $s->stratAmt }}
                                                                                        @lang('messages.pdus.strat')
                                                                                        {{ $s->event->org->creditLabel }}{{ $s->stratAmt != 1?'s':'' }}
                                                                                    </b>
                                                                                    <br/>
                                                                                @endif
                                                                                @if($s->techAmt > 0)
                                                                                    <b>
                                                                                        {{ $s->techAmt }}
                                                                                        @lang('messages.pdus.tech')
                                                                                        {{ $s->event->org->creditLabel }}{{ $s->techAmt != 1?'s':'' }}
                                                                                    </b>
                                                                                    <br/>
                                                                                @endif
                                                                            </td>
                                                                            <td data-title="{{ trans('messages.fields.limit') }}"
                                                                                rowspan="{{ $count>0 ? ($count-1)*3+1 : 1 }}"
                                                                                style="text-align:left;{{ $x%2?:'background-color:lightgray;' }}">
                                                                                <b> @lang('messages.headers.att_limit')
                                                                                    : </b>
                                                                                {{ $s->maxAttendees == 0 ? 'N/A' : $s->maxAttendees }}
                                                                            </td>
                                                                        @else

                                                                        @endif
                                                                    @endforeach
                                                                </tr>
                                                            @endif
                                                        @endfor
                                                    @endfor
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                        </div>
                    </div>
                    @endif
                </div>
                {!! Form::close() !!}
                @include('v1.parts.end_content')

                @include('v1.parts.start_content', ['header' => trans('messages.fields.d&t'), 'subheader' => '', 'w1' => '3', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
                <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback">
                    <table class="table" style="border: none;">
                        <tr style="border: none;">
                            <td style="text-align: right; border: none;">
                                <h4 class="red">
                                    @lang('messages.fields.from'):
                                </h4>
                            </td>
                            <td style="border: none;">
                                <nobr>
                                    <h4>
                                        {{ $event->eventStartDate->format('n/j/Y') }}
                                    </h4>
                                </nobr>
                                <nobr>
                                    <h4>
                                        {{ $event->eventStartDate->format('g:i A') }}
                                    </h4>
                                </nobr>
                            </td>
                        </tr>
                        <tr style="border: none;">
                            <td style="text-align: right; border: none;">
                                <h4 class="red">
                                    {{ ucwords(__('messages.headers.to')) }}:
                                </h4>
                            </td>
                            <td style="border: none;">
                                <nobr>
                                    <h4>
                                        {{ $event->eventEndDate->format('n/j/Y') }}
                                    </h4>
                                </nobr>
                                <nobr>
                                    <h4>
                                        {{ $event->eventEndDate->format('g:i A') }}
                                    </h4>
                                </nobr>
                            </td>
                        </tr>
                    </table>
                </div>
                @include('v1.parts.end_content')

                @include('v1.parts.start_content', ['header' => trans('messages.fields.loc'), 'subheader' => '', 'w1' => '3', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
                <div class="col-md-12 col-sm-12 col-xs-12">
                    @include('v1.parts.location_display', ['loc' => $event_loc, 'map' => 1, 'no_title' => 1])
                </div>
                @include('v1.parts.end_content')

                @include('v1.parts.start_content', ['header' => trans('messages.fields.org'), 'subheader' => '', 'w1' => '3',
                                                    'class' => 'hidden-xs', 'w2' => '0', 'r1' => 0, 'r2' => 0, 'r3' => 0])
                <p>
                    <img alt="{!! $currentOrg->orgName !!} {{ trans('messages.headers.logo') }}" src="{{ $logo }}"/>
                </p>
                {{ $event->contactOrg }}
                <br/>
                {{ $event->contactEmail }}
                <br/>
                <br/>
                @include('v1.parts.end_content')

                @if($event->eventInfo)
                    @include('v1.parts.start_content', ['header' => trans('messages.fields.additional'),
                             'subheader' => '', 'w1' => '9', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
                    {!! $event->eventInfo !!}
                    @if(isset($tags))
                        <p>@lang('messages.fields.tags')</p>
                    @endif
                    @include('v1.parts.end_content')
                @endif
                @include('v1.parts.end_content')
                @endsection

                @section('scripts')
                    <script>
                        $('a[data-toggle="tab"]').click(function (e) {
                            $(this).tab('show');
                        });
                        // e.target
                        //e.relatedTarget // previous active tab
                    </script>
                    <script>
                        $('#btn-validate').on('click', function (e) {
                            e.preventDefault();
                            validateCode({{ $event->eventID }});
                        });
                        $('#purchase').on('click', function (e) {
                            e.preventDefault();
                            checksum();
                        });

                        @php
                            $tkt_vars = ''; $tkt_sum = 'var sum = '; $sum = ''; $i = 0; $j = 0;
                            foreach ($bundles as $t) {
                                $i++;
                                $tkt_vars .= 'var i' . $i . ' = parseInt($("#q-' . $t->ticketID . '").val());' . "\n";
                                $tkt_sum .= "i$i + ";
                                $sum .= "i$i + ";
                            }

                            foreach ($tickets as $t) {
                                $j++;
                                $tkt_vars .= 'var j' . $j . ' = parseInt($("#q-' . $t->ticketID . '").val());' . "\n";
                                $tkt_sum .= "j$j + ";
                            }
                            $tkt_sum .= "0;";
                            $sum .= "0";
                        @endphp
                        function checksum() {
                            {!! $tkt_vars !!}
                            {!! $tkt_sum !!}

                            if(sum > 0)
                            {
                                $("#start_registration").submit();
                            }
                        else
                            {
                                alert("{{ trans('messages.instructions.quantity') }}");
                            }
                        }

                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                    </script>
                    <script>
                        function validateCode(eventID) {
                            var codeValue = $("#discount_code").val();
                            if (FieldIsEmpty(codeValue)) {
                                var message = '<span><i class="fa fa-warning fa-2x text-warning mid_align">&nbsp;</i>{{ trans('messages.codes.empty') }}</span>';
                                $('#status_msg').html(message).fadeIn(500).fadeOut(3000);

                            } else {
                                $.ajax({
                                    type: 'POST',
                                    cache: false,
                                    async: true,
                                    url: '{{ env('APP_URL') }}/discount/' + eventID,
                                    dataType: 'json',
                                    data: {
                                        event_id: eventID,
                                        discount_code: codeValue
                                    },
                                    beforeSend: function () {
                                        $('#status_msg').html('');
                                        $('#status_msg').fadeIn(0);
                                    },
                                    success: function (data) {
                                        //console.log(data);
                                        var result = eval(data);
                                        $('.status_msg').html(result.message).fadeIn(0);
                                        if (result.status == 'error') {
                                            console.log($("#discount_code" + which).val());
                                            $("#discount_code" + which).val('');
                                        }
                                    },
                                    error: function (data) {
                                        console.log(data);
                                        var result = eval(data);
                                        $('#status_msg').html(result.message).fadeIn(0);
                                    }
                                });
                            }
                        }
                    </script>
                    <script>
                        $('[data-toggle="popover"]').popover({
                            container: 'body',
                            placement: 'top'
                        });
                    </script>
    @endsection
@else
    @section('content')
        @lang('messages.instructions.inactive')
    @endsection
@endif

