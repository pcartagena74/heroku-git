@php
    /**
     * Comment:
     * Created: 10/8/20
     *
     * @var $currentPerson
     * @var $currentOrg
     *
     */

//@include('v1.auth_pages.admin.panels.event_api')

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
                admin_props: @json($admin_props),
                separator: '{{ $org_props[0]->value }}',
                header: '{{ $org_props[1]->value }}',
                choices: [],
                enabled: true,
                drag: false,
            },
            computed: {
                draggingInfo() {
                    return this.drag ? "under drag" : "";
                },
            },

            methods: {
                onSubmit() {
                    console.log('submit btn clicked')
                },

                onUpdate: function (event) {
                    this.choices.splice(event.newIndex, 0, this.choices.splice(event.oldIndex, 1)[0]);
                    org_props[1].value = choices.join(' '+ separator + ' ');
                },

                api_update: function(name, value) {
                    {{--
                    console.log(name);
                    console.log(value);
                    --}}
                    axios.post('/panel/update', {
                        name: name,
                        value: value,
                    })
                        //.then(console.log('Update happened'))
                        .catch(error => console.log(error.response));
                }
            }
        });
    </script>
@endsection

@section('footer')
@endsection
