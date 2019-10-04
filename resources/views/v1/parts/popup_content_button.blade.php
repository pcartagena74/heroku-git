<?php
/**
 * Comment:
 * Created: 10/2/2019
 */

if(!isset($placement)){
    $placement = 'top';
}
if(!isset($color)){
    $color = 'btn-primary';
}

// removed from below b/c of iOS
// data-trigger="focus"
?>
@if(isset($title) && isset($content) && isset($button_text))
<a tabindex="0" role="button"
   class="btn btn-xs {{ $color }} {{ $extra_class ?? '' }}"
   data-html="true"
   data-toggle="popover"
   data-placement="{{ $placement }}"
   title="{!! $title !!}"
   data-content="{!! $content !!}">
    {!! $button_text ?? '' !!}
</a>
@endif
