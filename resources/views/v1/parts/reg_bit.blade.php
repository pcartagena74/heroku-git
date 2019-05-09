<?php
/**
 * Comment: This is a reusable blade file that shows
 *          registrations purchased by someone else
 *          (reg-finance->personID <> $this->currentPerson->personID)
 *
 * @param:
 *      $header: for display
 *      $reg_array: the collection of registration records
 *
 * Created: 8/25/2017
 */

use App\RegSession;
use App\RegFinance;
use App\Registration;
use App\Person;
use App\Ticket;
use App\EventSession;
use App\Event;
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


    @foreach($reg_array as $reg)
        @if($reg->event->eventEndDate->gte($today))
            @include('v1.parts.start_min_content', ['header' => $reg->event->eventName,
            'subheader' => $reg->event->eventStartDate->format('n/j/Y'), 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
            <div class="col-md-12 col-sm-12 col-xs-12">

<?php
                $person = Person::find($reg->personID);
                $ticket = Ticket::find($reg->ticketID);
                $event = Event::find($reg->eventID);
                $regSessions = RegSession::where([
                    ['regID', '=', $reg->regID],
                    ['eventID', '=', $event->eventID]
                ])->get();
                $mem_or_not = Lang::has('messages.fields.'.$reg->membership) ? trans('messages.fields.'.$reg->membership) : $reg->membership;
                $rf    = RegFinance::where('regID', $reg->rfID)->first();
                $receipt_filename = $rf->eventID . "/" . $rf->confirmation . ".pdf";
                $receipt_url = $s3fs->getAdapter()->getClient()->getObjectUrl(env('AWS_BUCKET2'), $receipt_filename);
?>
                @include('v1.parts.start_min_content', ['header' => $mem_or_not . " Ticket (" .  $person->showFullName() .
                 "): " . $reg->ticket->ticketLabel . " (" . $reg->regID . '-' . $reg->ticket->ticketID . ")", 'subheader' => '<i class="fa fa-dollar"></i> ' .
                 $reg->subtotal, 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                {!! Form::open(['method'  => 'delete', 'route' => [ 'cancel_registration', $reg->regID, $reg->regfinance->regID ], 'data-toggle' => 'validator' ]) !!}
                {{--
                        // Removing ability to refund/cancel a sub-portion of a ticket by someone who did not purchase it.

                                        <button type="submit" class="btn btn-danger btn-sm">
                                            @if($reg->subtotal > 0)
                                                Refund Registration
                                            @else
                                                Cancel Registration
                                            @endif
                                        </button>
                                        {!! Form::close() !!}
                --}}
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

                @include('v1.parts.session_bubbles', ['event' => $reg->event, 'ticket' => $reg->ticket, 'rf' => $reg->regfinance,
                'reg' => $reg, 'regSession' => $regSessions])

                @include('v1.parts.end_content')

            </div>
            @include('v1.parts.end_content')
        @endif
    @endforeach

</div>
@include('v1.parts.end_content')
