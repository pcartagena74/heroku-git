<?php
/**
 * Comment:
 * Created: 7/18/2017
 */
$topBits = '';
$header = ['ID', trans('messages.fields.firstName'), trans('messages.fields.lastName'),
           trans('messages.fields.email'), trans_choice('messages.headers.events', 2), trans('messages.fields.buttons')];
$data = [];

foreach($speakers as $speaker){

    if($speaker->count > 0){
        $button = $color = 'btn-purple'; $text = trans('messages.headers.sAct');
        $tooltip = trans('messages.headers.sAct');
        $extras = "data-toggle='modal' data-target='#dynamic_modal' data-target-id='" . $speaker->personID. "'";
        $symbol = "<i class='far fa-microphone'></i>"; $dp = 'left';
        $html = view('v1.parts.url_button',
            compact('button', 'extras', 'color', 'tooltip', 'symbol', 'dp')
        )->render();
    } else {
        $html = null;
    }
    array_push($data, [$speaker->personID, $speaker->firstName, $speaker->lastName, $speaker->login, $speaker->count, $html]);
}
?>

@extends('v1.layouts.auth')

@section('content')
    <h2>
        @lang('messages.nav.s_list')
    </h2>

    <p>
        <b>@lang('messages.headers.note'):</b> @lang('messages.instructions.add_speaker')
    </p>

    @include('v1.parts.datatable', ['headers' => $header, 'data' => $data, 'scroll' => 1])


@endsection

@section('scripts')
@include('v1.parts.footer-datatable')
<script>
    $(document).ready(function() {
        $('#datatable-fixed-header').DataTable({
            "fixedHeader": true,
            "order": [[ 1, "asc" ]]
        });
    });
</script>
@endsection

@section('modals')
    @include('v1.modals.dynamic', ['header' => trans('messages.headers.sAct'), 'url' => 'speakers'])
    @include('v1.modals.context_sensitive_issue')
@endsection
