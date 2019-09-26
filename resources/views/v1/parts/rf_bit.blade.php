<?php
/**
 * Comment: This is a reusable blade file that shows
 *          registrations based on passed reg-finance collection
 *
 * @param:
 *      $header: for display
 *      $rf_array: the collection of reg-finance records
 *
 * Created: 8/25/2017
 */

use App\RegSession;
use App\Registration;
use App\Person;
use App\Ticket;
use App\EventSession;
use App\Event;
use App\Org;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;

$tcount = 0;
$today = \Carbon\Carbon::now();

$client = new S3Client([
    'credentials' => [
        'key' => env('AWS_KEY'),
        'secret' => env('AWS_SECRET')
    ],
    'region' => env('AWS_REGION'),
    'version' => 'latest',
]);

$adapter = new AwsS3Adapter($client, env('AWS_BUCKET2'));
$s3fs = new Filesystem($adapter);
?>

@include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
<div class="col-md-12 col-sm-12 col-xs-12">

    @foreach($rf_array as $rf)
        <?php
        $receipt_filename = $rf->eventID . "/" . $rf->confirmation . ".pdf";
        $receipt_url = $s3fs->getAdapter()->getClient()->getObjectUrl(env('AWS_BUCKET2'), $receipt_filename);

        $file_headers = @get_headers($receipt_url);
        if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
            $receipt_exists = false;
        } else {
            $receipt_exists = true;
        }

        $header = $rf->event->eventName . " <small>" . trans_choice('messages.headers.seats', $rf->seats) . ": " . $rf->seats . "</small>";
        ?>
        {{-- @if($rf->event->eventEndDate->gte($today)) --}}
        @include('v1.parts.start_min_content', ['header' => $header,
                 'subheader' => $rf->event->eventStartDate->format('n/j/Y'),
                 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
        <div class="col-md-12 col-sm-12 col-xs-12">
            @foreach($rf->registrations as $reg)
                <?php
                $person = Person::find($reg->personID);
                $event = Event::find($reg->eventID);
                $org = Org::find($event->orgID);
                $regSessions = RegSession::where([
                    ['regID', '=', $reg->regID],
                    ['eventID', '=', $event->eventID]
                ])->get();
                $mem_or_not = Lang::has('messages.fields.' . $reg->membership) ? trans('messages.fields.' . $reg->membership) : $reg->membership;
                //if($reg->regID == 12597) { dd($event->eventStartDate); }
                ?>
                @include('v1.parts.start_min_content', ['header' => $mem_or_not . " " . trans('messages.fields.ticket') . " (" .  $person->showFullName() .
                "): " . $reg->ticket->ticketLabel . " (" . $reg->regID . '-' . $reg->ticket->ticketID . ")", 'subheader' => trans('messages.symbols.cur').
                ' ' . number_format($reg->subtotal, 2), 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                @if($rf->cost > 0) {{-- There is a fee for event --}}

                    @if($rf->pmtRecd == 1 && $today->lte($event->eventStartDate->subDays($org->refundDays)) && !$event->isNonRefundable)
                        {{-- Payment received and able to display a refund button --}}

                        {!! Form::open(['method'  => 'delete', 'data-toggle' => 'validator',
                                        'route' => ['cancel_registration', $reg->regID, $rf->regID] ]) !!}
                        <button type="submit" class="btn btn-danger btn-sm">
                            @lang('messages.buttons.reg_ref')
                        </button>
                        {!! Form::close() !!}
                    @endif

                    @if($rf->pmtRecd == 1)
                        {{-- Payment received so receipt buttons are appropriate --}}
                        <a target="_new" href="{!! env('APP_URL') !!}/show_receipt/{{ $rf->regID }}"
                           class="btn btn-success btn-sm">@lang('messages.buttons.rec_disp')</a>

                        @if($receipt_exists)
                            <a target="_new" href="{{ $receipt_url }}"
                               class="btn btn-primary btn-sm">@lang('messages.buttons.rec_down')</a>
                        @else
                            <a target="_new" href="{{ env('APP_URL'). "/recreate_receipt/".$rf->regID }}"
                               class="btn btn-primary btn-sm">@lang('messages.buttons.rec_down')</a>
                        @endif
                        <br/>
                    @else

                        {{-- Payment NOT received so pay balance button should be displayed if it's the
                         only/first seat --}}

                        @if($rf->seats == 1 || $reg == $rf->registrations->first())
                            <a href="{!! env('APP_URL') !!}/confirm_registration/{{ $rf->regID }}"
                               class="btn btn-primary btn-sm">@lang('messages.buttons.pay_bal')</a>
                        @endif

                        {{-- Cancel button is always OK to show --}}
                        {!! Form::open(['method'  => 'delete', 'data-toggle' => 'validator',
                            'route' => ['cancel_registration', $reg->regID, $rf->regID] ]) !!}
                        <button type="submit" class="btn btn-danger btn-sm">
                            @lang('messages.buttons.reg_can')
                        </button>
                        {!! Form::close() !!}
                    @endif

                @else               {{-- There is no fee for event --}}

                    @if($rf->pmtRecd == 1)
                        {{-- No-fee payment received (here meaning completed transaction) so cancelation is OK whenever --}}

                        {!! Form::open(['method'  => 'delete', 'data-toggle' => 'validator',
                                        'route' => ['cancel_registration', $reg->regID, $rf->regID] ]) !!}
                        <button type="submit" class="btn btn-danger btn-sm">
                            @lang('messages.buttons.reg_can')
                        </button>
                        {!! Form::close() !!}

                        {{-- Payment received (here meaning completed transaction) so receipt buttons are appropriate --}}
                        <a target="_new" href="{!! env('APP_URL') !!}/show_receipt/{{ $rf->regID }}"
                           class="btn btn-success btn-sm">@lang('messages.buttons.rec_disp')</a>

                        <a target="_new" href="{{ $receipt_url }}"
                           class="btn btn-primary btn-sm">@lang('messages.buttons.rec_down')</a>
                        <br/>
                    @else
                        {{-- No charge but transaction wasn't completed.  Complete Reg button should be displayed if it's the only/first seat --}}

                        @if($rf->seats == 1 || $reg == $rf->registrations->first())
                            <a href="{!! env('APP_URL') !!}/confirm_registration/{{ $rf->regID }}"
                               class="btn btn-primary btn-sm">@lang('messages.buttons.comp_reg')</a>
                        @endif

                        {{-- Cancel button is always OK to show --}}
                        {!! Form::open(['method'  => 'delete', 'data-toggle' => 'validator',
                            'route' => ['cancel_registration', $reg->regID, $rf->regID] ]) !!}
                        <button type="submit" class="btn btn-danger btn-sm">
                            @lang('messages.buttons.reg_can')
                        </button>
                        {!! Form::close() !!}
                    @endif

                @endif

                &nbsp;<br/>
                @if($reg->ticket->has_sessions() && $rf->pmtRecd == 1)
                    @include('v1.parts.session_bubbles', ['event' => $rf->event, 'ticket' => $reg->ticket, 'rf' => $rf,
                    'reg' => $reg, 'regSession' => $regSessions])
                @endif

                @include('v1.parts.end_content')

            @endforeach  {{-- end of multiple-seat purchase --}}

        </div>
        @include('v1.parts.end_content')
    @endforeach

</div>
@include('v1.parts.end_content')
