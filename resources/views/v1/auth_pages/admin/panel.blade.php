@php
    /**
     * Comment:
     * Created: 10/8/20
     *
     * @var $currentPerson
     * @var $currentOrg
     *
     */

    $topBits = '';  // remove this if this was set in the controller
    $header = trans('messages.admin.api.api_props');

    $org_props = $currentOrg->admin_props;

@endphp

@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
    @if(Entrust::can('event-management') || Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
        <div id="el">

            <h2>@lang('messages.nav.ad_panel')</h2>
            @foreach($groups as $group)

                @include('v1.parts.start_content', ['header' => trans($group->name), 'subheader' => '', 'w1' => '12', 'w2' => '12', 'r1' => 1, 'r2' => 0, 'r3' => 0])
                @include($group->viewname, ['org_admin_props' => $org_props->where('groupID', $group->groupID)])
                @include('v1.parts.end_content')

            @endforeach

        </div>
    @endif

@endsection

@section('scripts')
    <script src="//cdn.jsdelivr.net/npm/sortablejs@1.8.4/Sortable.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/Vue.Draggable/2.19.2/vuedraggable.umd.min.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>

    <script>

        Vue.component('blah', {
            //
        })

        new Vue({
            el: '#el',
            data: {
                admin_props: @json($admin_props_json),
                chars: '{{ $org_props[7]->value }}',
                separator: '{{ $org_props[0]->value }}',
                header: '{{ $org_props[1]->value }}',
                choices: [],
                drag: false,
                checkbox_list: [
                    {sort: 0, checked: 0, value: "@lang('messages.fields.category')", id: "category", instr: '@lang('messages.admin.api.api_info_cat')'},
                    {sort: 0, checked: 0, value: "{!! trans_choice('messages.headers.et', 1) !!}", id: "et", instr: '@lang('messages.admin.api.api_info_et')'},
                    {sort: 0, checked: 0, value: "@lang('messages.admin.api.api_times')", id: "times"},
                    {sort: 0, checked: 0, value: "@lang('messages.headers.pdu_detail')", id: "pdus"},
                    {sort: 0, checked: 0, value: "@lang('messages.fields.memprice')", id: "memprice"},
                    {sort: 0, checked: 0, value: "@lang('messages.fields.nonprice')", id: "nonprice"},
                ],
                // enabled: true,
            },
            computed: {
            },

            mounted: function () {
                if(this.admin_props[1].value && this.admin_props[1].value.length > 0) {
                    this.choices = this.admin_props[1].value.split(' ' + this.separator + ' ',);
                    this.varArray_update();
                }
            },

            watch: {
                choices: function () {
                    this.admin_props[1].value = this.choices.join(' ' + this.separator + ' ');
                    this.api_update('header', this.admin_props[1].value);
                    this.varArray_update();
                },

                separator: function () {
                    this.admin_props[1].value = this.choices.join(' ' + this.separator + ' ');
                    this.api_update('header', this.admin_props[1].value);
                    this.varArray_update();
                }
            },

            methods: {
                onSubmit() {
                },

                api_update: function (name, value) {

                    axios.post('/panel/update', {
                        name: name,
                        value: value,
                    })
                        .catch(error => console.log(error.response));
                },

                varArray_update: function() {
                    var index = 0;
                    this.checkbox_list.forEach(e => {
                        e.checked = 0;
                        e.sort = 0;
                    });

                    this.choices.forEach(element => {
                        index += 1;
                        var x = this.checkbox_list.filter(function(ele) {
                            if(ele.value == element) {
                                ele.checked = 1;
                                ele.sort = index;
                            }
                        });
                    });
                    var out = [];
                    var array_copy = this.checkbox_list;
                    array_copy = array_copy.filter(function(v){ return v.checked == 1; });
                    array_copy.sort((a, b) => (a.sort > b.sort) ? 1 : -1);
                    array_copy.forEach(function(v) { out.push(v.id); });
                    var id_hdr = out.join(this.separator);
                    this.api_update('var_array', id_hdr);
                },
            }
        });
    </script>
@endsection

@section('footer')
@endsection

@section('modals')
    @include('v1.modals.dynamic', ['header' => trans('messages.admin.api.example'), 'url' => "eventlist"])
@endsection