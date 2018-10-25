<?php
/**
 * Comment: Template for registration cancel/refund button that can be reused
 * Created: 10/23/2018
 *
 * @param $reg: the regID concerned
 */

/*
$f = Form::open(['method' => 'delete', 'route' => ['cancel_registration', $r->regID, $r->rfID], 'data-toggle' => 'validator']);
$f .= '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\"' . trans('messages.tooltips.sure_refund') . '\");">';
$f .= '<i ' . trans('messages.symbols.cur_class') . ' data-toggle="tooltip" data-placement="top" title="'.
    trans('messages.tooltips.click_cancel_reg') .'"></i></button></form>';
*/

if ($reg->subtotal > 0 && $reg->regfinance->pmtRecd) {
    $button_symbol = trans('messages.symbols.cur');
    $confirm_msg = trans('messages.tooltips.sure_refund');
} else {
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
    {!! Form::open(['method' => 'delete', 'route' => ['cancel_registration', $reg->regID, $reg->rfID], 'data-toggle' => 'validator']) !!}
    <button type="submit" class="btn {{ $button_class }} btn-sm" onclick="return confirm('{{ $confirm_msg }}');"
        data-toggle="tooltip" data-placement="top" title="{{ $button_tooltip }}">
        {!! $button_symbol !!}
    </button>
    {!! Form::close() !!}
@else
    <button class="btn {{ $button_class }}btn-sm">
        <i data-toggle="tooltip" data-placement="top" title="{{ $button_tooltip }}" {!! $button_symbol !!}></i>
    </button>
@endif

