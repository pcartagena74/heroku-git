<?php
/**
 * Comment: Navigation bar along left
 * Created: 2/2/2017
 */

// Need to determine the # of organizations to which the user belongs.  Done in login
// Also need to determine the level of access the user has.             Also in login
// Then need to see if we need to show the org_context_switch page      org_count from login
// Lastly, need to modify the navigation based on above.                Down below.
// Need to repeat steps 1 & 2 in the org_context_switch page (tbd)

//$isAdmin = $_SESSION['isAdmin'];
//$adminLevel = $_SESSION['adminLevel'];
//$org_count = $_SESSION['org_count'];

$currentPerson = App\Person::find(auth()->user()->id);
$currentOrg    = $currentPerson->defaultOrg;

$options = array('validate_all' => true); // , 'return_type' => 'both');

try{
    $x = getimagesize($currentPerson->avatarURL);
} catch (Exception $exception) {
    $currentPerson->avatarURL = null;
    $currentPerson->save();
}
?>

<div class="col-md-3 left_col menu_fixed">
    <div class="left_col scroll-view">

        <div class="navbar nav_title" style="border: 0;">
            <a href="{{ env('APP_URL') }}/dashboard" class="site_title"><span>{{ $currentOrg->orgName }}</span></a>
        </div>

        <div class="profile">
            <div class="profile_pic">
                <img src="{{ $currentPerson->avatarURL or '/images/user.png' }}" alt="user avatar" class="img-circle profile_img" width="56" height="56">
            </div>
            <div class="profile_info">
                <span>@lang('messages.nav.welcome'), </span>
                <h2>{{ $currentPerson->prefName or $currentPerson->firstName }}</h2>
            </div>
        </div>

        <br>
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">

            <div class="menu_section">
                <h3>&nbsp;</h3>
                <ul class="nav side-menu">
                    <li><a><i class="far fa-fw fa-home"></i> @lang('messages.nav.my_set') <span class="far fa-pull-right fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
{{--
        The piece about multiple organizations would go here...
--}}
                            @if (0)
                                <li><a href="{{ env('APP_URL') }}/orgs">@lang('messages.nav.ms_org')</a></li>
                            @endif
                            <li><a href="{{ env('APP_URL') }}/dashboard">@lang('messages.nav.ms_dash')</a></li>
                            <li><a href="{{ env('APP_URL') }}/upcoming">@lang('messages.nav.ms_fut')</a></li>
                            <li><a href="{{ env('APP_URL') }}/profile/my">@lang('messages.nav.ms_profile')</a></li>
                            @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                            <li><a href="{{ env('APP_URL') }}/become">@lang('messages.nav.ms_become')</a></li>
                            @endif
                        </ul>
                    </li>

                    @if(Entrust::hasRole('Developer'))
                        <li><a><i class="far fa-fw fa-ticket-alt"></i> @lang('messages.nav.help')<span
                                        class="far fa-pull-right fa-chevron-down"></span></a>
                            <ul class="nav child_menu">
                                <li><a href="{{ env('APP_URL') }}/tickets-admin">@lang('messages.nav.h_dash')</a></li>
                                <li><a href="{{ env('APP_URL') }}/tickets">@lang('messages.nav.h_active')</a></li>
                                <li><a href="{{ env('APP_URL') }}/tickets/create">@lang('messages.nav.h_tkt')</a></li>
                                <li><a href="{{ env('APP_URL') }}/reports">@lang('messages.nav.h_rpt')</a></li>
                            </ul>
                        </li>
                    @endif

                    @if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('settings-management'))
                        || Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                        <li><a><i class="far fa-fw fa-university"></i> @lang('messages.nav.org_set')<span
                                        class="far fa-pull-right fa-chevron-down"></span></a>
                            <ul class="nav child_menu">
                                <li><a href="{{ env('APP_URL') }}/orgsettings">@lang('messages.nav.o_labels')</a></li>
                                <li><a href="{{ env('APP_URL') }}/eventdefaults">@lang('messages.nav.o_defaults')</a></li>
                                @if(Entrust::hasRole('Developer'))
                                <li><a href="{{ env('APP_URL') }}/load_data">@lang('messages.nav.o_upload')</a></li>
                                @endif
                                @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                                <li><a href="{{ env('APP_URL') }}/role_mgmt">@lang('messages.nav.o_roles')</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('event-management'))
                        || Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                        <li><a><i class="far fa-fw fa-calendar-alt"></i> @lang('messages.nav.ev_mgmt')<span
                                        class="far fa-pull-right fa-chevron-down"></span></a>
                            <ul class="nav child_menu">
                                <li><a href="{{ env('APP_URL') }}/events">@lang('messages.nav.ev_manage')</a></li>
                                <li><a href="{{ env('APP_URL') }}/locations">@lang('messages.nav.ev_loc')</a></li>
                                <li><a id="add" href="{{ env('APP_URL') }}/event/create">@lang('messages.nav.ev_add')</a></li>
                                <li><a id="grp" href="{{ env('APP_URL') }}/group">@lang('messages.nav.ev_grp')</a></li>
                            </ul>
                        </li>
                    @endif

                    @if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('speaker-management'))
                        || Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                        <li><a><i class="far fa-fw fa-microphone"></i> @lang('messages.nav.spk_mgmt')<span
                                        class="far fa-pull-right fa-chevron-down"></span></a>
                            <ul class="nav child_menu">
                                <li><a href="{{ env('APP_URL') }}/speakers">@lang('messages.nav.s_list')</a></li>
                                <li><a href="{{ env('APP_URL') }}#">@lang('messages.nav.s_new') <span
                                                class="label label-success pull-right">Define It</span></a></li>
                            </ul>
                        </li>
                    @endif

                    @if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('member-management'))
                        || Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                        <li><a><i class="far fa-fw fa-user"></i> @lang('messages.nav.mbr_mgmt')<span class="far fa-pull-right fa-chevron-down"></span></a>
                            <ul class="nav child_menu">
                                <li><a href="{{ env('APP_URL') }}/members" id="mem">@lang('messages.nav.ev_manage')</a></li>
                                @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                                <li><a href="{{ env('APP_URL') }}/merge/p">@lang('messages.nav.m_merge') <span
                                                class="label label-success pull-right">New</span></a></li>
                                @endif
                                <li><a href="{{ env('APP_URL') }}/mbrreport">@lang('messages.nav.m_rpt')</a>
                                    <span class="label label-success pull-right">NEW</span>
                                </li>
                                @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                                    <li><a href="{{ env('APP_URL') }}/force">@lang('messages.nav.m_pass')</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if(Entrust::hasRole('Developer'))
                        <li><a><i class="far fa-fw fa-envelope"></i> @lang('messages.nav.em_mktg')<span
                                        class="far fa-pull-right fa-chevron-down"></span></a>
                            <ul class="nav child_menu">
                                <li><a href="{{ env('APP_URL') }}/campaigns">@lang('messages.nav.e_camp')</a></li>
                                <li><a href="{{ env('APP_URL') }}/library">@lang('messages.nav.e_asset')</a></li>
                                <li><a href="{{ env('APP_URL') }}/lists">@lang('messages.nav.e_list')</a></li>
                                <li><a href="{{ env('APP_URL') }}/reports">@lang('messages.nav.e_rpt')</a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="sidebar-footer hidden-small">
            <a data-toggle="tooltip" data-placement="top" title="{{ trans('messages.nav.coming') }}: {{ trans('messages.nav.c_set') }}">
                <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
            </a>
            <a data-toggle="tooltip" data-placement="top" title="{{ trans('messages.nav.coming') }}: {{ trans('messages.nav.c_full') }}">
                <span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
            </a>
            <a data-toggle="tooltip" data-placement="top" title="{{ trans('messages.nav.coming') }}: {{ trans('messages.nav.c_lock') }}">
                <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
            </a>
            <a href="{{ env('APP_URL') }}/logout" data-toggle="tooltip" data-placement="top" title="{{ trans('messages.nav.c_log') }}">
                <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
            </a>
        </div>
    </div>
</div>
