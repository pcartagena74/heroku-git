@php
    /**
     * Comment: Page to show the Chapter's roles
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
    <style type="text/css">
        #orgChart {
            width: auto;
            height: auto;
        }

        p {
            font-size: 10px;
        }

        #orgChartContainer {
            width: 100%;
            height: 500px;
            overflow: auto;
            background: #eeeeee;
        }
    </style>
@endsection

@section('content')
    <div class="col-md-12 col-sm-12 col-xs-12 bg-white">
        <ul id="myTab" class="nav nav-tabs bar_tabs nav-justified" data-tabs="tabs">
            <li class="active">
                <a href="#tab_content1" data-toggle="tab">
                    <b>@lang('messages.default_roles.orgchart')</b>
                </a>
            </li>
            <li class="">
                <a href="#tab_content2" data-toggle="tab">
                    <b>TBD</b>
                    {{--
                    <b>@lang('messages.default_roles.orgdata')</b>
                     --}}
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="tab_content1">
                <br/>
                <h2>@lang('messages.instructions.volunteers')</h2>
                <div style="width:100%; height:700px;" id="orgchart"></div>
                {{-- --}}
            </div>

            <div class="tab-pane" id="tab_content2">
                <br/>
                <h2> Something could be here...  submit a ticket with ideas/suggestions. </h2>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#myTab a[href="#{{ old('tab') }}"]').tab('show')
        });
    </script>
    <script type="text/javascript" src="/js/orgchart.js"></script>
    <script type="text/javascript">

        var data = {!! $json_roles !!};
        var url = "{{ URL('volunteers') }}";
        var options_array = [{!! $option_string !!}];
        //console.log(data);

        var chart = new OrgChart(document.getElementById("orgchart"), {
            mouseScrool: OrgChart.action.ctrlZoom,
            nodeMouseClick: OrgChart.action.edit,
            template: 'diva',
            enableDragDrop: true,
            enableSearch: false,
            editForm: {
                generateElementsFromFields: false,
                titleBinding: "{{ trans('messages.fields.name') }}",
                photoBinding: "img",
                addMoreBtn: '',
                addMore: '',
                addMoreFieldName: '',
                buttons: {
                    share: {
                        text: null,
                        hideIfEditMode: true,
                        hideIfDetailsMode: true,
                    },
                    edit: {
                        icon: OrgChart.icon.edit(24, 24, '#fff'),
                        text: '{{ trans('messages.default_roles.edit') }}',
                        hideIfEditMode: true,
                        hideIfDetailsMode: false
                    },
                    remove: {
                        icon: OrgChart.icon.remove(24, 24, '#fff'),
                        text: '{{ trans('messages.default_roles.remove') }}',
                        hideIfDetailsMode: true
                    },
                },
                elements: [
                    {
                        type: 'select',
                        label: '{{ trans('messages.fields.name') }}',
                        binding: '{{ trans('messages.fields.name') }}',
                        options: [
                            {!! $option_string !!}
                        ]
                    },
                    {
                        type: 'textbox',
                        label: '{{ trans('messages.fields.title') }}',
                        binding: '{{ trans('messages.fields.title') }}',
                    },
                    {
                        type: 'date',
                        label: '{{ trans('messages.default_roles.start') }}',
                        binding: '{{ trans('messages.default_roles.start') }}',
                    },
                    {
                        type: 'date',
                        label: '{{ trans('messages.default_roles.end') }}',
                        binding: '{{ trans('messages.default_roles.end') }}',
                    },
                    {
                        type: 'textbox',
                        label: '{{ trans('messages.default_roles.jd_url') }}',
                        binding: '{{ trans('messages.default_roles.jd_url') }}',
                    },
                ],
            },

            nodeMenu: {
                edit: {text: "{{ trans('messages.default_roles.edit') }}"},
                add: {text: "{{ trans('messages.default_roles.add') }}"},
                remove: {text: "{{ trans('messages.default_roles.rem') }}"}
            },
            nodeBinding: {
                field_0: "{{ trans('messages.fields.name') }}",
                field_1: "{{ trans('messages.fields.title') }}",
                img_0: "img",
            },
            nodes: data,
        });

        chart.on('add', function (sender, node) {
            node.id = new Date().valueOf();
            node.pid = parseInt(node.pid);
            node.jd_URL = parseInt(node.pid);
            console.log(node);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: 'POST',
                url: "{{ route('nodes.store') }}",
                data: node,
                success: function (dataResult) {
                    var dataResult = JSON.parse(dataResult);
                    console.log(dataResult);
                    node.id = dataResult.id;
                    sender.addNode(node); // node is added with returned id
                }
            });
            return false;
        });

        chart.on('update', function (sender, oldnode, node) {
            console.log(oldnode);
            console.log(node);
            var updUrl = url + "/" + node.id + "/update";

            $.ajax({
                url: updUrl,
                type: "PATCH",
                cache: false,
                data: {
                    _token: '{{ csrf_token() }}',
                    newnode: node,
                    oldnode: oldnode,
                },
                success: function (dataResult) {
                    var dataResult = JSON.parse(dataResult);
                    if (dataResult.statusCode == 200) {
                        //$ele.fadeOut().remove();
                        console.log(dataResult);
                    }
                }
            });

        });

        chart.on('remove', function (sender, nodeId) {

            var dltUrl = url + "/" + nodeId;

            $.ajax({
                url: dltUrl,
                type: "DELETE",
                cache: false,
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (dataResult) {
                    var dataResult = JSON.parse(dataResult);
                    if (dataResult.statusCode == 200) {
                        $ele.fadeOut().remove();
                    }
                }
            });

        });

        {{--
        // just for example purpose
        function log(text) {
            $('#consoleOutput').append('<p>' + text + '</p>')
        }
        --}}
    </script>
@endsection
{{--
@section('modals')
    @include('v1.modals.dynamic', ['header' => trans('messages.admin.api.example'), 'url' => "eventlist"])
@endsection
--}}
