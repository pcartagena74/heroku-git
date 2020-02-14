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
<!DOCTYPE html>
@include('v1.parts.header')
@yield('header')
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-K4MGRCX');</script>
<!-- End Google Tag Manager -->
</head>
<body class="nav-md footer_fixed">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K4MGRCX"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<div class="container body">
    <div class="main_container bit">
        @include('v1.parts.nav-left')
        @include('v1.parts.nav-top')
        <div class="right_col" role="main">
            @if(Session::has('become'))
                @include('v1.parts.become_notice')
            @endif
                @include('v1.parts.error')
            @if($topBits)
                <div class="row tile_count hidden-xs hidden-sm">
                    @foreach($topBits as $tdata)
                        @if(count($tdata)>6)
                            @include('v1.parts.title-bit', ['icon' => $tdata[0], 'label' => $tdata[1],
                            'number' => $tdata[2], 'ctext'=> $tdata[3], 'rtext' => $tdata[4], 'up' => $tdata[5], 'width' => $tdata[6]])
                        @else
                            @include('v1.parts.title-bit', ['icon' => $tdata[0], 'label' => $tdata[1],
                            'number' => $tdata[2], 'ctext'=> $tdata[3], 'rtext' => $tdata[4], 'up' => $tdata[5]])
                        @endif
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