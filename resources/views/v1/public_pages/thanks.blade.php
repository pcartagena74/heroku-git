<?php
/**
 * Comment: a generic Thank You/Confirmation screen
 * Created: 7/8/2017
 *
 * Consider the following:
 *  - pass a header and message to customize the thanks/confirmation
 *  - $header defaults to "Thank You!" if not set
 *
 */

if(!isset($header)){
    $header = 'Thank You!';
}
?>
@extends('v1.layouts.no-auth_simple')

@section('content')
    @include('v1.parts.start_content', ['header' => $header, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    <h2>{{ $message }}</h2>

    @include('v1.parts.end_content')

@endsection