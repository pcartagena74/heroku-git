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
            <p>@lang('messages.public_marketing.main.read_more')</p>
            <ul class="nav nav-tabs">
                <li><a href="{{ env('APP_URL') }}">@lang('messages.public_marketing.main.home')</a></li>
                <li class="{{ Route::is('mktg')?'active':'' }}"><a data-toggle="tab"
                                                                   href="#marketing">@lang('messages.public_marketing.main.mktg')</a>
                </li>
                <li class="{{ Route::is('mail')?'active':'' }}"><a data-toggle="tab"
                                                                   href="#mailings">@lang('messages.public_marketing.main.mail')</a>
                </li>
                <li class="{{ Route::is('mtgs')?'active':'' }}"><a data-toggle="tab"
                                                                   href="#meetings">@lang('messages.public_marketing.main.meet')</a>
                </li>
                <li><a data-toggle="tab" href="#membership">@lang('messages.public_marketing.main.imm')</a></li>
            </ul>

            <div class="tab-content">
                <div id="marketing" class="tab-pane fade in{{ Route::is('mktg')?' active':'' }}">
                    <h2>@lang('messages.public_marketing.marketing.title')</h2>
                    @lang('messages.public_marketing.intro_line')
                    <br/>
                    <br/>
                    <ul>
                        <li>
                            @lang('messages.public_marketing.marketing.b1_t')
                            <dd>
                                @lang('messages.public_marketing.marketing.b1')
                            </dd>
                        </li>
                        <br/>
                        <li>
                            @lang('messages.public_marketing.marketing.b2_t')
                            <dd>
                                @lang('messages.public_marketing.marketing.b2')
                            </dd>
                        </li>
                        <br/>
                        <li>
                            @lang('messages.public_marketing.marketing.b3_t')
                            <dd>
                                @lang('messages.public_marketing.marketing.b3')
                            </dd>
                        </li>
                        <br/>
                    </ul>
                    <br/>

                </div>
                <div id="mailings" class="tab-pane fade in{{ Route::is('mail')?' active':'' }}">
                    <h2>@lang('messages.public_marketing.mailings.title')</h2>
                    @lang('messages.public_marketing.intro_line')
                    <br/>
                    <br/>
                    <ul>
                        <li>
                            @lang('messages.public_marketing.mailings.b1_t')
                            <dd>
                                @lang('messages.public_marketing.mailings.b1')
                            </dd>
                        </li>
                        <br/>
                        <li>
                            @lang('messages.public_marketing.mailings.b2_t')
                            <dd>
                                @lang('messages.public_marketing.mailings.b2')
                            </dd>
                        </li>
                    </ul>
                    <br/>

                </div>
                <div id="meetings" class="tab-pane fade in{{ Route::is('mtgs')?' active':'' }}">
                    <h2>@lang('messages.public_marketing.meetings.title')</h2>
                    @lang('messages.public_marketing.intro_line')
                    <br/>
                    <br/>

                    <ul>
                        <li>
                            @lang('messages.public_marketing.meetings.b1_t')
                            <dd>
                                @lang('messages.public_marketing.meetings.b1')
                            </dd>
                        </li>
                        <br/>

                        <li>
                            @lang('messages.public_marketing.meetings.b2_t')
                            <dd>
                                @lang('messages.public_marketing.meetings.b2')
                            </dd>
                        </li>
                        <br/>
                        <li>
                            @lang('messages.public_marketing.meetings.b3_t')
                            <dd>
                                @lang('messages.public_marketing.meetings.b3')
                            </dd>
                        </li>
                    </ul>
                    <br/>
                </div>
                <div id="membership" class="tab-pane fade">
                    <h2>@lang('messages.public_marketing.management.title')</h2>
                    @lang('messages.public_marketing.intro_line')
                    <br/>
                    <br/>
                    <ul>
                        <li>
                            @lang('messages.public_marketing.management.b1_t')
                            <dd>
                                @lang('messages.public_marketing.management.b1')
                            </dd>
                        </li>
                        <br/>
                        <li>
                            @lang('messages.public_marketing.management.b2_t')
                            <dd>
                                @lang('messages.public_marketing.management.b2')
                            </dd>
                        </li>
                        <br/>
                        <li>
                            @lang('messages.public_marketing.management.b3_t')
                            <dd>
                                @lang('messages.public_marketing.management.b3')
                            </dd>
                        </li>
                        <br/>
                    </ul>
                    <br/>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script nonce="{{ $cspScriptNonce }}">
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
