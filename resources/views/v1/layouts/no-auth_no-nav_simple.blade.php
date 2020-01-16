<?php
/**
 * Comment: Template for display API
 * Created: 11/28/2017
 */
?>
<!DOCTYPE html>
<html lang="en">
@include('v1.parts.header_simple_no-auth')
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-K4MGRCX');</script>
<!-- End Google Tag Manager -->
@yield('header')
</head>
<body style="background-color: white;">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-K4MGRCX"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
@include('v1.parts.error')

@yield('content')

{{--
    @include('v1.parts.footer_script')
--}}
@yield('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/iframe-resizer/3.6.1/iframeResizer.contentWindow.min.js"></script>
@yield('modals')
</body>
</html>