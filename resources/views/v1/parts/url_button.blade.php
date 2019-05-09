<?php
/**
 * Comment: Reusable, parametrized button template
 * Created: 2/12/2019
 *
 * @param $color
 * @param $url
 * @param $tooltip
 * @param $text
 * @param $symbol
 * @param $color
 * @param $confirm
 *
onclick="return confirm('Are you sure you want to cancel this registration and issue a refund?');"
 */
?>
<a href="{{ $url }}"
   @if(isset($tooltip))
      data-toggle="tooltip" title="{!! $tooltip !!}" data-placement="top"
   @endif
   @if(isset($confirm))
       onclick="return confirm('{!! $confirm !!}');"
   @endif
   class="btn {{ $color }} btn-md">
    {!! $symbol ?? '' !!}
    {!! $text ?? '' !!}
</a>
