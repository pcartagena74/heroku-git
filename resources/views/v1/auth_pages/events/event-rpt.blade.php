<?php
/**
 * Comment: The page to show all Event-related statistics
 * Created: 5/11/2017
 */

use App\Person;
use App\Ticket;
use App\RegFinance;

$topBits = ''; // there should be topBits for this

$headers = ['Ticket', 'Attendance Limit', 'Registrations', 'Wait List'];
$rows    = [];

foreach($tkts as $t) {
    array_push($rows, ['<nobr>' . $t->ticketLabel . '</nobr>', $t->maxAttendees, $t->regCount, $t->waitCount]);
}

$reg_headers = ['First Name', 'Last Name', 'Ticket', 'Register Date', 'Confirmation', 'Cost'];
$reg_rows    = [];

foreach($regs as $r) {
    $p = Person::find($r->personID);
    $t = Ticket::find($r->ticketID);
    $f = RegFinance::where('token', '=', $r->token)->first();
    array_push($reg_rows, [$p->firstName, $p->lastName, $t->ticketLabel, $t->createDate->format('n/j/Y'),
        $f->confirmation, '<i class="fa fa-dollar"></i>' . $f->cost]);
}

if(count($reg_rows) >= 15) {
    $scroll = 1;
} else {
    $scroll = 0;
}
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
    @include('v1.parts.start_content', ['header' => $event->eventName, 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])

    @include('v1.parts.start_content', ['header' => 'Ticket Statistics', 'subheader' => '', 'w1' => '6', 'w2' => '6', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    @include('v1.parts.datatable', ['headers' => $headers, 'data' => $rows, 'scroll' => 0])
    @include('v1.parts.end_content')

    @include('v1.parts.start_content', ['header' => 'Other Statistics', 'subheader' => '', 'w1' => '6', 'w2' => '6', 'r1' => 0, 'r2' => 0, 'r3' => 0])
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    @include('v1.parts.end_content')

    <div class="col-md-12 col-sm-12 col-xs-12">
        <ul id="myTab" class="nav nav-tabs bar_tabs nav-justified" role="tablist">
            <li class="active"><a href="#tab_content1" id="attendees-tab" data-toggle="tab"
                                  aria-expanded="true"><b>Registered Attendees</b></a></li>
            <li class=""><a href="#tab_content2" id="finances-tab" data-toggle="tab"
                            aria-expanded="false"><b>Financial Data</b></a></li>
            @if($event->hasTracks)
                <li class=""><a href="#tab_content3" id="sessions-tab" data-toggle="tab"
                                aria-expanded="false"><b>Session Registration</b></a></li>
            @endif
        </ul>

        <div id="tab-content" class="tab-content">
            <div class="tab-pane active" id="tab_content1" aria-labelledby="attendees-tab">
                &nbsp;<br/>

                @include('v1.parts.datatable', ['headers' => $reg_headers, 'data' => $reg_rows, 'scroll' => $scroll])

            </div>
            <div class="tab-pane active" id="tab_content2" aria-labelledby="finances-tab">
            </div>

            @if($event->hasTracks)
                <div class="tab-pane active" id="tab_content3" aria-labelledby="sessions-tab">
                </div>
            @endif

        </div>
    </div>

    @include('v1.parts.end_content')

@endsection


@section('scripts')
    @if($scroll)
    @include('v1.parts.footer-datatable')
    @endif
@endsection


@section('modals')
@endsection