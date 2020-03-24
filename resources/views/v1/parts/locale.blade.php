@if(empty($member))
@if(Entrust::hasRole('Developer'))
<div class="pull-left dropdown language-button">
    <a aria-expanded="true" aria-haspopup="true" class="btn btn-default dropdown-toggle" data-toggle="dropdown" id="dropdownMenu1" type="button">
        <i class="far fa-globe">
        </i>
        ({{session('locale')}})
        <span class="caret">
        </span>
    </a>
    <ul class="dropdown-menu">
        <li>
            <a class="bold" href="{{url('setlocale/en')}}">
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
@else
@if(Entrust::hasRole('Developer'))
<li class="">
    <a aria-expanded="false" class="user-profile dropdown-toggle" data-toggle="dropdown" href="javascript:void(0)">
        <i class="far fa-globe">
        </i>
        ({{session('locale')}})
        <span class="caret">
        </span>
    </a>
    <ul class="dropdown-menu dropdown-usermenu">
        <li>
            <a class="bold" href="{{url('setlocale/en')}}">
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
</li>
@endif
                @endif
