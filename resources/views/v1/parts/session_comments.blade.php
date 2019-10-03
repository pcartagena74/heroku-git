<?php
/**
 * @param: $list
 * @param: $title
 * Comment: Compiles the session comments from RSSurvey responses into a list
 * Created: 10/3/2019
 */
?>
@if(count($list)> 0)
    <b>{!! $title !!}</b>
    <ul>
        @foreach($list as $item)
        {!! str_replace('"', "&quot", $item) !!}
        @endforeach
    </ul>
@endif

