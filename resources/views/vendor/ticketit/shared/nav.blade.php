@php

use Kordy\Ticketit\Controllers\StatusesController;
use Kordy\Ticketit\Controllers\PrioritiesController;
use Kordy\Ticketit\Controllers\CategoriesController;
use Kordy\Ticketit\Controllers\AgentsController;
use Kordy\Ticketit\Controllers\ConfigurationsController;
use Kordy\Ticketit\Controllers\AdministratorsController;
use Kordy\Ticketit\Controllers\TicketsController;
use App\Http\TicketitControllers\TicketsControllerOver;
use App\Http\TicketitControllers\DashboardController;

$ori_tickets_path = 'TicketsController::class';
$new_tickets_path = 'App\Http\TicketitControllers\TicketsControllerOver::class';

@endphp
<div class="panel panel-default">
    <div class="panel-body">
        <ul class="nav nav-pills">
            <li role="presentation" class="{!! $tools->fullUrlIs(route(App\Models\Ticketit\SettingOver::grab('main_route') . '.index')) ? "active" : "" !!}">
                <a href="{{ route(App\Models\Ticketit\SettingOver::grab('main_route') . '.index') }}">{{ trans('ticketit::lang.nav-active-tickets') }}
                    <span class="badge">
                         @php
                            if ($u->isAdmin()) {
                                echo App\Models\Ticketit\TicketOver::active()->count();
                            } elseif ($u->isAgent()) {
                                echo App\Models\Ticketit\TicketOver::active()->agentUserTickets($u->id)->count();
                            } else {
                                echo App\Models\Ticketit\TicketOver::userTickets($u->id)->active()->count();
                            }
                        @endphp
                    </span>
                </a>
            </li>
            <li role="presentation" class="{!! $tools->fullUrlIs(route('tickets-complete')) ? "active" : "" !!}">
                <a href="{{ route('tickets-complete') }}">{{ trans('ticketit::lang.nav-completed-tickets') }}
                    <span class="badge">
                        @php
                            if ($u->isAdmin()) {
                                echo App\Models\Ticketit\TicketOver::complete()->count();
                            } elseif ($u->isAgent()) {
                                echo App\Models\Ticketit\TicketOver::complete()->agentUserTickets($u->id)->count();
                            } else {
                                echo App\Models\Ticketit\TicketOver::userTickets($u->id)->complete()->count();
                            }
                        @endphp
                    </span>
                </a>
            </li>

            @if($u->isAdmin())
                <li role="presentation" class="{!! $tools->fullUrlIs(action([DashboardController::class, 'index'])) || Request::is($setting->grab('admin_route').'/indicator*') ? "active" : "" !!}">
                    <a href="{{ action([DashboardController::class, 'index']) }}">{{ trans('ticketit::admin.nav-dashboard') }}</a>
                </li>

            @if(Entrust::hasRole('Developer'))
                <li role="presentation" class="dropdown {!!
                    $tools->fullUrlIs(action([\Kordy\Ticketit\Controllers\StatusesController::class, 'index']).'*') ||
                    $tools->fullUrlIs(action([\Kordy\Ticketit\Controllers\PrioritiesController::class, 'index']).'*') ||
                    $tools->fullUrlIs(action([\Kordy\Ticketit\Controllers\AgentsController::class, 'index']).'*') ||
                    $tools->fullUrlIs(action([\Kordy\Ticketit\Controllers\CategoriesController::class, 'index']).'*') ||
                    $tools->fullUrlIs(action([\Kordy\Ticketit\Controllers\ConfigurationsController::class, 'index']).'*') ||
                    $tools->fullUrlIs(action([\Kordy\Ticketit\Controllers\AdministratorsController::class, 'index']).'*')
                    ? "active" : "" !!}">

                    <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        {{ trans('ticketit::admin.nav-settings') }} <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li role="presentation" class="{!! $tools->fullUrlIs(action([\Kordy\Ticketit\Controllers\StatusesController::class, 'index']).'*') ? "active" : "" !!}">
                            <a href="{{ action([\Kordy\Ticketit\Controllers\StatusesController::class, 'index']) }}">{{ trans('ticketit::admin.nav-statuses') }}</a>
                        </li>
                        <li role="presentation"  class="{!! $tools->fullUrlIs(action([\Kordy\Ticketit\Controllers\PrioritiesController::class, 'index']).'*') ? "active" : "" !!}">
                            <a href="{{ action([\Kordy\Ticketit\Controllers\PrioritiesController::class, 'index']) }}">{{ trans('ticketit::admin.nav-priorities') }}</a>
                        </li>
                        <li role="presentation"  class="{!! $tools->fullUrlIs(action([\Kordy\Ticketit\Controllers\AgentsController::class, 'index']).'*') ? "active" : "" !!}">
                            <a href="{{ action([\Kordy\Ticketit\Controllers\AgentsController::class, 'index']) }}">{{ trans('ticketit::admin.nav-agents') }}</a>
                        </li>
                        <li role="presentation"  class="{!! $tools->fullUrlIs(action([\Kordy\Ticketit\Controllers\CategoriesController::class, 'index']).'*') ? "active" : "" !!}">
                            <a href="{{ action([\Kordy\Ticketit\Controllers\CategoriesController::class, 'index']) }}">{{ trans('ticketit::admin.nav-categories') }}</a>
                        </li>
                        <li role="presentation"  class="{!! $tools->fullUrlIs(action([\Kordy\Ticketit\Controllers\ConfigurationsController::class, 'index']).'*') ? "active" : "" !!}">
                            <a href="{{ action([\Kordy\Ticketit\Controllers\ConfigurationsController::class, 'index']) }}">{{ trans('ticketit::admin.nav-configuration') }}</a>
                        </li>
                        <li role="presentation"  class="{!! $tools->fullUrlIs(action([\Kordy\Ticketit\Controllers\AdministratorsController::class, 'index']).'*') ? "active" : "" !!}">
                            <a href="{{ action([\Kordy\Ticketit\Controllers\AdministratorsController::class, 'index'])}}">{{ trans('ticketit::admin.nav-administrator') }}</a>
                        </li>
                        {{--
                        <li role="presentation" class="{!! $tools->fullUrlIs(route('tickets-admin.status.index').'*') ? "active" : "" !!}">
                            <a href="{{ route('tickets-admin.status.index') }}">{{ trans('ticketit::admin.nav-statuses') }}</a>
                        </li>
                        <li role="presentation"  class="{!! $tools->fullUrlIs(route('tickets-admin/priority').'*') ? "active" : "" !!}">
                            <a href="{{ route('tickets-admin/priority') }}">{{ trans('ticketit::admin.nav-priorities') }}</a>
                        </li>
                        <li role="presentation"  class="{!! $tools->fullUrlIs(action([\Kordy\Ticketit\Controllers\AgentsController::class, 'index']).'*') ? "active" : "" !!}">
                            <a href="{{ action([\Kordy\Ticketit\Controllers\AgentsController::class, 'index']) }}">{{ trans('ticketit::admin.nav-agents') }}</a>
                        </li>
                        <li role="presentation"  class="{!! $tools->fullUrlIs(route('tickets-admin/category').'*') ? "active" : "" !!}">
                            <a href="{{ route('tickets-admin/category') }}">{{ trans('ticketit::admin.nav-categories') }}</a>
                        </li>
                        <li role="presentation"  class="{!! $tools->fullUrlIs(route('tickets-admin/configuration').'*') ? "active" : "" !!}">
                            <a href="{{ route('tickets-admin/configuration') }}">{{ trans('ticketit::admin.nav-configuration') }}</a>
                        </li>
                        <li role="presentation"  class="{!! $tools->fullUrlIs(route('tickets-admin/administrator').'*') ? "active" : "" !!}">
                            <a href="{{ route('tickets-admin/administrator') }}">{{ trans('ticketit::admin.nav-administrator') }}</a>
                        </li>
                        --}}
                    </ul>
                </li>
                @endif
            @endif

        </ul>
    </div>
</div>
