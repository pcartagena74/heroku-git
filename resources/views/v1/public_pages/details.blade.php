<?php
/**
 * Comment: Privacy and Return Policy page
 * Created: 3/18/2017
 */
?>
@extends('v1.layouts.no-auth_no-nav')

@section('content')

    <div class="col-md-offset-1 col-sm-offset-1 col-md-10 col-sm-10 col-xs-10" style="padding-top: 20px;">
        <div class="container">
            <p>You can read more about mCentric features available to chapters.</p>
            <ul class="nav nav-tabs">
                <li><a href="{{ env('APP_URL') }}">Home</a></li>
                <li class="{{ Route::is('mktg')?'active':'' }}"><a data-toggle="tab" href="#marketing">Marketing</a></li>
                <li class="{{ Route::is('mail')?'active':'' }}"><a data-toggle="tab" href="#mailings">Mailings</a></li>
                <li class="{{ Route::is('mtgs')?'active':'' }}"><a data-toggle="tab" href="#meetings">Meetings</a></li>
                <li><a data-toggle="tab" href="#membership">Integrated Membership Management</a></li>
            </ul>

            <div class="tab-content">
                <div id="marketing" class="tab-pane fade in{{ Route::is('mktg')?' active':'' }}">
                    <h2><span class="fas fa-tachometer-alt"></span> mCentric-Facilitated Marketing</h2>
                    mCentric facilitates the following activities through the data it analyzes on your behalf:
                    <br />
                    <br />
                    <ul>
                        <li><b><i style="color: black;" class="far fa-newspaper"></i> Subscription Management:</b>
                            For as many lists as you maintain, members have the option of subscribing based on interest
                        </li>
                        <br />
                        <li><b><i style="color: red;" class="far fa-envelope-open-text"></i> Scheduled Notifications:</b>
                            Setup automatic notifications based on what matters most to your chapter
                        </li>
                        <br />
                        <li><b><i style="color: blue;" class="fas fa-list-ul"></i> Segmentation:</b>
                            Create lists based traits or factors important to your chapter
                        </li>
                        <br />
                    </ul>
                    <br/>

                </div>
                <div id="mailings" class="tab-pane fade in{{ Route::is('mail')?' active':'' }}">
                    <h2><span class="far fa-envelope-open-text"></span> mCentric-Facilitated Mailings</h2>
                    mCentric facilitates the following activities through the data it analyzes on your behalf:
                    <br />
                    <br />
                    <ul>
                        <li><b><i style="color: green;" class="fas fa-exclamation-triangle"></i> Notifications:</b>
                            Routine notifications can be automatically sent based on actions, timing, etc.
                        </li>
                        <br />
                        <li><b><i style="color: brown;" class="fas fa-list-ul"></i> List Building:</b>
                            Send email to people in your database according to event attendance, membership status, etc.
                        </li>
                    </ul>
                    <br/>

                </div>
                <div id="meetings" class="tab-pane fade in{{ Route::is('mtgs')?' active':'' }}">
                    <h2><span class="far fa-calendar-alt"></span> mCentric-Facilitated Meeting &amp; Event Management</h2>
                    mCentric facilitates the following activities through the data it analyzes on your behalf:
                    <br />
                    <br/>

                    <ul>
                        <li>
                            <b><i style="color: rebeccapurple;" class="far fa-ticket-alt"></i> Complex Ticketing Pricing:</b>
                            <dd>
                                Create tickets with member, non-member, early bird, or other complex pricing;<br/>
                                or bundle tickets together as necessary for particular events
                            </dd>
                        </li>
                        <br />

                        <li>
                            <b><i style="color: blue;" class="fas fa-credit-card"></i> Payment Processing:</b>
                            <dd>
                                Process payments with credit cards or allow attendees to pay at the door with cash or
                                check
                            </dd>
                        </li>
                        <br />
                        <li>
                            <b><i style="color: red;" class="fas fa-chart-bar"></i> Real-Time Reporting:</b>
                            <dd>
                                Reporting that provides access to registration and session details
                            </dd>
                        </li>
                    </ul>
                    <br/>
                </div>
                <div id="membership" class="tab-pane fade">
                    <h2><span class="fas fa-users"></span> mCentric-Facilitated Membership Management</h2>
                    mCentric facilitates the following activities through the data it analyzes on your behalf:
                    <br />
                    <br />
                    <ul>
                        <li>
                            <b><i style="color: purple;" class="fas fa-chart-bar"></i> Financial Reporting:</b>
                            <dd>
                                Track and manage event finances
                            </dd>
                        </li>
                        <br />
                        <li>
                            <b><i style="color: red;" class="fas fa-chart-pie"></i> Engagement Reporting:</b>
                            <dd>
                                Understand how engaged your members are and increase retention
                            </dd>
                        </li>
                        <br />
                        <li>
                            <b><i style="color: brown;" class="fas fa-users"></i> Contact Management:</b>
                            <dd>
                                Track, manage, and connect members and non-members alike
                            </dd>
                        </li>
                        <br />
                    </ul>
                    <br/>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
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
                    }, 1500);
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
            }, 2000);
        }
    </script>
@endsection
