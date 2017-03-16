<?php
/**
 * Comment: Template for pages without authentication
 * Created: 2/2/2017
 */
//<script src='https://www.google.com/recaptcha/api.js'></script>
?>
<!DOCTYPE html>
<html lang="en">
@include('v1.parts.header')
</head>
<body class="nav-md footer_fixed">
<div class="container body col-md-12 col-sm-12 col-xs-12">
    <div class="main_container bit">

    @yield('content')

    @include('v1.parts.footer_script')
    @include('v1.parts.footer')
    @yield('scripts')
    @yield('modals')
    </div>
</div>
</body>
</html>