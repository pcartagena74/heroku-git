<?php
/**
 * Comment:
 * Created: 2/9/2017
 */


$headers = ['#', 'Name', 'PMI ID', 'PMI Classification', 'Company', 'Title', 'Industry', 'Expiration', 'Buttons'];

?>
@extends('v1.layouts.auth', ['topBits' => $topBits])

@section('content')
    <div class="col-md-12 col-sm-12 col-xs-12">
        <ul id="myTab" class="nav nav-tabs bar_tabs nav-justified" role="tablist">
            <li class="active"><a href="#tab_content1" id="member_tab" data-toggle="tab"
                                  aria-expanded="true"><b>Report: Members Only</b></a></li>
            <li class=""><a href="#tab_content2" id="everyone_tab" data-toggle="tab"
                            aria-expanded="false"><b>Report: All People</b></a></li>
{{--
            @if(Entrust::hasRole('Speaker'))
                <li class=""><a href="#tab_content3" id="other-tab" data-toggle="tab"
                                aria-expanded="false"><b>Third Thing</b></a></li>
            @endif
--}}
        </ul>

        <div id="tab-content" class="tab-content">
            <div class="tab-pane active" id="tab_content1" aria-labelledby="member_tab">
                &nbsp;<br/>
                @include('v1.parts.start_content', ['header' => 'Percentage Bar Graph of Attendance', 'subheader' => '',
                         'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])
                Thinking about whether this is a monthly count of attendance (for all events) or whatever...
                @include('v1.parts.end_content')

                @include('v1.parts.start_content', ['header' => 'Industry Breakdown', 'subheader' => '',
                         'w1' => '6', 'w2' => '12', 'r1' => 0, 'r2' => 0, 'r3' => 0])A
                Pie Chart of Industries
                @include('v1.parts.end_content')


            </div>
            <div class="tab-pane fade" id="tab_content2" aria-labelledby="everyone_tab">
                <br />
            </div>
        </div>
    </div>


@endsection

@section('scripts')
{{--
    @include('v1.parts.footer-datatable')
    <script>
        $(document).ready(function() {
            $('#member_table').DataTable({
                "fixedHeader": true,
                "order": [[ 0, "asc" ]]
            });
        });
    </script>
--}}
@endsection