@php
    /**
     * Comment: This is a reusable blade file that shows
     *          registrations based on passed reg-finance collection
     *
     * @param:
     *      $header: for display
     *      $rf_array: the collection of reg-finance records
     *
     * Created: 8/25/2017
     * Updated: 2/08/2025 - L9 upgrade & AWSS3V3 stuff
     */

    use App\Models\RegSession;
    use App\Models\Registration;
    use App\Models\Person;
    use App\Models\Ticket;
    use App\Models\EventSession;
    use App\Models\Event;
    use App\Models\Org;
    use Aws\S3\S3Client;
    use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
    use League\Flysystem\Filesystem;

    $tcount = 0;
    $today = \Carbon\Carbon::now();

@endphp

@include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
<div class="col-md-12 col-sm-12 col-xs-12">

    @foreach($rf_array as $rf)
        @php
            $receipt_filename = $rf->eventID . "/" . $rf->confirmation . ".pdf";
            $s3name = select_bucket('r', config('APP_ENV'));
            try {
                if(Storage::disk($s3name)->exists($receipt_filename)){
                    $receipt_url = Storage::disk($s3name)->url($receipt_filename);
                }
                $file_headers = @get_headers($receipt_url);
                if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
                    $receipt_exists = false;
                } else {
                    $receipt_exists = true;
                }
            } catch (Exception $e) {
                    $receipt_url = '#';
                    $receipt_exists = false;
            }


            $header = $rf->event->eventName . " <small>" . trans_choice('messages.headers.seats', $rf->seats) . ": " . $rf->seats . "</small>";
        @endphp
        {{-- @if($rf->event->eventEndDate->gte($today)) --}}
        @include('v1.parts.start_min_content', ['header' => $header,
                 'subheader' => $rf->event->eventStartDate->format('n/j/Y'),
                 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
        <div class="col-md-12 col-sm-12 col-xs-12">
            @foreach($rf->registrations as $reg)
                @php
                    $person = Person::find($reg->personID);
                    $event = Event::find($reg->eventID);
                    $org = Org::find($event->orgID);
                    $regSessions = RegSession::where([
                        ['regID', '=', $reg->regID],
                        ['eventID', '=', $event->eventID]
                    ])->get();
                    $mem_or_not = Lang::has('messages.fields.' . $reg->membership) ? trans('messages.fields.' . $reg->membership) : $reg->membership;
                    //if($reg->regID == 12597) { dd($event->eventStartDate); }
                @endphp
                @include('v1.parts.start_min_content', ['header' => $mem_or_not . " " . trans('messages.fields.ticket') . " (" .  $person->showFullName() .
                "): " . $reg->ticket->ticketLabel . " (" . $reg->regID . '-' . $reg->ticket->ticketID . ")", 'subheader' => trans('messages.symbols.cur').
                ' ' . number_format($reg->subtotal, 2), 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

                @if($rf->cost > 0)
                    {{-- There is a fee for event --}}

                    @if($rf->pmtRecd == 1 && $today->lte($event->eventStartDate->subDays($org->refundDays)) && !$event->isNonRefundable)
                        {{-- Payment received and able to display a refund button --}}

                        {{ html()->form('DELETE', route('cancel_registration', [$reg->regID, $rf->regID]))->data('toggle', 'validator')->open() }}
                        <button type="submit" class="btn btn-danger btn-sm">
                            @lang('messages.buttons.reg_ref')
                        </button>
                        {{ html()->form()->close() }}
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
                        {{ html()->form('DELETE', route('cancel_registration', [$reg->regID, $rf->regID]))->data('toggle', 'validator')->open() }}
                        <button type="submit" class="btn btn-danger btn-sm">
                            @lang('messages.buttons.reg_can')
                        </button>
                        {{ html()->form()->close() }}
                    @endif

                @else
                    {{-- There is no fee for event --}}

                    @if($rf->pmtRecd == 1)
                        {{-- No-fee payment received (here meaning completed transaction) so cancelation is OK whenever --}}

                        {{ html()->form('DELETE', route('cancel_registration', [$reg->regID, $rf->regID]))->data('toggle', 'validator')->open() }}
                        <button type="submit" class="btn btn-danger btn-sm">
                            @lang('messages.buttons.reg_can')
                        </button>
                        {{ html()->form()->close() }}

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
                        {{ html()->form('DELETE', route('cancel_registration', [$reg->regID, $rf->regID]))->data('toggle', 'validator')->open() }}
                        <button type="submit" class="btn btn-danger btn-sm">
                            @lang('messages.buttons.reg_can')
                        </button>
                        {{ html()->form()->close() }}
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
