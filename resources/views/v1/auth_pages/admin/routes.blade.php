@php
/**
 * Comment: A prettier way to view php artisan routes:list
 *          from: https://gist.github.com/mtvbrianking/cadac63ad4bc21aa126dfe1fb42e0d86
 * Created: 8/22/2021
 */

@endphp

@extends('layouts.app')

@section('extra-css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/css/dataTables.bootstrap4.min.css" />
    <style type="text/css">
        .card-header h4.title {
            margin-bottom: 0;
        }
        td label {
            margin-bottom: 0;
        }
    </style>
@endsection

@push('extra-js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.19/js/dataTables.bootstrap4.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#tbl_routes').DataTable({
                pageLength: 10,
                language: {
                    emptyTable: "No routes available",
                    info: "Showing _START_ to _END_ of _TOTAL_ routes",
                    infoEmpty: "Showing 0 to 0 of 0 routes",
                    infoFiltered: "(filtered from _MAX_ total routes)",
                    lengthMenu: "Show _MENU_ routes",
                    search: "Search routes:",
                    zeroRecords: "No routes match search criteria"
                },
                order: [
                    [
                        1,
                        'asc'
                    ],
                ]
            });
        });
    </script>
@endpush

@section('content')

    <div class="container-fluid">
        <nav aria-label="breadcrumb" role="navigation">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Routes</li>
            </ol>
        </nav>

        <div class="row justify-content-center">

            <div class="col-lg-12">
                <div class="card">

                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm-3">
                                <h4 class="title">Routes</h4>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <table id="tbl_routes" class="table table-sm">
                                    <thead>
                                    <tr>
                                        <th>Method</th>
                                        <th>URI</th>
                                        <th>Name</th>
                                        <th>Action</th>
                                        <th>Middleware</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($routes as $route)
                                        <tr>
                                            <td class="d-i-f">
                                                @foreach ($route['methods'] as $method)
                                                    @if($method == "GET" || $method == "HEAD")
                                                        <label class="badge badge-success">{{ $method }}</label>
                                                    @elseif($method == "PUT" || $method == "PATCH")
                                                        <label class="badge badge-info">{{ $method }}</label>
                                                    @elseif($method == "POST")
                                                        <label class="badge badge-warning">{{ $method }}</label>
                                                    @elseif($method == "DELETE")
                                                        <label class="badge badge-danger">{{ $method }}</label>
                                                    @endif
                                                @endforeach
                                            </td>
                                            <td>
                                                {{ $route['uri'] }}
                                            </td>
                                            <td>
                                                {{ $route['name'] }}
                                            </td>
                                            <td>
                                                {{ $route['action'] }}
                                            </td>
                                            <td>
                                                {{ $route['middleware'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection