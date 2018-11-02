<?php
/**
 * Comment: Navigation bar across top-right
 * Created: 2/2/2017
 *
 * May need to revisit once sessions and globals are decided post laravel authorization
 */

$currentPerson = App\Person::find(auth()->user()->id);

try{
    $x = getimagesize($currentPerson->avatarURL);
} catch (Exception $exception) {
    $currentPerson->avatarURL = null;
    $currentPerson->save();
}
?>
<div class="top_nav">
    <div class="nav_menu">
        <nav>
            <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
            </div>

            <ul class="nav navbar-nav navbar-right">
                <li class="">
                    <a href="#" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <img src="{{ $currentPerson->avatarURL or '/images/user.png' }}" alt="user avatar" width="29" height="29">
                        {{ $currentPerson->prefName or $currentPerson->firstName }}
                        <span class=" fa fa-angle-down"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-usermenu pull-right">
                        <li><a href="/profile/my"> @lang('messages.nav.ms_profile')</a></li>

                            <li>
                                <a href="/profile/linkedin">
                                    @if($currentPerson->avatarURL !== null)
                                        <span class="badge bg-success pull-right">&nbsp;</span>
                                    @else
                                        <span class="badge bg-red pull-right">&nbsp;</span>
                                    @endif
                                    <span>@lang('messages.nav.con_link')</span>
                                </a>
                            </li>
                        <li><a href="/logout"><i class="fa fa-sign-out pull-right"></i> @lang('messages.nav.c_log')</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</div>
