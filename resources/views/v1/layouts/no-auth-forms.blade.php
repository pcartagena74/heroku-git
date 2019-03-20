<?php
/**
 * Comment: Template for pages without authentication
 * Created: 2/2/2017
 */
?>
<!DOCTYPE html>
<html lang="en">
@include('v1.parts.header')
<script src='https://www.google.com/recaptcha/api.js'></script>
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
<div class="container body col-md-12 col-sm-12 col-xs-12">
    <div class="main_container na">

    @yield('content')

    @include('v1.parts.footer_script')
    @include('v1.parts.footer')
    @yield('scripts')
    @yield('modals')
    </div>
</div>
</body>
</html>