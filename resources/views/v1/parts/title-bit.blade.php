@php
/**
 * Comment: This is widget display of counts of things at the top of pages
 * Created: 2/2/2017
 *
 * @var int $icon   This is a number that corresponds to the icon list below
 * @var str $label  This is the small text that above the big stat
 * @var int $number This is the main statistic (big green number) in the widget
 * @var str $ctext  This is the greed/red statistic small text
 * @var str $rtext  This is the black accompanying text
 * @var int $up     override of $up value of 1
 * @var int $width  override of col-xs-1 if provided
 *
 * icons: fa-user, fa-bar-chart, fa-calendar, fa-clock-o, fa-home, fa-heart
 */

if(!isset($width)){
    $width = 1;
}

if($ctext == 0 || $ctext == ''){
    $up = 0;
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
        $itxt = "far fa-clock";
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
    case -1:
        $up = '-down';
        $color = 'red';
        break;
    case 1:
        $up = '-up';
        $color = 'green';
        break;
    default:
        $up = '';
        $color = 'red';
}
@endphp
<div class="col-xs-{{ $width }} tile_stats_count">
    <span style="text-align: center;" class="animated flipInY count_top"><i class="{{ $itxt }}">&nbsp;</i> {{ $label }}</span>
    <div style="text-align: center;" class="count green tiles-stats">{{ $number }}</div>
    @if(isset($ctext) && isset($rtext) && strlen($ctext)>0 && strlen($rtext)>0 )
        <span style="text-align: center;" class="count_bottom"><i class="{{ $color }}"><i class="fas fa-sort{{ $up }}"></i> {{ $ctext }}</i> {{ $rtext }}</span>
    @endif
</div>
