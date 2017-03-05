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
    <link rel="icon" href="../../favicon.ico">

    <title>m|Centric</title>

    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link href="//maxcdn.bootstrapcdn.com/bootswatch/3.3.7/cerulean/bootstrap.min.css" rel="stylesheet">
    <?php // <link href="//maxcdn.bootstrapcdn.com/bootswatch/3.3.7/sandstone/bootstrap.min.css" rel="stylesheet"> ?>
    <link href="//maxcdn.bootstrapcdn.com/css/ie10-viewport-bug-workaround.css" rel="stylesheet">
    <link href="/css/jumbotron.css" rel="stylesheet">
    <script src="/js/ie-emulation-modes-warning.js"></script>
    <!--[if lt IE 9]>
    <script src="js/html5shiv.min.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<nav class="col-md-12 col-sm-12 col-xs-12 navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header col-md-6 col-sm-6 col-xs-12" style="vertical-align: top;;">
            <a class="navbar-brand" href="#"><img style="height: 25px; vertical-align: top;" src="/images/mCentric_logo.png" alt="m|Centric" /></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse col-md-6 col-sm-6 col-xs-12">
            <form class="navbar-form navbar-right" method="post" action="{{ url('/login') }}" id="login-form">
                {{ csrf_field() }}
                <div class="form-group">
                    <input type="email" placeholder="Email" class="form-control input-sm" name="login" id="user_email" value="{{ old('login') }}" required autofocus>
                </div>
                <div class="form-group">
                    <input type="password" placeholder="Password" class="form-control input-sm" name="password" id="password" required>
                </div>
                <button type="submit" class="btn btn-success" name="btn-login" id="btn-login">Login</button>
                <div class="form-group">
                    <div class="col-md-1 col-md-offset-4">
                        <div class="checkbox">
                            <label style="color: white;">
                                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}><nobr>Remember Me</nobr>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div><!--/.navbar-collapse -->
        <div class="col-md-12 col-sm-12 col-xs-12 navbar-inverse"><span id="err"></span></div>
    </div>
</nav>

<div class="jumbotron">
    <div class="container mainimage">
        <?php //  <img src="/images/main.jpg" alt="" style="height: auto; width:100%;" /> ?>
        <div class="overlay">
            <h2>Integrated Membership Management</h2>
            <p>A bunch of words...</p>
            <p><a class="btn btn-primary btn-sm" href="#" role="button">Learn more &raquo;</a></p>
        </div>
    </div>
</div>

<div class="container">
    <!-- Example row of columns -->
    <div class="row">
        <div class="col-md-4">
            <h2>Selling Point #1</h2>
            <p>Stuff... </p>
            <p><a class="btn btn-default" href="#" role="button">View details &raquo;</a></p>
        </div>
        <div class="col-md-4">
            <h2>Selling Point #2</h2>
            <p>Stuff...  </p>
            <p><a class="btn btn-default" href="#" role="button">View details &raquo;</a></p>
        </div>
        <div class="col-md-4">
            <h2>Selling Point #3</h2>
            <p>Stuff...  </p>
            <p><a class="btn btn-default" href="#" role="button">View details &raquo;</a></p>
        </div>
    </div>

    <hr>

    <footer>
        <p>&copy; 2017 Member Centric </p>
    </footer>
</div> <!-- /container -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"><\/script>')</script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="//cdn.jsdelivr.net/bootbox/4.4.0/bootbox.min.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="/js/ie10-viewport-bug-workaround.js"></script>
<script src="/js/mmmm_loginscript.js"></script>
</body>
</html>