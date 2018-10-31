<?php
/**
 * Comment: Template for pages without authentication
 * Created: 2/2/2017
 */
//<script src='https://www.google.com/recaptcha/api.js'></script>
?>
<!DOCTYPE html>
<html lang="en">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
@include('v1.parts.header_simple_no-auth')
</head>
<body class="nav-md footer_fixed">
<nav class="col-md-12 col-sm-12 col-xs-12 navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="col-md-4 col-sm-4 col-xs-12" style="vertical-align: top;">
            <a class="navbar-brand" href="{{ env('APP_URL') }}/"><img style="height: 25px; vertical-align: top;" src="{{ env('APP_URL') }}/images/mCentric_logo.png" alt="m|Centric"/></a>
        </div>
    </div>
</nav>
<div class="container body col-md-12 col-sm-12 col-xs-12">
    <div class="main_container bit">
        @include('v1.parts.error')

    @yield('content')

{{--
    @include('v1.parts.footer_script')
--}}
    @include('v1.parts.footer')
    @yield('scripts')
    @yield('modals')
    </div>
</div>
</body>
</html>