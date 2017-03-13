<?php
/**
 * Comment: Confirmation screen and launcher of Braintree Paypal stuff
 * Created: 3/12/2017
 */



?>
@extends('v1.layouts.no-auth2')


@section('content')
    @include('v1.parts.start_content', ['header' => "Registration Confirmation", 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    @include('v1.parts.end_content')
@endsection
