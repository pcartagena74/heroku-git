@extends($master)

@section('page')
    {{ trans('ticketit::lang.index-title') }}
@stop

@section('content')
    @include('ticketit::shared.header')
    @include('ticketit::tickets.index')

@stop

@section('footer')
    <script src="//cdn.datatables.net/v/bs/dt-{{ Kordy\Ticketit\Helpers\Cdn::DataTables }}/r-{{ Kordy\Ticketit\Helpers\Cdn::DataTablesResponsive }}/datatables.min.js"/>
    <script nonce="{{ $cspScriptNonce }}">
        var table = $('.table').DataTable({
            processing: false,
            serverSide: true,
            responsive: true,
            pageLength: {{ $setting->grab('paginate_items') }},
            lengthMenu: {{ json_encode($setting->grab('length_menu')) }},
            ajax: {
                url: '{!! route($setting->grab('main_route').'.data', $complete) !!}',
                data: {
                    'param': $('#filter_owner').val()
                }
            },
            language: {
                decimal: "{{ trans('ticketit::lang.table-decimal') }}",
                emptyTable: "{{ trans('ticketit::lang.table-empty') }}",
                info: "{{ trans('ticketit::lang.table-info') }}",
                infoEmpty: "{{ trans('ticketit::lang.table-info-empty') }}",
                infoFiltered: "{{ trans('ticketit::lang.table-info-filtered') }}",
                infoPostFix: "{{ trans('ticketit::lang.table-info-postfix') }}",
                thousands: "{{ trans('ticketit::lang.table-thousands') }}",
                lengthMenu: "{{ trans('ticketit::lang.table-length-menu') }}",
                loadingRecords: "{{ trans('ticketit::lang.table-loading-results') }}",
                processing: "{{ trans('ticketit::lang.table-processing') }}",
                search: "{{ trans('ticketit::lang.table-search') }}",
                zeroRecords: "{{ trans('ticketit::lang.table-zero-records') }}",
                paginate: {
                    first: "{{ trans('ticketit::lang.table-paginate-first') }}",
                    last: "{{ trans('ticketit::lang.table-paginate-last') }}",
                    next: "{{ trans('ticketit::lang.table-paginate-next') }}",
                    previous: "{{ trans('ticketit::lang.table-paginate-prev') }}"
                },
                aria: {
                    sortAscending: "{{ trans('ticketit::lang.table-aria-sort-asc') }}",
                    sortDescending: "{{ trans('ticketit::lang.table-aria-sort-desc') }}"
                },
            },
            columns: [
                {data: 'id', name: 'ticketit.id'},
                {data: 'subject', name: 'subject'},
                {data: 'status', name: 'ticketit_statuses.name'},
                {data: 'updated_at', name: 'ticketit.updated_at'},
                {data: 'agent', name: 'agent_id'},
                    @if( $u->isAgent() || $u->isAdmin() )
                {
                    data: 'priority', name: 'ticketit_priorities.name'
                },
                {data: 'owner', name: 'user_id'},
                {data: 'category', name: 'ticketit_categories.name'}
                @endif
            ],
        });

        function changeSearch(search_by, ths) {
            var id = '{{auth()->user()->id}}';
            $('#search-button-group .btn').removeClass('active');
            $(ths).addClass('active');
            if (search_by == 'all') {
                table.columns().search('').draw();
            }
            if (search_by == 'created') {
                table.columns(6)
                    .search(id)
                    .columns(4)
                    .search('')
                    .draw();
            }
            if (search_by == 'assigned') {
                table.columns(4)
                    .search(id)
                    .columns(6)
                    .search('')
                    .draw();
            }
        }
    </script>
@append
