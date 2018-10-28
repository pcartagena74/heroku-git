<?php
/**
 * Comment:
 * Created: 2/9/2017
 */


$headers = ['#', 'Name', trans('messages.fields.pmi_id'), trans('messages.fields.classification'),
            trans('messages.fields.compName'), trans('messages.fields.title'), trans('messages.fields.title'),
            trans('messages.fields.indName'), trans('messages.fields.expr'), trans('messages.fields.buttons')];

count($mbr_list) > 15 ? $scroll = 1 : $scroll = 0;

$members = [];

foreach($mbr_list as $mbr) {
    $csrf = csrf_field();

    $profile_form = "<a href='". env('APP_URL') . "/profile/$mbr->personID' type='button' data-toggle='tooltip' data-placement='top'
                     title='" . trans('messages.tooltips.vep') . "' class='btn btn-xs btn-primary'><i class='far fa-fw fa-edit'></i></a>";

    if($mbr->cnt >0) {
        $activity_form = "<div data-toggle='tooltip' data-placement='top' title='" . trans('messages.tooltips.va') . "'>
                          <button data-toggle='modal' class='btn btn-xs btn-success' data-target='#dynamic_modal'
                           data-target-id='" . $mbr->personID . "'><i class='far fa-fw fa-book'></i></button></div>";
    } else {
        $activity_form = '';
    }

    $merge_form = "<a href='" . env('APP_URL') . "/merge/p/$mbr->personID' data-toggle='tooltip' data-placement='top'
                    title='" . trans('messages.tooltips.mr') . "' class='btn btn-xs btn-warning'>
                   <i class='far fa-fw fa-code-branch'></i></a>";

    $mbr->cnt = $profile_form . $merge_form . $activity_form;
    // fullName, OrgStat1, OrgStat2, compName, title, indName, 'RelDate4' (now named 'Expire') - 7/20/17
}
$data = collect($mbr_list);

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')

    @include('v1.parts.start_content', ['header' => trans('messages.headers.mList'), 'subheader' => '',
             'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])

    @include('v1.parts.datatable', ['headers' => $headers,
        'data' => $data->toArray(),
        'id' => 'member_table',
        'scroll' => $scroll])

    @include('v1.parts.end_content')

@endsection

@section('scripts')
    @include('v1.parts.footer-datatable')
    <script>
        $(document).ready(function() {
            $('#member_table').DataTable({
                "fixedHeader": true,
                "order": [[ 0, "asc" ]]
            });
        });
    </script>
@endsection

@section('modals')
    @include('v1.modals.dynamic', ['header' => trans('messages.headers.mAct'), 'show_past' => 1])
@endsection
