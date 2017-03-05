<?php
/**
 * Comment:
 * Created: 2/9/2017
 */


$headers = ['#', 'Name', 'PMI ID', 'PMI Classification', 'Company', 'Title', 'Industry', 'Expiration', 'Buttons'];

count($mbr_list) > 15 ? $scroll = 1 : $scroll = 0;

$members = [];

foreach($mbr_list as $mbr) {
    $csrf = csrf_field();

    $profile_form = "<a href='/profile/$mbr->personID' type='button' data-toggle='tooltip' data-placement='top'
                     title='View/Edit Profile' class='btn btn-xs btn-primary'><i class='fa fa-edit'></i></a>";

    if($mbr->cnt >0) {
        $activity_form = "<button data-toggle='tooltip' data-placement='top' title='View Activity' class='btn btn-xs btn-success'>
                          <i class='fa fa-book'></i></button>";
    } else {
        $activity_form = '';
    }

    $mbr->cnt = $profile_form . $activity_form;
    // fullName, OrgStat1, OrgStat2, compName, title, indName, 'RelDate4'
}

//dd($members);
?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @include('v1.parts.start_content', ['header' => 'Member List', 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

    @include('v1.parts.datatable', ['headers' => $headers, 'data' => $mbr_list, 'scroll' => $scroll])

    @include('v1.parts.end_content')

@endsection

@section('scripts')
    @include('v1.parts.footer-datatable')
@endsection