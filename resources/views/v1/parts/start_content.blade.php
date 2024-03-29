@php
/**

* Comment: This starts all content boxes with a title (and small text) above an <hr>
 * Created: 2/2/2017
 *
 * @var $header
 * @var $subheader
 * @var $w1
 * @var $w2
 * @var $r1
 * @var $r2
 * @var $r3
 *
 * sending 'min' defined will trigger the collapse mechanism
 *
 */


 /*
  * To collapse, the page calling it must contain:
  *
  *
    <script>
        $('.collapsed').css('height', 'auto');
        $('.collapsed').find('.x_content').css('display', 'none');
    </script>
  */
if(isset($id)) {
    $id = "id='$id' ";
} else {
    $id = null;
}

if(!isset($w2)){
    $w2 = $w1;
}

if(!isset($class)){
    $class = "";
}

if($w1>0){
    $md = 'col-md-'.$w1;
} else {
    $md = '';
}

if($w2>0){
    $xs = 'col-xs-'.$w2;
} else {
    $xs = '';
}
if(isset($min)){
    $min = " collapsed";
    $chevron = "down";
} else {
    $min='';
    $chevron = "up";
}
@endphp
<div class="{{ $md }} {{ $xs }} {{ $class }}
        @if(isset($o))
        col-md-offset-{{ $o }} col-xs-offset-{{ $o }}
        @endif
        ">
    <div {!!  $id ?? '' !!}class="x_panel{{ $min }}">
        <div class="x_title">
            <h2>{!! $header !!}<small>&nbsp;{!! $subheader !!}</small></h2>

            @if ($r1+$r2+$r3 > 0)

                <ul class="nav navbar-right panel_toolbox">

                    @if ($r1==1)
                        <li><a class="collapse-link"><i class="fa fa-chevron-{{ $chevron }}"></i></a></li>
                    @endif

                    @if ($r2==1)
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="#">Settings 1</a></li>
                                <li><a href="#">Settings 2</a></li>
                            </ul>
                        </li>

                    @endif

                    @if ($r3==1)
                        <li><a class="close-link"><i class="fa fa-close"></i></a></li>
                    @endif

                </ul>
            @endif

            <div class="clearfix"></div>
        </div>
        <div class="x_content">