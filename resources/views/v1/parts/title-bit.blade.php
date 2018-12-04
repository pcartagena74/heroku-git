<?php
/**
 * Comment: This is widget display at the top of pages
 * Created: 2/2/2017
 *
 * @param int $icon   This is a number that corresponds to the icon list below
 * @param str $label  This is the small text that above the big stat
 * @param int $number This is the main statistic (big green number) in the widget
 * @param str $ctext
 * @param str $rtext
 * @param int $width  override of col-xs-1 if provided
 *
 * icons: fa-user, fa-bar-chart, fa-calendar, fa-clock-o, fa-home, fa-heart
 */

if(!isset($width)){
    $width = 1;
}
if(!isset($up)){
    $up = 1;
}
switch ($icon) {
    case 1:
        $itxt = "fas fa-user";
        break;
    case 2:
        $itxt = "fas fa-chart-bar";
        break;
    case 3:
        $itxt = "far fa-calendar-alt";
        break;
    case 4:
        $itxt = "far fa-clock-o";
        break;
    case 5:
        $itxt = "far fa-home";
        break;
    case 6:
        $itxt = "fas fa-heart";
        break;
    case 7:
        $itxt = "fas fa-star";
        break;
    case 8:
        $itxt = "fas fa-cog";
        break;
    case 9:
        $itxt = "fas fa-users";
        break;
}

switch($up){
    case 0:
        $up = 'desc';
        $color = 'red';
        break;
    default:
        $up = 'asc';
        $color = 'green';
}
?>
<div class="col-xs-{{ $width }} tile_stats_count">
    <span style="text-align: center;" class="animated flipInY count_top"><i class="{{ $itxt }}">&nbsp;</i> {{ $label }}</span>
    <div style="text-align: center;" class="count green tiles-stats">{{ $number }}</div>
    @if($ctext <> "")
        <span style="text-align: center;" class="count_bottom"><i class="{{ $color }}"><i class="fa fa-sort-{{ $up }}"></i> {{ $ctext }}</i> {{ $rtext }}</span>
    @endif
</div>
