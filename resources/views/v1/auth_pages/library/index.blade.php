@php

/**
 * Comment: Create New Organization and associate it with a user.
 *
 * Created: 18/02/2020
 */

$topBits = '';  // remove this if this was set in the controller
$header = implode(" ", [trans('messages.nav.o_create')]);

@endphp
@extends('v1.layouts.auth', ['topBits' => $topBits])
@section('header')
{{--
<link href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" rel="stylesheet"/>
--}}
{{--
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet"/>
--}}
@endsection
<link href="{{ asset('vendor/file-manager/css/file-manager.css') }}" rel="stylesheet"/>
@section('content')
<style type="text/css">
    #file_manager .fm-content.d-flex.flex-column.col {
		width: 100%;
	}
	#file_manager .fm-content.d-flex.flex-column.col .fm-disk-list{
		display: none;
	}
	#file_manager .fm-breadcrumb ol.breadcrumb.active-manager li:nth-child(n+3) {
		display:none;
	}
	#file_manager .fm-breadcrumb ol.breadcrumb.active-manager li:nth-child(1) {
		display:none;
	}
	#file_manager .table {
    width: 100%;
    margin-bottom: 1rem;
    color: #212529
}
#file_manager .col-auto {
    -ms-flex: 0 0 auto;
    flex: 0 0 auto;
    width: auto;
    max-width: 100%;
    margin: 0 auto 0 0;
}
#file_manager .col-auto.text-right {
    margin: 0 0 auto 0;
}
#file_manager .row {
    display: flex;
    -ms-flex-wrap: wrap;
    flex-wrap: wrap;
    margin-right: -15px;
    margin-left: -15px;
    margin-bottom: 10px;
}

#file_manager .justify-content-between {
    -ms-flex-pack: justify!important;
    justify-content: space-between!important;
}
#file_manager .table td, #file_manager .table th {
    padding: .75rem;
    vertical-align: top;
    border-top: 1px solid #dee2e6
}
#file_manager .table thead th {
    vertical-align: bottom;
    border-bottom: 2px solid #dee2e6
}
#file_manager .table tbody+tbody {
    border-top: 2px solid #dee2e6
}
#file_manager .table-sm td,#file_manager  .table-sm th {
    padding: .3rem
}
#file_manager .table-bordered {
    border: 1px solid #dee2e6
}
#file_manager .table-bordered td,#file_manager  .table-bordered th {
    border: 1px solid #dee2e6
}
#file_manager .table-bordered thead td,#file_manager  .table-bordered thead th {
    border-bottom-width: 2px
}
#file_manager .table-borderless tbody+tbody,#file_manager  .table-borderless td,#file_manager  .table-borderless th,#file_manager  .table-borderless thead th {
    border: 0
}
#file_manager .table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, .05)
}
#file_manager .table-hover tbody tr:hover {
    color: #212529;
    background-color: rgba(0, 0, 0, .075)
}
#file_manager .table-primary,#file_manager .table-primary>td,#file_manager  .table-primary>th {
    background-color: #b8daff
}
#file_manager .table-primary tbody+tbody,#file_manager  .table-primary td,#file_manager .table-primary th,#file_manager .table-primary thead th {
    border-color: #7abaff
}
#file_manager .table-hover .table-primary:hover {
    background-color: #9fcdff
}
#file_manager .table-hover .table-primary:hover>td,#file_manager .table-hover .table-primary:hover>th {
    background-color: #9fcdff
}
#file_manager .table-secondary,#file_manager .table-secondary>td,#file_manager .table-secondary>th {
    background-color: #d6d8db
}
#file_manager .table-secondary tbody+tbody,#file_manager .table-secondary td, #file_manager .table-secondary th, #file_manager .table-secondary thead th {
    border-color: #b3b7bb
}
#file_manager .table-hover .table-secondary:hover {
    background-color: #c8cbcf
}
#file_manager .table-hover .table-secondary:hover>td,#file_manager  .table-hover .table-secondary:hover>th {
    background-color: #c8cbcf
}
#file_manager .table-success, #file_manager .table-success>td, #file_manager .table-success>th {
    background-color: #c3e6cb
}
#file_manager .table-success tbody+tbody, #file_manager .table-success td, #file_manager .table-success th, #file_manager .table-success thead th {
    border-color: #8fd19e
}
#file_manager .table-hover .table-success:hover {
    background-color: #b1dfbb
}
#file_manager .table-hover .table-success:hover>td,#file_manager  .table-hover .table-success:hover>th {
    background-color: #b1dfbb
}
#file_manager .table-info,#file_manager  .table-info>td,#file_manager  .table-info>th {
    background-color: #bee5eb
}
#file_manager .table-info tbody+tbody, #file_manager .table-info td,#file_manager  .table-info th, #file_manager .table-info thead th {
    border-color: #86cfda
}
#file_manager .table-hover .table-info:hover {
    background-color: #abdde5
}
#file_manager .table-hover .table-info:hover>td,#file_manager  .table-hover .table-info:hover>th {
    background-color: #abdde5
}
#file_manager .table-warning,#file_manager  .table-warning>td,#file_manager  .table-warning>th {
    background-color: #ffeeba
}
#file_manager .table-warning tbody+tbody,#file_manager  .table-warning td,#file_manager  .table-warning th,#file_manager .table-warning thead th {
    border-color: #ffdf7e
}
#file_manager .table-hover .table-warning:hover {
    background-color: #ffe8a1
}
#file_manager .table-hover .table-warning:hover>td,#file_manager  .table-hover .table-warning:hover>th {
    background-color: #ffe8a1
}
#file_manager .table-danger,#file_manager  .table-danger>td,#file_manager  .table-danger>th {
    background-color: #f5c6cb
}
#file_manager .table-danger tbody+tbody,#file_manager  .table-danger td,#file_manager  .table-danger th,#file_manager .table-danger thead th {
    border-color: #ed969e
}
#file_manager .table-hover .table-danger:hover {
    background-color: #f1b0b7
}
#file_manager .table-hover .table-danger:hover>td,#file_manager  .table-hover .table-danger:hover>th {
    background-color: #f1b0b7
}
#file_manager .table-light,#file_manager  .table-light>td,#file_manager  .table-light>th {
    background-color: #fdfdfe
}
#file_manager .table-light tbody+tbody,#file_manager  .table-light td,#file_manager  .table-light th,#file_manager .table-light thead th {
    border-color: #fbfcfc
}
#file_manager .table-hover .table-light:hover {
    background-color: #ececf6
}
#file_manager .table-hover .table-light:hover>td,#file_manager  .table-hover .table-light:hover>th {
    background-color: #ececf6
}
#file_manager .table-dark,#file_manager  .table-dark>td,#file_manager  .table-dark>th {
    background-color: #c6c8ca
}
#file_manager .table-dark tbody+tbody,#file_manager  .table-dark td,#file_manager  .table-dark th,#file_manager  .table-dark thead th {
    border-color: #95999c
}
#file_manager .table-hover .table-dark:hover {
    background-color: #b9bbbe
}
#file_manager .table-hover .table-dark:hover>td,#file_manager  .table-hover .table-dark:hover>th {
    background-color: #b9bbbe
}
#file_manager .table-active,#file_manager  .table-active>td,#file_manager  .table-active>th {
    background-color: rgba(0, 0, 0, .075)
}
#file_manager .table-hover .table-active:hover {
    background-color: rgba(0, 0, 0, .075)
}
#file_manager .table-hover .table-active:hover>td,#file_manager  .table-hover .table-active:hover>th {
    background-color: rgba(0, 0, 0, .075)
}
#file_manager .table .thead-dark th {
    color: #fff;
    background-color: #343a40;
    border-color: #454d55
}
#file_manager .table .thead-light th {
    color: #495057;
    background-color: #e9ecef;
    border-color: #dee2e6
}
#file_manager .table-dark {
    color: #fff;
    background-color: #343a40
}
#file_manager .table-dark td,#file_manager  .table-dark th,#file_manager  .table-dark thead th {
    border-color: #454d55
}
#file_manager .table-dark.table-bordered {
    border: 0
}
#file_manager .table-dark.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(255, 255, 255, .05)
}
#file_manager .table-dark.table-hover tbody tr:hover {
    color: #fff;
    background-color: rgba(255, 255, 255, .075)
}
</style>
<div class="form-group col-xs-12">
    <div id="file_manager" style="height: 700px;">
        <div id="fm">
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('vendor/file-manager/js/file-manager.js') }}">
</script>
@endsection
@section('footer')
@endsection
