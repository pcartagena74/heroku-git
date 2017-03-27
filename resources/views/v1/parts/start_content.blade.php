<?php
/**
 * Comment: This starts all content boxes with a title (and small text) above an <hr>
 * Created: 2/2/2017
 *
 * $header, $subheader, $w1, $w2, $r1, $r2, $r3 [optional $id]
 *
 */

if(isset($id)) {
    $id = "id='$id' ";
}

?>
<div class="col-md-{{ $w1 }} col-xs-{{ $w2 }}
        @if(isset($o))
        col-md-offset-{{ $o }} col-xs-offset-{{ $o }}
        @endif
        ">
    <div {!!  $id or '' !!}class="x_panel">
        <div class="x_title">
            <h3>{!! $header !!}<small>&nbsp;{{ $subheader }}</small></h3>

            @if ($r1+$r2+$r3 > 0)

                <ul class="nav navbar-right panel_toolbox">

                    @if ($r1==1)
                        <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
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