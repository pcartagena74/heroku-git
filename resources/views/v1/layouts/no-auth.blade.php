<?php
/**
 * Comment: Template for pages without authentication
 * Created: 2/2/2017
 */
?>
<!DOCTYPE html>
<html lang="en">
@include('v1.parts.header')
<body class="nav-md footer_fixed">
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