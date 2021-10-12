@php
    /**
     * Comment: Page to show the Chapter's roles
     * Created: 10/4/21
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
    <div id="orgChartContainer">
        <div id="orgChart"></div>
    </div>
    <div id="consoleOutput">
    </div>
@endsection

@section('scripts')
    <script type="text/javascript" src="/js/jquery.orgchart.js"></script>
    <script type="text/javascript">

    var data = {!! $json_roles !!};
    console.log(data);

    $(function(){
        org_chart = $('#orgChart').orgChart({
            data: data,
            showControls: true,
            allowEdit: true,
            newNodeText: '@lang('messages.default_roles.add_child')',
            onAddNode: function(node){ 
                log('Created new node on node '+node.data.id);
                org_chart.newNode(node.data.id); 
            },
            onDeleteNode: function(node){
                log('Deleted node '+node.data.id);
                org_chart.deleteNode(node.data.id); 
            },
            onClickNode: function(node){
                log('Clicked node '+node.data.id);
            }

        });
    });

    // just for example purpose
    function log(text){
        $('#consoleOutput').append('<p>'+text+'</p>')
    }
    </script>
@endsection