@php
    /**
     * Comment: Navigation bar across top-right
     * Created: 2/2/2017
     *
     * May need to revisit once sessions and globals are decided post laravel authorization
     */

    $currentPerson = App\Models\Person::find(auth()->user()->id);

    if($currentPerson->avatarURL !== null){
        try{
            $x = getimagesize($currentPerson->avatarURL);
            $badge_color = "bg-success";
        } catch (Exception $exception) {
            $currentPerson->avatarURL = null;
            $badge_color = "bg-red";
            $currentPerson->save();
        }
    } else {
        $badge_color = "bg-red";
    }
@endphp
<div class="top_nav">
    <div class="nav_menu">
        <nav>
            <div class="nav toggle">
                <a id="menu_toggle">
                    <i class="fa fa-bars">
                    </i>
                </a>
            </div>
            <ul class="nav navbar-nav navbar-right">
                <li class="">
                    <a aria-expanded="false" class="user-profile dropdown-toggle" data-toggle="dropdown" href="#">
                        <img alt="{{ trans('messages.alt_txt.avatar') }}" height="29"
                             src="{{ $currentPerson->avatarURL ?? '/images/user.png' }}" width="29"/>
                        {{ $currentPerson->prefName ?? $currentPerson->firstName }}
                        <span class=" fa fa-angle-down">
                            </span>
                    </a>
                    <ul class="dropdown-menu dropdown-usermenu pull-right">
                        <li>
                            <a href="/profile/my">
                                @lang('messages.nav.ms_profile')
                            </a>
                        </li>
                        <li>
                            <a href="/profile/linkedin">
                                <span class="badge {{ $badge_color }} pull-right">
                                </span>
                                <span data-toggle="tooltip" title="{!! trans('messages.tooltips.linkedIn') !!}">
                                    @lang('messages.nav.con_link')
                                </span>
                            </a>
                        </li>
                        <li>
                            <a data-target="#context_issue" data-toggle="modal" href="#">
                                <i class="fas fa-bug pull-right">
                                </i>
                                @lang('messages.nav.context_issue')
                            </a>
                        </li>
                        <li>
                            <a href="/logout">
                                <i class="fa fa-sign-out pull-right">
                                </i>
                                @lang('messages.nav.c_log')
                            </a>
                        </li>
                    </ul>
                </li>
                @if(Entrust::hasRole('Admin') || Entrust::hasRole('Developer'))
                    <li class="nav-item dropdown">
                        <a aria-expanded="false" class="dropdown-toggle info-number"
                           href="{{route('tickets.my-tickets')}}"
                           id="navbarDropdown1">
                            <i class="far fa-fw fa-ticket-alt" style="white-space:nowrap;">
                            </i>
                            <span class="badge bg-green">
                            {{getActiveTicketCountAgent()}}
                        </span>
                        </a>
                    </li>
                @endif
                @include('v1.parts.locale',['member'=>true])
            </ul>
        </nav>
    </div>
</div>
@section('modals')
    @include('v1.modals.context_sensitive_issue')
@endsection
