@php
    /**

     * Comment: This is a reusable blade file that shows
     *          registrations purchased by someone else
     *          (reg-finance->personID <> $this->currentPerson->personID)
     *
     * @param:
     *      $header: for display
     *      $reg_array: the collection of registration records
     *
     * Created: 8/25/2017; Updated 12/14/2024 for Laravel 9.x
     */

    use App\Models\RegSession;
    use App\Models\RegFinance;
    use App\Models\Registration;
    use App\Models\Person;
    use App\Models\Ticket;
    use App\Models\EventSession;
    use App\Models\Event;
    use Aws\S3\S3Client;
    use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
    use League\Flysystem\Filesystem;

    $tcount = 0;
    $today = \Carbon\Carbon::now();

@endphp

@include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
<div class="col-md-12 col-sm-12 col-xs-12">


    @foreach($reg_array as $reg)
        @if($reg->event->eventEndDate->gte($today))
            @include('v1.parts.start_min_content', ['header' => $reg->event->eventName,
            'subheader' => $reg->event->eventStartDate->format('n/j/Y'), 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
            <div class="col-md-12 col-sm-12 col-xs-12">

                @php
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

                    $s3name = select_bucket('r', config('APP_ENV'));
                    try {
                        if(Storage::disk($s3name)->exists($receipt_filename)){
                            $receipt_url = Storage::disk($s3name)->url($receipt_filename);
                        }
                    } catch (Exception $e) {
                            $receipt_url = '#';
                    }
                @endphp
                @include('v1.parts.start_min_content', ['header' => $mem_or_not .  " " . trans('messages.fields.ticket') . " (" .  $person->showFullName() .
                 "): " . $reg->ticket->ticketLabel . " (" . $reg->regID . '-' . $reg->ticket->ticketID . ")", 'subheader' => trans('messages.symbols.cur') .
                 $reg->subtotal, 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                <a target="_new" href="{!! env('APP_URL') !!}/show_receipt/{{ $rf->regID }}"
                   class="btn btn-success btn-sm">@lang('messages.buttons.rec_disp')</a>

                @if($reg->ticket->has_sessions())
                    @include('v1.parts.session_bubbles', ['event' => $reg->event, 'ticket' => $reg->ticket, 'rf' => $reg->regfinance,
                    'reg' => $reg, 'regSession' => $regSessions])
                @endif

                @include('v1.parts.end_content')

            </div>
            @include('v1.parts.end_content')
        @endif
    @endforeach

</div>
@include('v1.parts.end_content')
