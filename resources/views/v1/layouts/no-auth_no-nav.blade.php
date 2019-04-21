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
        width: 100%;
        height: 100px;
    }
</style>
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
<nav class="col-md-12 col-sm-12 col-xs-12 navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="col-md-4 col-sm-4 col-xs-12" style="vertical-align: top;">
            <a class="navbar-brand" href="/"><img style="height: 25px; vertical-align: top;"
                                                  src="{{ env('APP_URL') }}/images/mCentric_logo.png"
                                                  alt="mCentric"/></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse col-md-6 col-sm-6 col-xs-12"
             style="display:table-cell; vertical-align:top">
            <form class="navbar-form navbar-right" method="post" action="{{ env('APP_URL') }}/login" id="login-form">
                {{ csrf_field() }}
                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                    <input type="email" placeholder="Email Address" class="form-control input-sm" name="email"
                           id="user_email"
                           value="{{ old('email') }}" required autofocus>
                    @if ($errors->has('email'))
                        <span class="help-block"><strong>{{ $errors->first('email') }}</strong></span>
                    @endif
                </div>
                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    @if ($errors->has('password'))
                        <span class="help-block"><strong>{{ $errors->first('password') }}</strong></span>
                    @endif
                    <input type="password" placeholder="Password" class="form-control input-sm" name="password"
                           id="password" required>
                </div>
                <button type="submit" class="btn btn-success" name="btn-login" id="btn-login">@lang('messages.buttons.login')</button>
                <div class="form-group">
                    <div class="col-md-1 col-md-offset-4">
                        <div class="checkbox">
                            <label style="color: white;">
                                <nobr><input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                    &nbsp; @lang('messages.auth.remember')
                                </nobr>
                                <br/>
                                <a style="color: white;" href="/password/reset">@lang('messages.auth.forgot')</a>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div><!--/.navbar-collapse -->
        <div class="col-md-12 col-sm-12 col-xs-12 navbar-inverse"><span id="err"></span></div>
    </div>
</nav>

    <div class="top_content">
        <div class="mainimage">
            <img src="{{ env('APP_URL') }}/images/main.jpg" alt="" style="height: 350px; width:100%;"/>
            <div class="overlay">
                <div class='console-container'><span id='text'></span>
                    @if(!Agent::isMobile())
                    <div class='console-underscore' id='console'>&#95;</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

<div class="container body col-md-12 col-sm-12 col-xs-12">
    <div class="main_container bit">

        @yield('content')

        @include('v1.parts.footer_public_scripts')
        @yield('scripts')
        @if(Agent::isMobile())
        @else
            <script>
                // function([string1, string2],target id,[color1,color2])
                consoleText(['{{ trans('messages.public_marketing.main.mktg') }}', '{{ trans('messages.public_marketing.main.mail') }}',
                             '{{ trans('messages.public_marketing.main.meet') }}', '{{ trans('messages.public_marketing.main.imm') }}'],
                             'text', ['black', 'black', 'black']);

                function consoleText(words, id, colors) {
                    if (colors === undefined) colors = ['black'];
                    var visible = true;
                    var con = document.getElementById('console');
                    var letterCount = 1;
                    var x = 1;
                    var waiting = false;
                    var target = document.getElementById(id)
                    target.setAttribute('style', 'color:' + colors[0])
                    window.setInterval(function () {

                        if (letterCount === 0 && waiting === false) {
                            waiting = true;
                            target.innerHTML = words[0].substring(0, letterCount)
                            window.setTimeout(function () {
                                var usedColor = colors.shift();
                                colors.push(usedColor);
                                var usedWord = words.shift();
                                words.push(usedWord);
                                x = 1;
                                target.setAttribute('style', 'color:' + colors[0])
                                letterCount += x;
                                waiting = false;
                            }, 1500)
                        } else if (letterCount === words[0].length + 1 && waiting === false) {
                            waiting = true;
                            window.setTimeout(function () {
                                x = -1;
                                letterCount += x;
                                waiting = false;
                            }, 1000)
                        } else if (waiting === false) {
                            if (x === -1) {
                                target.innerHTML = '';
                                letterCount = 0;
                            } else {
                                target.innerHTML = words[0].substring(0, letterCount)
                                letterCount += x;
                            }
                        }
                    }, 60)
                    window.setInterval(function () {
                        if (visible === true) {
                            con.className = 'console-underscore hidden'
                            visible = false;

                        } else {
                            con.className = 'console-underscore'
                            visible = true;
                        }
                    }, 2000)
                }
            </script>
        @endif
        @yield('modals')
    </div>
</div>
@include('v1.parts.footer')
</body>
</html>