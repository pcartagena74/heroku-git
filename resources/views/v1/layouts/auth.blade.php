<?php
/**
 * Comment: Template for pages that require authentication
 * Created: 2/2/2017
 */
// footer_fixed
if(!isset($topBits)){
    $topBits = '';
}
?>
@include('v1.parts.header')
        {{--
@yield('page')
        --}}
</head>
<body class="nav-md footer_fixed">
<div class="container body">
    <div class="main_container">
        @include('v1.parts.nav-left')
        @include('v1.parts.nav-top')
        <div class="right_col" role="main">
            @include('v1.parts.error')
            @if($topBits)
                <div class="row tile_count">
                    @foreach($topBits as $tdata)
                        @include('v1.parts.title-bit', ['icon' => $tdata[0], 'label' => $tdata[1],
                        'number' => $tdata[2], 'ctext'=> $tdata[3], 'rtext' => $tdata[4], 'up' => $tdata[5]])
                    @endforeach
                </div>
            @endif

            @yield('content')

            <p>&nbsp;</p>
            @include('v1.parts.footer')
        </div>
    </div>
</div>
@include('v1.parts.footer_script')
<script>
    $("[data-toggle=tooltip]").tooltip();
</script>
@yield('scripts')
@yield('footer')
@yield('modals')
</body>
</html>