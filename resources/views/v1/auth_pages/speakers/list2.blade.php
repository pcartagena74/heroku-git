@php
    /**
     * Comment: Vuejs view to display partially-editable Speaker data
     * Created: 7/25/2021
     *
     * @var $speakers
     */

    $topBits = '';
    $header = ['ID', trans('messages.fields.firstName'), trans('messages.fields.lastName'),
        trans('messages.fields.email'), trans_choice('messages.headers.events', 2), trans('messages.fields.buttons')];
    $data = [];

    dd($speakers)
@endphp

@extends('v1.layouts.auth')

@section('content')
    @if(Entrust::can('speaker-management') || Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
        <div id="el">
            <h2> @lang('messages.nav.s_list') </h2>


            <template v-slot:header-r>
                Total rows: @{{ rows.length }}
            </template>
            </vue-editable-grid>
        </div>
    @endif
@endsection

{{--
<script src="https://cdn.jsdelivr.net/npm/vue-grid2-editable@0.2.7/dist/vue-grid.min.js"></script>
--}}

@section('scripts')

    <script nonce="{{ $cspScriptNonce }}">
        // Vue.component('vue-editable-grid', VueEditableGrid)

        new Vue({
            el: '#el',
            data: {
                speakers: @json($speakers),

            },


        });
    </script>
@endsection
