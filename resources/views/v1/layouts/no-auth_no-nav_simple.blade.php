<?php
/**
 * Comment: Template for display API
 * Created: 11/28/2017
 */
?>
<!DOCTYPE html>
<html lang="en">
@include('v1.parts.header_simple_no-auth')
</head>
<body style="background-color: white;">
@include('v1.parts.error')

@yield('content')

{{--
    @include('v1.parts.footer_script')
--}}
@yield('scripts')
@yield('modals')
</body>
</html>