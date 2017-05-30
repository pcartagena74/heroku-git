<?php
/**
 * Comment: Template for pages without authentication
 * Created: 2/2/2017
 */
//<script src='https://www.google.com/recaptcha/api.js'></script>
?>
<!DOCTYPE html>
<html lang="en">
@include('v1.parts.header_no-auth_no-nav')
<style>
    body {
        overflow-y: 0 auto;
    }
    footer {
        background-color: #fff;
        position: fixed;
        padding: 10px;
        top: 100vh;
        margin-top: -50px;
        margin-right: 50px;
        width: 100%; height: 100px;
    }
</style>
</head>
<body class="nav-md footer_fixed">
<nav class="col-md-12 col-sm-12 col-xs-12 navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="col-md-4 col-sm-4 col-xs-12" style="vertical-align: top;">
            <a class="navbar-brand" href="/"><img style="height: 25px; vertical-align: top;" src="/images/mCentric_logo.png" alt="m|Centric"/></a>
        </div>
    </div>
</nav>
<div class="top_content">
    <div class="mainimage">
        <img src="/images/main.jpg" alt="" style="height: 350px; width:100%;"/>
        <div class="overlay">
            <div class='console-container'><span id='text'></span>
                <div class='console-underscore' id='console'>&#95;</div>
            </div>
        </div>
    </div>
</div>
<div class="container body col-md-12 col-sm-12 col-xs-12">
    <div class="main_container bit">

    @yield('content')

    @include('v1.parts.footer_script')
    @yield('scripts')
    @yield('modals')
    </div>
</div>
@include('v1.parts.footer')
</body>
</html>