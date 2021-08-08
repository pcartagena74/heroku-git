@php
/**
 * Comment: Created for v3 of Stripe with stronger authentication
 * Created: 5/10/2019
 */
@endphp
<button id="{{ $id ?? 'payment' }}" type="submit" data-toggle="modal"  data-toggle="modal" data-target="#stripe_modal"
                                class="card btn btn-primary btn-md">
                            <b>@lang('messages.buttons.ccpay')</b>
</button>