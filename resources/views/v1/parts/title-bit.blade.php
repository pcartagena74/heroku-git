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
 *
 * icons: fa-user, fa-bar-chart, fa-calendar, fa-clock-o, fa-home, fa-heart
 */
switch ($icon) {
    case 1:
        $itxt = "fa-user";
        break;
    case 2:
        $itxt = "fa-bar-chart";
        break;
    case 3:
        $itxt = "fa-calendar";
        break;
    case 4:
        $itxt = "fa-clock-o";
        break;
    case 5:
        $itxt = "fa-home";
        break;
    case 6:
        $itxt = "fa-heart";
        break;
    case 7:
        $itxt = "fa-star-o";
        break;
    case 8:
        $itxt = "fa-cog";
        break;
}
?>
<div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
    <span class="count_top"><i class="fa {{ $itxt }}">&nbsp;</i>{{ $label }}</span>
    <div class="count green">{{ $number }}</div>
    @if($ctext <> "")
        <span class="count_bottom"><i class="green"><i class="fa fa-sort-asc"></i>{{ $ctext }}</i>{{ $rtext }}</span>
    @endif
</div>
