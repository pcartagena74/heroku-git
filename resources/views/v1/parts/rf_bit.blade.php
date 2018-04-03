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
?>
        {{-- @if($rf->event->eventEndDate->gte($today)) --}}
        @include('v1.parts.start_min_content', ['header' => $rf->event->eventName,
                 'subheader' => $rf->event->eventStartDate->format('n/j/Y'),
                 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
        <div class="col-md-12 col-sm-12 col-xs-12">
            @if($rf->seats > 1)
                @foreach($rf->registration as $reg)
<?php
                    $person = Person::find($reg->personID);
                    $event = Event::find($reg->eventID);
                    $org = Org::find($event->orgID);
                    $regSessions = RegSession::where([
                        ['regID', '=', $reg->regID],
                        ['eventID', '=', $event->eventID]
                    ])->get();
?>
                    @include('v1.parts.start_min_content', ['header' => $reg->membership .
                    " Ticket (" .  $person->showFullName() . "): " . $reg->ticket->ticketLabel . " (" . $reg->regID . ")",
                    'subheader' => '<i class="fa fa-dollar"></i> ' . $reg->subtotal,
                    'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                    @if($rf->pmtRecd == 1)  {{-- payment received --}}

                        @if($event->eventStartDate->gte($today->subDays($org->refundDays)) && !$event->isNonRefundable)
                            {!! Form::open(['method'  => 'delete',
                                            'route' => [ 'cancel_registration', $reg->regID, $rf->regID ],
                                            'data-toggle' => 'validator' ]) !!}
                            <button type="submit" class="btn btn-danger btn-sm">
                                @if($rf->cost > 0)
                                    Refund Registration
                                @else
                                    Cancel Registration
                                @endif
                            </button>
                            {!! Form::close() !!}

                        @endif

                        <a target="_new"
                           @if($rf->isGroupReg)
                           href="{!! env('APP_URL') !!}/show_group_receipt/{{ $rf->regID }}"
                           @else
                           href="{!! env('APP_URL') !!}/show_receipt/{{ $rf->regID }}"
                           @endif
                           class="btn btn-success btn-sm">Display Receipt</a>
                        <a target="_new" href="{{ $receipt_url }}"
                           class="btn btn-primary btn-sm">Download Receipt</a>
                        <br/>

                        @include('v1.parts.session_bubbles', ['event' => $rf->event, 'ticket' => $reg->ticket, 'rf' => $rf,
                        'reg' => $reg, 'regSession' => $regSessions])

                    @else {{-- payment not received; possibly marked "At the Door" --}}

                        @if($rf->cost > 0)
                            <a href="{!! env('APP_URL') !!}/confirm_registration/{{ $rf->regID }}"
                               class="btn btn-primary btn-sm">Pay Balance Due Now</a>
                        @else
                            {!! Form::open(['method'  => 'delete',
                                            'route' => [ 'cancel_registration', $reg->regID, $rf->regID ],
                                            'data-toggle' => 'validator' ]) !!}
                            <button type="submit" class="btn btn-danger btn-sm">
                                Cancel Registration
                            </button>
                            {!! Form::close() !!}

                        @endif

                    @endif

                    @include('v1.parts.end_content')

                @endforeach  {{-- end of multiple-seat purchase --}}
            @else
<?php
                $reg = Registration::with('ticket', 'person')->where('regID', $rf->regID)->first();
?>
                @include('v1.parts.start_min_content', ['header' => $reg->membership .
                " (" . $reg->person->showFullName() . ") Ticket: " . $reg->ticket->ticketLabel .
                " (" . $reg->regID . ")", 'subheader' => '<i class="fa fa-dollar"></i> ' . $reg->subtotal,
                'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
<?php
                $regSessions = RegSession::where([
                    ['regID', '=', $reg->regID],
                    ['eventID', '=', $rf->event->eventID]
                ])->get();
                $event = $rf->event;
                $org = Org::find($event->orgID);
?>
                @if($rf->pmtRecd == 1)  {{-- payment received --}}
                @if($event->eventStartDate->gte($today->subDays($org->refundDays)) && !$event->isNonRefundable)
                    {!! Form::open(['method'  => 'delete',
                                    'route' => [ 'cancel_registration', $reg->regID, $rf->regID ],
                                    'data-toggle' => 'validator' ]) !!}
                    <button type="submit" class="btn btn-danger btn-sm">
                        @if($rf->cost > 0)
                            Refund Registration
                        @else
                            Cancel Registration
                        @endif
                    </button>
                    {!! Form::close() !!}
                @endif

                <a target="_new"
                   @if($rf->isGroupReg)
                   href="{!! env('APP_URL') !!}/show_group_receipt/{{ $rf->regID }}"
                   @else
                   href="{!! env('APP_URL') !!}/show_receipt/{{ $rf->regID }}"
                   @endif
                   class="btn btn-success btn-sm">Display Receipt</a>
                <a target="_new" href="{{ $receipt_url }}"
                   class="btn btn-primary btn-sm">Download Receipt</a>
                <br/>

                @include('v1.parts.session_bubbles', ['event' => $rf->event, 'ticket' => $reg->ticket, 'rf' => $rf,
                'reg' => $reg, 'regSession' => $regSessions])

                @else {{-- payment not received; possibly marked "At the Door" --}}
                    @if($event->eventStartDate->gte($today->subDays($org->refundDays)))
                        {!! Form::open(['method'  => 'delete',
                                        'route' => [ 'cancel_registration', $reg->regID, $rf->regID ],
                                        'data-toggle' => 'validator' ]) !!}
                        <button type="submit" class="btn btn-danger btn-sm">
                            Cancel Registration
                        </button>
                        {!! Form::close() !!}
                    @endif

                    @if($rf->cost > 0 && $rf->pmtRecd == 0)
                        <a href="{!! env('APP_URL') !!}/confirm_registration/{{ $rf->regID }}"
                           class="btn btn-primary btn-sm">Pay Balance Due Now</a>
                    @else
                        <a target="_new"
                           @if($rf->isGroupReg)
                           href="{!! env('APP_URL') !!}/show_group_receipt/{{ $rf->regID }}"
                           @else
                           href="{!! env('APP_URL') !!}/show_receipt/{{ $rf->regID }}"
                           @endif
                           class="btn btn-success btn-sm">Display Receipt</a>
                        <a target="_new" href="{{ $receipt_url }}"
                           class="btn btn-primary btn-sm">Download Receipt</a>
                    @endif
                <br/>
                @include('v1.parts.session_bubbles', ['event' => $rf->event, 'ticket' => $reg->ticket, 'rf' => $rf,
                'reg' => $reg, 'regSession' => $regSessions])
                @endif

                @include('v1.parts.end_content')
            @endif
        </div>
        @include('v1.parts.end_content')
    @endforeach

</div>
@include('v1.parts.end_content')
