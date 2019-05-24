<?php
/**
 * Comment: a generic Error screen
 * Created: 11/18/2017
 */

if(!isset($header)){
    $header = 'Error!';
}
?>
@extends('v1.layouts.no-auth_simple')

@section('content')
    @include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <h2>{!! $message !!}</h2>

    @include('v1.parts.end_content')

@endsection