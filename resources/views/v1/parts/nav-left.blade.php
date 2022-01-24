@php
/**
 * Comment: Navigation bar along left
 * Created: 2/2/2017
 */

// Need to determine the # of organizations to which the user belongs.  Done in login
// Also need to determine the level of access the user has.             Also in login
// Then need to see if we need to show the org_context_switch page      org_count from login
// Lastly, need to modify the navigation based on above.                Down below.
// Need to repeat steps 1 & 2 in the org_context_switch page (tbd)
// 
// 
// removed Entrust('orgname') condition check as roles are now associated with Org id its not needed.


//$isAdmin = $_SESSION['isAdmin'];
//$adminLevel = $_SESSION['adminLevel'];
//$org_count = $_SESSION['org_count'];

try {
    $currentPerson = App\Models\Person::find(auth()->user()->id);
    $currentOrg    = $currentPerson->defaultOrg;
} catch(Exception $e) {
    request()->session()->flash('alert-warning', trans('messages.errors.timeout'));
    return redirect()->route('home');
}

// This is an Entrust option array that is used when invoking $user->ability(x, y, z)
// $options is the implied z parameter in above.
$options = array('validate_all' => true); // , 'return_type' => 'both');

//  LinkedIN image access seems to "timeout" after 2-3 months.
//  This prevents the display of a broken image

if($currentPerson->avatarURL !== null){
    try{
        $x = getimagesize($currentPerson->avatarURL);
    } catch (Exception $exception) {
        $currentPerson->avatarURL = null;
        $currentPerson->save();
    }
}


@endphp
<div class="col-md-3 left_col menu_fixed">
    <div class="left_col scroll-view">
        <div class="navbar nav_title" style="border: 0;">
            <a class="site_title" href="{{ url('dashboard')}}"> <span> {{ $currentOrg->orgName }} </span> </a>
        </div>
        <div class="profile">
            <div class="profile_pic">
                <img alt="{{ trans('messages.alt_txt.avatar') }}" class="img-circle profile_img" height="56" src="{{ $currentPerson->avatarURL ?? '/images/user.png' }}" width="56" />
            </div>
            <div class="profile_info">
                <span> @lang('messages.nav.welcome'), </span>
                <h2> {{ $currentPerson->prefName ?? $currentPerson->firstName }} </h2>
            </div>
        </div>
        <br />
        <div class="main_menu_side hidden-print main_menu" id="sidebar-menu">
            <div class="menu_section">

                <ul class="nav side-menu">
                    <li> <a class="clear"> <i class="far fa-fw fa-home"></i> @lang('messages.nav.my_set')
                            <span class="far fa-pull-right fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                            <li> <a href="{{ url('dashboard')}}"> @lang('messages.nav.ms_dash') </a> </li>
                            <li> <a href="{{ url('upcoming')}}"> @lang('messages.nav.ms_fut') </a> </li>
                            <li> <a href="{{ url('profile/my')}}"> @lang('messages.nav.ms_profile') </a> </li>
                            @if(Entrust::hasRole("Developer") ||
                                (null !== $currentPerson->service_role && count($currentPerson->has_volunteers()) > 0))
                            <li> <a href="{{ url("volunteers/$currentPerson->defaultOrgID")}}">
                                    @lang('messages.nav.ms_vol')
                                </a>
                            </li>
                            @endif
                            @if (count($currentPerson->orgs)>1)
                                <li> <a href="{{ url('orgs/my')}}"> @lang('messages.nav.ms_org') </a> </li>
                            @endif
                            @if(showActiveTicketUser())
                                <li> <a href="{{ url('tickets') }}">
                                    @php
                                    $unread_ticket = getActiveTicketCountUser();
                                    @endphp
                                    @lang('messages.nav.active_issue')
                                    @if($unread_ticket > 0)
                                        <span class="badge bg-green"> {{$unread_ticket}} </span>
                                    @endif
                                    </a>
                                </li>
                            @endif
                            @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                            @endif
                        </ul>
                    </li>
                    @if(((Entrust::hasRole('Admin') || Entrust::can('settings-management')))
                    || Entrust::hasRole('Developer'))
                    <li>
                        <a> <i class="fas fa-fw fa-lock-alt"></i> @lang('messages.nav.admin')
                            <span class="far fa-pull-right fa-chevron-down"></span> </a>
                        <ul class="nav child_menu">
                            <li> <a href="{{ url('/')}}/become"> @lang('messages.nav.ms_become') </a> </li>
                            <li> <a href="{{ url('/')}}/newuser/create"> @lang('messages.nav.ad_new') </a> </li>
                            <li> <a href="{{ url('/')}}/role_mgmt"> @lang('messages.nav.o_roles') </a> </li>

                            @if(Entrust::hasRole('Developer'))
                            <li> <a href="{{ url('create_organization')}}">@lang('messages.nav.ad_new_org')</a></li>
                            <li> <a href="{{ url('/')}}/panel">@lang('messages.nav.ad_panel')</a> </li>
                            <li> <a href="{{ url('/')}}/load_data"> @lang('messages.nav.o_upload') </a> </li>
                            @endif
                        </ul>
                    </li>
                    @endif

                @if( ((Entrust::hasRole('Board')|| Entrust::hasRole('Admin') ||
                      Entrust::can('event-management') || Entrust::can('settings-management')))
                    || Entrust::hasRole('Developer'))
                    <li>
                        <a> <i class="far fa-fw fa-university"></i> @lang('messages.nav.org_set')
                            <span class="far fa-pull-right fa-chevron-down"></span></a>

                        <ul class="nav child_menu">
                            @if(Entrust::can('settings-management'))
                            <li><a href="{{ url('orgsettings',$currentOrg)}}">@lang('messages.nav.o_labels')</a></li>
                            @endif

                            @if(Entrust::can('event-management'))
                            <li> <a href="{{ url('eventdefaults')}}">@lang('messages.nav.o_defaults')</a> </li>
                            @endif
                            @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                            @endif
                        </ul>
                    </li>
                    @endif

                @if(((Entrust::hasRole('Board')|| Entrust::can('event-management')))
                    || Entrust::hasRole('Developer'))
                    <li>
                        <a> <i class="far fa-fw fa-calendar-alt"></i> @lang('messages.nav.ev_mgmt')
                            <span class="far fa-pull-right fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                            <li> <a href="{{ url('manage_events')}}">@lang('messages.nav.ev_manage')</a></li>
                            <li> <a href="{{ url('event/create')}}" id="add">@lang('messages.nav.ev_add')</a></li>
                            <li> <a href="{{ url('group')}}" id="grp"> @lang('messages.nav.ev_grp') </a> </li>
                            <li> <a href="{{ url('eventstats')}}">@lang('messages.nav.ev_stats')</a></li>
                            <li> <a href="{{ url('locations')}}">@lang('messages.nav.ev_loc')</a></li>
                            <li> <a href="{{ url('manage_events/past')}}">@lang('messages.nav.ev_old')</a></li>
                        </ul>
                    </li>
                    @endif

                @if(((Entrust::hasRole('Board')|| Entrust::can('member-management')))
                    || Entrust::hasRole('Developer'))
                    <li> <a> <i class="far fa-fw fa-user"></i> @lang('messages.nav.mbr_mgmt')
                            <span class="far fa-pull-right fa-chevron-down"></span> </a>
                        <ul class="nav child_menu">
                            <li> <a href="{{ url('search')}}"> @lang('messages.nav.m_sch') </a> </li>
                            @if(Entrust::hasRole("Developer"))
                            <li> <a href="{{ url('volunteers')}}"> @lang('messages.nav.m_vol') </a> </li>
                            @endif
                            <li> <a href="{{ url('membership')}}"> @lang('messages.nav.m_new_or_exp') </a> </li>

                            @if(Entrust::hasRole('Developer') || Entrust::hasRole('Admin'))
                            <li> <a href="{{ url('merge/p')}}"> @lang('messages.nav.m_merge')
                                    <span class="label label-danger pull-right">@lang('messages.nav.b_admin')</span></a>
                            </li>
                            @endif
                            <li> <a href="{{ url('members')}}" id="mem"> @lang('messages.nav.m_list')
                                    <span class="label label-warning pull-right">@lang('messages.nav.b_slow')</span></a></li>
                            <li> <a href="{{ url('mbrreport')}}"> @lang('messages.nav.m_rpt')
                                    <span class="label label-success pull-right"> NEW </span> </a> </li>
                            {{--
                            @if(Entrust::hasRole('Deleted') || Entrust::hasRole('Deleted'))
                            <li> <a href="{{ url('force')}}"> @lang('messages.nav.m_pass') </a> </li>
                            @endif
                            --}}
                        </ul>
                    </li>
                    @endif

                @if(((Entrust::hasRole('Board') ||
                        Entrust::can('event-management') || Entrust::can('speaker-management')))
                    || Entrust::hasRole('Developer'))
                    <li>
                        <a><i class="far fa-fw fa-microphone"></i> @lang('messages.nav.spk_mgmt')
                            <span class="far fa-pull-right fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                            <li> <a href="{{ url('speakers')}}"> @lang('messages.nav.s_list') </a> </li>
                            <li> <a href="{{ url('/')}}#"> @lang('messages.nav.s_new')
                                    <span class="label label-success pull-right"> Define It </span></a> </li>
                        </ul>
                    </li>
                    @endif

                @if(Entrust::hasRole('Developer') || Entrust::hasRole('Marketing'))
                    <li> <a><i class="far fa-fw fa-envelope"></i> @lang('messages.nav.em_mktg')
                            <span class="far fa-pull-right fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                            <li> <a href="{{ url('campaigns')}}"> @lang('messages.nav.e_camp') </a> </li>
                            <li> <a href="{{ url('library')}}"> @lang('messages.nav.e_asset') </a> </li>
                            <li> <a href="{{ url('lists')}}"> @lang('messages.nav.e_list') </a> </li>
                            <li> <a href="{{ url('reports')}}"> @lang('messages.nav.e_rpt') </a> </li>
                        </ul>
                    </li>
                    @endif
                    <li style="display: none">
                        <a href="{{ route('tickets.my-tickets')}}"><i class="fas fa-fw fa-ticket-alt"></i>
                            @lang('messages.nav.my_support') <span class="far fa-pull-right"></span></a>
                    </li>
                    @if(App\Models\Ticketit\AgentOver::isAdmin() || App\Models\Ticketit\AgentOver::isAgent())
                    <li class="">
                        <a><i class="far fa-fw fa-ticket-alt"></i>
                            @lang('messages.nav.help') <span class="far fa-pull-right fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                            @if((Entrust::hasRole('Admin') || Entrust::hasRole('Developer')) || auth()->id() == 1)
                            <li> <a href="{{ url('tickets-admin')}}"> @lang('messages.nav.h_dash') </a> </li>
                            @endif
                            <li>
                                <a href="{{ url('tickets')}}"> @lang('messages.nav.h_active')
                                    @php
                                        $ticket_count = getActiveTicketCountAgent();
                                    @endphp
                                    @if($ticket_count > 0)
                                        <span class="badge bg-green"> {{$ticket_count }} </span>
                                    @endif
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="sidebar-footer hidden-small">
            <a data-placement="top" data-toggle="tooltip" title="{{ trans('messages.nav.coming') }}: {{ trans('messages.nav.c_set') }}">
                <span aria-hidden="true" class="glyphicon glyphicon-cog">
                </span>
            </a>
            <a data-placement="top" data-toggle="tooltip" title="{{ trans('messages.nav.coming') }}: {{ trans('messages.nav.c_full') }}">
                <span aria-hidden="true" class="glyphicon glyphicon-fullscreen">
                </span>
            </a>
            <a data-placement="top" data-toggle="tooltip" title="{{ trans('messages.nav.coming') }}: {{ trans('messages.nav.c_lock') }}">
                <span aria-hidden="true" class="glyphicon glyphicon-eye-close">
                </span>
            </a>
            <a data-placement="top" data-toggle="tooltip" href="{{ url('/')}}/logout" title="{{ trans('messages.nav.c_log') }}">
                <span aria-hidden="true" class="glyphicon glyphicon-off">
                </span>
            </a>
        </div>
        <br />
    </div>
</div>
