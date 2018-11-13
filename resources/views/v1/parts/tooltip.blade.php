<?php
/**
 * Comment: a shortcut to get help text out there
 * Created: 12/4/2017
 * @param: $p          placement variable - defaults to top
 * @param: $title
 * @param: $c          color variable     - defaults to purple
 */
if(!isset($p)){
    $p = "top";
}
if(!isset($c)){
    $c = "purple";
}
?>
<a data-toggle="tooltip" title="{{ $title }}" data-placement="{{ $p }}">
    <i class="fas fa-info-square {{ $c }}"></i>
</a>
