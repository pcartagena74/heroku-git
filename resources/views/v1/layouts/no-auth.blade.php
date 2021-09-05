@php
/**
 * Comment: Template for pages without authentication
 * Created: 2/2/2017
 */
//<script src='https://www.google.com/recaptcha/api.js'>
@endphp
<!DOCTYPE html>
<html lang="en">
    @include('v1.parts.header_no-auth')
<body class="nav-md footer_fixed">
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe height="0" src="https://www.googletagmanager.com/ns.html?id=GTM-K4MGRCX" style="display:none;visibility:hidden" width="0">
        </iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
    <nav class="col-md-12 col-sm-12 col-xs-12 navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <div class="col-md-4 col-sm-4 col-xs-10" style="vertical-align: top;">
                <a class="navbar-brand" href="/">
                    <img alt="mCentric" src="{{ env('APP_URL') }}/images/mCentric_logo.png" style="height: 25px; vertical-align: top;"/>
                </a>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-2 pull-right" style="vertical-align: top;">
                @include('v1.parts.locale',['member'=>false])
            </div>
        </div>
    </nav>
    <div class="container body col-md-12 col-sm-12 col-xs-12">
        <div class="main_container bit">
            @include('v1.parts.error',['no_auth'=>true])

    @yield('content')

    @include('v1.parts.footer_script')
    @yield('scripts')
    @include('v1.parts.footer')
    @yield('modals')
        </div>
    </div>
</body>
</html>