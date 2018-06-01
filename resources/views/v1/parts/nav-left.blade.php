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
{{--
    // small green nav button
    <span class="label label-success pull-right">Coming Soon</span>
--}}
<div class="col-md-3 left_col menu_fixed">
    <div class="left_col scroll-view">

        <div class="navbar nav_title" style="border: 0;">
            <a href="{{ env('APP_URL') }}/dashboard" class="site_title"><span>{{ $currentOrg->orgName }}</span></a>
        </div>

        <div class="profile"><!-- img_2 -->
            <div class="profile_pic">
                <img src="{{ $currentPerson->avatarURL or '/images/user.png' }}" alt="user avatar" class="img-circle profile_img" width="56" height="56">
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
                                <li><a href="{{ env('APP_URL') }}/orgs">My Organizations</a></li>
                            @endif
                            <li><a href="{{ env('APP_URL') }}/dashboard">My Dashboard</a></li>
                            <li><a href="{{ env('APP_URL') }}/upcoming">My Future Events</a></li>
                            <li><a href="{{ env('APP_URL') }}/profile/my">My Profile</a></li>
                            @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                            <li><a href="{{ env('APP_URL') }}/become">Become</a></li>
                            @endif
                        </ul>
                    </li>

                    @if(Entrust::hasRole('Developer'))
                        <li><a><i class="fa fa-ticket"></i>Help Desk<span
                                        class="fa fa-chevron-down"></span></a>
                            <ul class="nav child_menu">
                                <li><a href="{{ env('APP_URL') }}/tickets-admin">Dashboard</a></li>
                                <li><a href="{{ env('APP_URL') }}/tickets">Active Tickets</a></li>
                                <li><a href="{{ env('APP_URL') }}/tickets/create">Create Ticket</a></li>
                                <li><a href="{{ env('APP_URL') }}/reports">Reporting</a></li>
                            </ul>
                        </li>
                    @endif

                    @if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('settings-management'))
                        || Entrust::hasRole('Developer' || Entrust::hasRole('Admin')))
                        <li><a><i class="fa fa-bank"></i>Organization Settings<span
                                        class="fa fa-chevron-down"></span></a>
                            <ul class="nav child_menu">
                                <li><a href="{{ env('APP_URL') }}/orgsettings">Custom Field Labels</a></li>
                                <li><a href="{{ env('APP_URL') }}/eventdefaults">Event Defaults</a></li>
                                @if(Entrust::hasRole('Developer'))
                                <li><a href="{{ env('APP_URL') }}/load_data">Upload Data</a></li>
                                @endif
                                @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                                <li><a href="{{ env('APP_URL') }}/role_mgmt">Roles & Permissions</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('event-management'))
                        || Entrust::hasRole('Developer' || Entrust::hasRole('Admin')))
                        <li><a><i class="fa fa-calendar"></i> Event Management<span
                                        class="fa fa-chevron-down"></span></a>
                            <ul class="nav child_menu">
                                <li><a href="{{ env('APP_URL') }}/events">Manage Events</a></li>
                                <li><a href="{{ env('APP_URL') }}/locations">Location Management</a></li>
                                <li><a id="add" href="{{ env('APP_URL') }}/event/create">Add Event</a></li>
                                <li><a id="grp" href="{{ env('APP_URL') }}/group">Group Registration</a></li>
                            </ul>
                        </li>
                    @endif

                    @if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('speaker-management'))
                        || Entrust::hasRole('Developer' || Entrust::hasRole('Admin')))
                        <li><a><i class="fa fa-microphone"></i> Speaker Management<span
                                        class="fa fa-chevron-down"></span></a>
                            <ul class="nav child_menu">
                                <li><a href="{{ env('APP_URL') }}/speakers">Speakers List</a></li>
                                <li><a href="{{ env('APP_URL') }}#">New Report <span
                                                class="label label-success pull-right">Just Define It</span></a></li>
                            </ul>
                        </li>
                    @endif

                    @if((Entrust::hasRole($currentOrg->orgName) && Entrust::can('member-management'))
                        || Entrust::hasRole('Developer' || Entrust::hasRole('Admin')))
                        <li><a><i class="fa fa-user"></i> Member Management<span class="fa fa-chevron-down"></span></a>
                            <ul class="nav child_menu">
                                <li><a href="{{ env('APP_URL') }}/members" id="mem">Member List</a></li>
                                @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                                <li><a href="{{ env('APP_URL') }}/merge/p">Merge Members <span
                                                class="label label-success pull-right">New</span></a></li>
                                @endif
                                <li><a href="{{ env('APP_URL') }}/mbrreport">Member Report</a>
                                    <span class="label label-success pull-right">NEW</span>
                                </li>
                                @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                                    <li><a href="{{ env('APP_URL') }}/force">Change Password</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if(Entrust::hasRole('Developer'))
                        <li><a><i class="fa fa-envelope"></i>Email Marketing<span
                                        class="fa fa-chevron-down"></span></a>
                            <ul class="nav child_menu">
                                <li><a href="{{ env('APP_URL') }}/campaigns">Campaigns</a></li>
                                <li><a href="{{ env('APP_URL') }}/library">Asset Library</a></li>
                                <li><a href="{{ env('APP_URL') }}/lists">List Maintenance</a></li>
                                <li><a href="{{ env('APP_URL') }}/reports">Reporting</a></li>
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
            <a href="{{ env('APP_URL') }}/logout" data-toggle="tooltip" data-placement="top" title="Logout">
                <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
            </a>
        </div>
    </div>
</div>
