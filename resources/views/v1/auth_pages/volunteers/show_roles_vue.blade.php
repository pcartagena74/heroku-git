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

@endphp

@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('header')
<link href="css/jquery.orgchart.css" media="all" rel="stylesheet" type="text/css" />
<style type="text/css">
#orgChart{
    width: auto;
    height: auto;
}

p {
    font-size: 10px;
}

#orgChartContainer{
    width: 1000px;
    height: 500px;
    overflow: auto;
    background: #eeeeee;
}
    </style>
@endsection

@section('content')
    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif
    <div style="width:100%; height:700px;" id="orgchart"/>
@endsection

@section('scripts')
    <script type="text/javascript" src="/js/orgchart.js"></script>
    <script type="text/javascript">

    var data = {!! $json_roles !!};
    //console.log(data);

    var chart = new OrgChart(document.getElementById("orgchart"), {
        template: 'diva',
        enableDragDrop: true,
        nodeMenu:{
            details: {text:"{{ trans('messages.default_roles.det') }}"},
            edit: {text:"{{ trans('messages.default_roles.edit') }}"},
            add: {text:"{{ trans('messages.default_roles.add') }}"},
            remove: {text:"{{ trans('messages.default_roles.rem') }}"}
        },
        nodeBinding: {
            field_0: "{{ trans('messages.fields.name') }}",
            field_1: "{{ trans('messages.fields.title') }}",
        },
        nodes: data,
    });

    chart.on('add', function (sender, node) {
        node.id = new Date().valueOf();
        node.pid = parseInt(node.pid);
        //node.jd_URL = parseInt(node.pid);
        console.log(node);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type:'POST',
            url:"{{ route('nodes.store') }}",
            data: node,
            success:function(data){
                sender.addNode(node); // node is adding
            }
        });
        return false;
    });

    chart.on('edit', function (sender, node) {
        console.log(node);

    });

    chart.on('remove', function (sender, nodeId) {

        var url = "{{URL('volunteers')}}";
        var dltUrl = url+"/"+nodeId;

        $.ajax({
            url: dltUrl,
            type: "DELETE",
            cache: false,
            data:{
                _token:'{{ csrf_token() }}'
            },
            success: function(dataResult){
                var dataResult = JSON.parse(dataResult);
                if(dataResult.statusCode==200){
                    $ele.fadeOut().remove();
                }
            }
        });

    });

    // just for example purpose
    function log(text){
        $('#consoleOutput').append('<p>'+text+'</p>')
    }
    </script>
@endsection