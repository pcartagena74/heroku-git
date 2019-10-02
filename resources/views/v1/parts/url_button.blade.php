<?php
/**
 * Comment: Reusable, parametrized button template
 * Created: 2/12/2019
 *
 * @param $color
 * @param $tooltip
 *
 * Optional:
 * @param $button - default assumes a "link button"
 * @param $dp - data-placement default: top
 * @param $url
 * @param $text
 * @param $symbol
 * @param $confirm
 * @param $extras
 *
onclick="return confirm('Are you sure you want to cancel this registration and issue a refund?');"
 */

?>
@if(isset($button))
    @if(isset($tooltip))
        <div data-toggle="tooltip" title="{!! $tooltip !!}" data-placement="{{ $dp ?? 'top' }}">
    @endif
        <button
@else
    <a href="{{ $url ?? '' }}"
@endif
@if(isset($tooltip) && !isset($button))
    data-toggle="tooltip" title="{!! $tooltip !!}" data-placement="{{ $dp ?? 'top' }}"
@endif
@if(isset($confirm))
    onclick="return confirm('{!! $confirm !!}');"
@endif
@if(isset($extras))
    {!! $extras !!}
@endif

    class="btn {{ $color }} btn-md">
    {!! $symbol ?? '' !!}
    {!! $text ?? '' !!}

@if(isset($button))
    </button>
    @if(isset($tooltip))
        </div>
    @endif
    @else
        </a>
@endif
