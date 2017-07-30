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
?>
<div class="col-md-3 left_col menu_fixed">
    <div class="left_col scroll-view">

        <div class="navbar nav_title" style="border: 0;">
            <a href="/dashboard" class="site_title"><span>{{ $currentOrg->orgName }}</span></a>
        </div>

        <div class="profile"><!-- img_2 -->
            <div class="profile_pic">
                <img src="{{ $currentPerson->avatarURL or '/images/user.png' }}" alt="user avatar" class="img-circle profile_img">
            </div>
            <div class="profile_info">
                <span>Welcome,</span>
                <h2>{{ $currentPerson->prefName or $currentPerson->firstName }}</h2>
            </div>
        </div>

        <br>
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">

            <div class="menu_section">
                <h3>&nbsp;</h3>
                <ul class="nav side-menu">
                    <li><a><i class="fa fa-home"></i> My Settings <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
{{--
        The piece about multiple organizations would go here...
--}}
                            @if (0)
                                <li><a href="/orgs">My Organizations</a></li>
                            @endif
                            <li><a href="/dashboard">My Dashboard</a></li>
                            <li><a href="/upcoming">My Future Events</a></li>
                            <li><a href="/profile/my">My Profile</a></li>
                        </ul>
                    </li>

                    @if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('settings-management'))
                        || Entrust::hasRole('Development'))
                        <li><a><i class="fa fa-edit"></i>Organization Settings<span
                                        class="fa fa-chevron-down"></span></a>
                            <ul class="nav child_menu">
                                <li><a href="/orgsettings">Custom Field Labels</a></li>
                                <li><a href="/eventdefaults">Event Defaults</a></li>
                                <li><a href="/load_data">Upload Data</a></li>
                                <li><a href="/role_mgmt">Roles & Permissions</a></li>
                            </ul>
                        </li>
                    @endif
                    @if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('member-management'))
                        || Entrust::hasRole('Development'))
                        <li><a><i class="fa fa-user"></i> Member Management<span class="fa fa-chevron-down"></span></a>
                            <ul class="nav child_menu">
                                <li><a href="/members" id="mem">Member Management</a></li>
                                <li><a href="/merge/p">Merge Members <span
                                                class="label label-success pull-right">New</span></a></li>
                                <li><a href="#">Something New... <span
                                                class="label label-success pull-right">Coming Soon</span></a></li>
                            </ul>
                        </li>
                    @endif
                    @if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('event-management'))
                        || Entrust::hasRole('Development'))
                        <li><a><i class="fa fa-calendar"></i> Event Management<span
                                        class="fa fa-chevron-down"></span></a>
                            <ul class="nav child_menu">
                                <li><a href="/events">Manage Events</a></li>
                                <li><a href="/locations">Location Management</a></li>
                                <li><a id="add" href="/event/create">Add Event</a></li>
                                <li><a href="#">New Report <span
                                                class="label label-success pull-right">Just Define It</span></a></li>
                            </ul>
                        </li>
                    @endif

                    @if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('speaker-management'))
    || Entrust::hasRole('Development'))
                        <li><a><i class="fa fa-microphone"></i> Speaker Management<span
                                        class="fa fa-chevron-down"></span></a>
                            <ul class="nav child_menu">
                                <li><a href="/speakers">Manage Speakers</a></li>
                                <li><a href="#">New Report <span
                                                class="label label-success pull-right">Just Define It</span></a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="sidebar-footer hidden-small">
            <a data-toggle="tooltip" data-placement="top" title="Coming Soon: Settings">
                <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
            </a>
            <a data-toggle="tooltip" data-placement="top" title="Coming Soon: FullScreen">
                <span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
            </a>
            <a data-toggle="tooltip" data-placement="top" title="Coming Soon: Lock">
                <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
            </a>
            <a href="/logout" data-toggle="tooltip" data-placement="top" title="Logout">
                <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
            </a>
        </div>
    </div>
</div>
