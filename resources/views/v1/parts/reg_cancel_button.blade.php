@php
    /**
     * Comment: Template for registration cancel/refund button that can be reused
     * Created: 10/23/2018; Updated 10/12/2024 for Laravel 9.x
     *
     * @var $reg : the regID concerned
     * @var $wait : will be set to 1 if wait list button needed
     *
     */

    use Aws\S3\S3Client;
    use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
    use League\Flysystem\Filesystem;

    $wait_tooltip = trans('messages.tooltips.wait_cnv');
    $wait_confirm = trans('messages.tooltips.sure');

    $today = \Carbon\Carbon::now();
    $post_event = $today->gte($reg->event->eventEndDate);

    $rf = $reg->regfinance;
    $receipt_filename = $rf->eventID . "/" . $rf->confirmation . ".pdf";

    try {
        if(Storage::disk('s3_receipts')->exists($receipt_filename)){
            $receipt_url = Storage::disk('s3_receipts')->url($receipt_filename);
            $receipt_exists = true;
        }
    } catch(Exception $e) {
        $receipt_url = '#';
        $receipt_exists = false;
    }

    if ($reg->subtotal > 0 && $reg->regfinance->pmtRecd) {
        // currency symbol
        $button_symbol = trans('messages.symbols.cur');
        $confirm_msg = trans('messages.tooltips.sure_refund');
    } else {
        // trash can (for delete) in lieu of currency symbol
        $button_symbol = trans('messages.symbols.trash');
        $confirm_msg = trans('messages.tooltips.sure_cancel');
    }

    if (Entrust::hasRole('Admin')) {
        $button_class = "btn-danger";
        $button_tooltip = trans('messages.tooltips.click_cancel_reg');
    } else {
        $confirm_msg = "";
        $button_class = "btn-secondary";
        $button_tooltip = trans('messages.tooltips.cant_cancel_reg');
    }
@endphp
@if(Entrust::hasRole('Admin') || Entrust::hasRole('Developer'))

    @if(!$post_event || Entrust::hasRole('Developer'))
        @if($reg->regStatus == 'wait')
            <a class="btn btn-primary btn-sm" onclick="return confirm('{{ $wait_confirm }}');"
               href="{!! env('APP_URL')."/promote/$reg->regID" !!}" data-toggle="tooltip" title="{!! $wait_tooltip !!}"><i
                        class="fas fa-angle-double-right"></i></a>
        @endif

        @if($reg->regStatus == trans('messages.reg_status.progress'))
            <a href="{{ env('APP_URL') }}/confirm_registration/{{ $reg->rfID }}" target="_top"
               class="btn btn-sm btn-success"
               data-toggle="tooltip" title="{{ trans('messages.headers.reg_con2') }}" data-placement="top">
                <i class="fal fa-credit-card"></i>
            </a>
        @endif

        {{ html()->form('DELETE', route('cancel_registration', [$reg->regID, $reg->rfID]))->data('toggle', 'validator')->open() }}
        <button type="submit" class="btn {{ $button_class }} btn-sm" onclick="return confirm('{{ $confirm_msg }}');"
                data-toggle="tooltip" data-placement="top" title="{{ $button_tooltip }}">
            {!! $button_symbol !!}
        </button>
        {{ html()->form()->close() }}
    @endif

    @if($reg->regfinance->pmtRecd && $reg->subtotal > 0)
        @if($receipt_exists)
            <a target="_new" href="{{ $receipt_url }}"
               class="btn btn-success btn-sm" data-toggle="tooltip" title="{!! trans('messages.buttons.rec_down') !!}">
                <i class="far fa-file-invoice-dollar fa-fw"></i></a>
        @else
            <a target="_new" href="{{ env('APP_URL'). "/recreate_receipt/".$rf->regID }}"
               class="btn btn-success btn-sm" data-toggle="tooltip" title="{!! trans('messages.buttons.rec_down') !!}">
                <i class="far fa-file-invoice-dollar fa-fw"></i></a>
        @endif
    @endif

@else
    @if(!$post_event)
        <button class="btn {{ $button_class }}btn-sm" data-toggle="tooltip" title="{{ $button_tooltip }}">
            {!! $button_symbol !!}
        </button>
    @endif
@endif

