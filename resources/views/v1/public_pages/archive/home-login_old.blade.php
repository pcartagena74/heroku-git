<?php
// include in all forms:   {{ csrf_field() }}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Integrated Member Management, Email Marketing, Event Registration, Surveys">
    <meta name="author" content="Efcico Corporation">
    <link rel="icon" href="/favicon.ico">

    <title>mCentric</title>

    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link href="//maxcdn.bootstrapcdn.com/bootswatch/3.3.7/cerulean/bootstrap.min.css" rel="stylesheet">
    <?php // <link href="//maxcdn.bootstrapcdn.com/bootswatch/3.3.7/sandstone/bootstrap.min.css" rel="stylesheet"> ?>
    <link href="//maxcdn.bootstrapcdn.com/css/ie10-viewport-bug-workaround.css" rel="stylesheet">
    <link href="/css/jumbotron.css" rel="stylesheet">
    <link href="/css/mmmm.css" rel="stylesheet">
    <script src="/js/ie-emulation-modes-warning.js"></script>
    <!--[if lt IE 9]>
    <script src="/js/html5shiv.min.js"></script>
    <script src="/js/respond.min.js"></script>
    <![endif]-->
</head>
<body onload="load()">
<nav class="col-md-12 col-sm-12 col-xs-12 navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header col-md-4 col-sm-4 col-xs-12" style="vertical-align: top;;">
            <a class="navbar-brand" href="/"><img style="height: 25px; vertical-align: top;" src="{{ env('APP_URL') }}/images/mCentric_logo.png" alt="mCentric"/></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse col-md-6 col-sm-6 col-xs-12"
             style="display:table-cell; vertical-align:top">
            <form class="navbar-form navbar-right" method="post" action="{{ url('/login') }}" id="login-form">
                {{ csrf_field() }}
                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                    @if ($errors->has('email'))
                    <span class="help-block"><strong>{{ $errors->first('email') }}</strong></span>
                    @endif
                    <input type="email" placeholder="Email Address" class="form-control input-sm" name="email" id="user_email"
                           value="{{ old('email') }}" required autofocus>
                </div>
                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    @if ($errors->has('password'))
                    <span class="help-block"><strong>{{ $errors->first('password') }}</strong></span>
                    @endif
                    <input type="password" placeholder="Password" class="form-control input-sm" name="password" id="password" required>
                </div>
                <button type="submit" class="btn btn-success" name="btn-login" id="btn-login">Login</button>
                <div class="form-group">
                    <div class="col-md-1 col-md-offset-4">
                        <div class="checkbox">
                            <label style="color: white;">
                                <nobr><input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                &nbsp; Remember Me</nobr><br />
                                <a style="color: white;" href="/password/reset">Forgot Password?</a>
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
                <div class='console-underscore' id='console'>&#95;</div>
            </div>
        </div>
    </div>
</div>

<div class="container" style="padding-top: 20px;">
    <h4>mCentric was created with specific customization for organizations like Project Management
        Institute<sup>&reg;</sup> (PMI) chapters in mind.</h4>
    <h4>Whether your organization holds events, needs to coordinate content, or wants to measure member engagement,
        mCentric can help.</h4>
    <div class="row">
        <div class="col-md-4 column_text">
            <h2>Marketing</h2>
            <p>mCentric aggregates the data that you keep about your current and prospective members. Segmenting your
                members based on the traits that are evident in your data allow for better targeting of your
                campaigns.</p>
            <p>Need to see the list of members that are expiring this month?  Next month?</p>
            <p><a class="btn btn-default" href="/details#marketing" role="button">View details &raquo;</a></p>
        </div>
        <div class="col-md-4 column_text">
            <h2>Mailings</h2>
            <p>With better segmentation capability and targeting, use mCentric's integrated email capabilities to
                execute specific campaigns and maintain contact with your constituents. </p>
            <p>Why should maintaining lists of members be so difficult?</p>
            <p><a class="btn btn-default" href="/details#mailings" role="button">View details &raquo;</a></p>
        </div>
        <div class="col-md-4 column_text">
            <h2>Meetings</h2>
            <p>Need to advertise meetings or events regardless of entrance fees? mCentric can help you setup, advertise
                your events, and sell tickets to your events. </p><p>If you're holding a no-fee event and still need these
                services, mCentric will do this for you for no additional charge.</p>
            <p><a class="btn btn-default" href="/details#meetings" role="button">View details &raquo;</a></p>
        </div>
    </div>

    <hr>

    <footer>
        <p>&copy; 2017 mCentric </p>
    </footer>
</div> <!-- /container -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"><\/script>')</script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="//cdn.jsdelivr.net/bootbox/4.4.0/bootbox.min.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="/js/ie10-viewport-bug-workaround.js"></script>
<script src="/js/mmmm_loginscript.js"></script>
<script>
    // function([string1, string2],target id,[color1,color2])
    consoleText(['Marketing', 'Mailings', 'Meetings', 'Integrated Membership Management'], 'text', ['black', 'black', 'black']);

    function consoleText(words, id, colors) {
        if (colors === undefined) colors = ['black'];
        var visible = true;
        var con = document.getElementById('console');
        var letterCount = 1;
        var x = 1;
        var waiting = false;
        var target = document.getElementById(id);
        target.setAttribute('style', 'color:' + colors[0]);
        window.setInterval(function () {

            if (letterCount === 0 && waiting === false) {
                waiting = true;
                target.innerHTML = words[0].substring(0, letterCount);
                window.setTimeout(function () {
                    var usedColor = colors.shift();
                    colors.push(usedColor);
                    var usedWord = words.shift();
                    words.push(usedWord);
                    x = 1;
                    target.setAttribute('style', 'color:' + colors[0]);
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
                    target.innerHTML = words[0].substring(0, letterCount);
                    letterCount += x;
                }
            }
        }, 60);
        window.setInterval(function () {
            if (visible === true) {
                con.className = 'console-underscore hidden';
                visible = false;

            } else {
                con.className = 'console-underscore';
                visible = true;
            }
        }, 2000)
    }
</script>
</body>
</html>