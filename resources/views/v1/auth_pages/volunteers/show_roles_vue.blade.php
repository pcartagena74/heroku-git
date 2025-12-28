@php
    /**
     * Comment: Page to show the Chapter's roles (VueJS version)
     * Created: 10/9/21
     *
     * @var $json_roles
     * @var $roles
     */

    $topBits = '';  // remove this if this was set in the controller
    $header = '';

//$x = getimagesize($currentPerson->avatarURL);

@endphp

@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('header')
    <link href="/css/jquery.orgchart.css" media="all" rel="stylesheet" type="text/css"/>
    <style nonce="{{ $cspStyleNonce }}">
        #app {
            font-family: 'Avenir', Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-align: center;
            color: #2c3e50;
            margin-top: 60px;
        }

        html, body {
            width: 100%;
            height: 100%;
            padding: 0;
            margin: 0;
            overflow: hidden;
            font-family: Helvetica;
        }

        #tree {
            width: 100%;
            height: 100%;
        }
    </style>
@endsection

@section('content')
    <div id="app">
        <OrgChart/>
    </div>
@endsection

@section('scripts')
    <script src="/js/orgchart.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script type="text/javascript">

        require('/js.orgchart.js');
        require('https://cdn.jsdelivr.net/npm/vue/dist/vue.js');

        export default {
            name: 'app',
            components: {
                OrgChart,
            }
        }

        new Vue({
            el: '#app',
            data() {
                return {
                    nodes: [
                        {!! $json_roles !!}
                    ]
                }
            },
            computed: {},

            mounted: function () {
            },

            watch: {},

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


            }
        });


    </script>
@endsection

@section('modals')
    @include('v1.modals.dynamic', ['header' => trans('messages.admin.api.example'), 'url' => "eventlist"])
@endsection
