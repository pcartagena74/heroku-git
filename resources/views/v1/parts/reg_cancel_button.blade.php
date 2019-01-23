<?php
/**
 * Comment: Template for registration cancel/refund button that can be reused
 * Created: 10/23/2018
 *
 * @param $reg: the regID concerned
 * @param $wait: will be set to 1 if wait list button needed
 *
 */

$wait_tooltip = trans('messages.tooltips.wait_cnv');
$wait_confirm = trans('messages.tooltips.sure');

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
    $button_tooltip = trans('messages.tooltips.click_cancel_reg') ;
} else {
    $confirm_msg = "";
    $button_class = "btn-secondary";
    $button_tooltip = trans('messages.tooltips.cant_cancel_reg');
}
?>
@if(Entrust::hasRole('Admin'))

    @if($reg->regStatus == trans('messages.headers.wait'))
        <a class="btn btn-primary btn-sm" onclick="return confirm('{{ $wait_confirm }}');"
           href="{!! env('APP_URL')."/promote/$reg->regID" !!}" data-toggle="tooltip" title="{!! $wait_tooltip !!}"><i class="fas fa-angle-double-right"></i></a>
    @endif

    {!! Form::open(['method' => 'delete', 'route' => ['cancel_registration', $reg->regID, $reg->rfID], 'data-toggle' => 'validator']) !!}
    <button type="submit" class="btn {{ $button_class }} btn-sm" onclick="return confirm('{{ $confirm_msg }}');"
        data-toggle="tooltip" data-placement="top" title="{{ $button_tooltip }}">
        {!! $button_symbol !!}
    </button>
    {!! Form::close() !!}
@else
    <button class="btn {{ $button_class }}btn-sm" data-toggle="tooltip" title="{{ $button_tooltip }}">
        {!! $button_symbol !!}
    </button>
@endif

