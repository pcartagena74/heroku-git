<?php
/**
 * Comment:
 * Created: 2/2/2017
 */
?>
<style>
    .language-button {
        /*position: absolute;*/
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
    @else
    footer {
        position: static;
        width: 100%;
        margin: 0;
    }
    footer::after {
        content: ' ';
        display: block;
        clear: both;
    }
    .footer-logo {
        margin: 18px 0 15px 5px;
    }
    @endif
</style>
<p>
</p>
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
     @if(Entrust::hasRole('Developer'))
    <div class="pull-right dropdown language-button">
        <button aria-expanded="true" aria-haspopup="true" class="btn btn-default dropdown-toggle" data-toggle="dropdown" id="dropdownMenu1" type="button">
            Launguage ({{session('locale')}})
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