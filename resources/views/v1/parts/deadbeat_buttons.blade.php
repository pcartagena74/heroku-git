@php
/**
 * Comment: Template for Cash/Check or Delete Buttons
 * Created: 1/11/2019
 *
 * @param $regID
 * @param $rfID
 *
 */
@endphp
@if (Entrust::hasRole('Admin'))
    {{ html()->form('PATCH', route('accept_payment', [$regID, $rfID]))->data('toggle', 'validator')->open() }}
    <button type="submit" value="1" name="{!! trans('messages.buttons.cash') !!}"
            onclick="return confirm('{!! trans('messages.tooltips.pmt_msg', ['method' => strtolower(trans('messages.buttons.cash'))]) !!}')"
            class="btn btn-success btn-sm" data-toggle="tooltip" title="{!! trans('messages.tooltips.cash') !!}">
        {!! trans('messages.symbols.cash') !!}
    </button>

    <button type="submit" value="1" name="{!! trans('messages.buttons.check') !!}"
            onclick="return confirm('{!! trans('messages.tooltips.pmt_msg', ['method' => strtolower(trans('messages.buttons.check'))]) !!}')"
            class="btn btn-primary btn-sm" data-toggle="tooltip" title="{!! trans('messages.tooltips.check') !!}">
        {!! trans('messages.symbols.check') !!}
    </button>

    <a class="btn btn-warning btn-sm" data-toggle="tooltip" title="{!! trans('messages.tooltips.card') !!}"
            target="_new" href="{!! env('APP_URL') . "/confirm_registration/$rfID" !!}">
        {!! trans('messages.symbols.card') !!}
    </a>
    {{ html()->form()->close() }}
    {{ html()->form('DELETE', route('cancel_registration', [$regID, $rfID]))->data('toggle', 'validator')->open() }}
    <button type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip"
            onclick="return confirm('{!! trans('messages.tooltips.sure_cancel') !!}');"
            title="{!! trans('messages.tooltips.reg_cancel') !!}">
    {!! trans('messages.symbols.trash') !!}
    </button>
    {{ html()->form()->close() }}
@else
    <button type="submit" class="btn btn-secondary btn-sm" data-toggle="tooltip" title="{!! trans('messages.tooltips.no_auth') !!}">
        <i class="far fa-money-bill-wave"></i>
    </button>

    <button type="submit" class="btn btn-secondary btn-sm" data-toggle="tooltip" title="{!! trans('messages.tooltips.no_auth') !!}">
        <i class="far fa-money-check-alt"></i>
    </button>
@endif
