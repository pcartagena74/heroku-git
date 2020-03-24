<?php
/**
 * Comment:
 * Created: 2/2/2017
 */
?>
@php
/*
<style>
    .main_container .right_col  > *:nth-last-child(2):not(footer)  {
        margin-bottom: 25px;
        clear: both;
    }
    .footer_fixed footer {
        left: initial;
        width: auto;
        right: 0;
        z-index: 999;
    }
    .language-button {
        right: 0;
        top: 12px;
        z-index: 999;
        min-width: 110px;
        
    }
    .dropdown-toggle {
        float: right;
        margin: 0;
    }
    .language-button .dropdown-menu {
        top: calc(-100% + -28px);
        min-width: initial;
    }
    
    @if(Auth::check())
    .footer__logo {
        margin-top: 20px;
        margin-left: 5px;
    }
    footer {
        background: transparent;    
    } 
    @else
    footer {
        position: static;
        width: 100%;
        margin: 0;
        background: transparent;
    }
    footer::after {
        content: ' ';
        display: block;
        clear: both;
    }
    .footer-logo {
        margin: 0;
    }
    @endif
</style>

<footer>
    <div class="pull-right footer-logo">
        @if(!Auth::check())
        <a href="{{ env('APP_URL') }}">
            <img alt="mCentric" class="footer__logo" src="{{ env('APP_URL') }}/images/mCentric_logo_blue.png" style="height: 25px;"/>
        </a>
        @else
        <img alt="mCentric" class="footer__logo" src="{{ env('APP_URL') }}/images/mCentric_logo_blue.png" style="height: 25px;"/>
        @endif
    </div>
     @if(Entrust::hasRole('Developer') && false)
    <div class="pull-right dropdown language-button">
        <button aria-expanded="true" aria-haspopup="true" class="btn btn-default dropdown-toggle" data-toggle="dropdown" id="dropdownMenu1" type="button">
            <i class="far fa-globe"></i> ({{session('locale')}})
            <span class="caret">
            </span>
        </button>
        <ul class="dropdown-menu">
            <li>
                <a href="{{url('setlocale/en')}}" class="bold">
                    <span>
                        🇺🇸
                    </span>
                    English
                </a>
            </li>
            <li>
                <a href="{{url('setlocale/es')}}">
                    <span>
                        🇪🇸
                    </span>
                    Español
                </a>
            </li>
        </ul>
    </div>
    @endif
</footer>

*/
@endphp

<p>&nbsp;</p>
<footer>
    <div class="pull-right">
        @if(!Auth::check())
            <a href="{{ env('APP_URL') }}"><img src="{{ env('APP_URL') }}/images/mCentric_logo_blue.png" alt="mCentric" style="height: 25px;" /></a>
        @else
            <img src="{{ env('APP_URL') }}/images/mCentric_logo_blue.png" hspace="50" alt="mCentric" style="height: 25px;" />
        @endif
    </div>
</footer>