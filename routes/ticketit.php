<?php

use App\Http\Controllers\Kordy\Ticketit\Controllers;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => \Kordy\Ticketit\Helpers\LaravelVersion::authMiddleware()], function () use ($main_route, $main_route_path, $admin_route, $admin_route_path) {
    $ori_tickets_path = 'Kordy\Ticketit\Controllers\TicketsController::class';
    $new_tickets_path = \App\Http\TicketitControllers\TicketsControllerOver::class;
    $new_tickets_comment_path = \App\Http\TicketitControllers\CommentsControllerOver::class;
    //Route::group(['middleware' => '', function () use ($main_route) {
    //Ticket public route
    Route::get("$main_route_path/complete", $new_tickets_path.'@indexComplete')
        ->name("$main_route-complete");
    Route::get("$main_route_path/data/{id?}", $new_tickets_path.'@data')
        ->name("$main_route.data");

    $field_name = last(explode('/', $main_route_path));

    //added by mufaddal for context ticket
    Route::post("$main_route_path/storeAjax", $new_tickets_path.'@storeAjax')
        ->name("$main_route.storeAjax");

    Route::get("$main_route_path/my-tickets", $new_tickets_path.'@myTickets')
        ->name("$main_route.my-tickets");

    Route::resource($main_route_path, $new_tickets_path, [
        'names'      => [
            'index'   => $main_route.'.index',
            'store'   => $main_route.'.store',
            'create'  => $main_route.'.create',
            'update'  => $main_route.'.update',
            'show'    => $main_route.'.show',
            'destroy' => $main_route.'.destroy',
            'edit'    => $main_route.'.edit',
        ],
        'parameters' => [
            $field_name => 'ticket',
        ],
    ]);

    //Ticket Comments public route
    $field_name = last(explode('/', "$main_route_path-comment"));
    Route::resource("$main_route_path-comment", $new_tickets_comment_path, [
        'names'      => [
            'index'   => "$main_route-comment.index",
            'store'   => "$main_route-comment.store",
            'create'  => "$main_route-comment.create",
            'update'  => "$main_route-comment.update",
            'show'    => "$main_route-comment.show",
            'destroy' => "$main_route-comment.destroy",
            'edit'    => "$main_route-comment.edit",
        ],
        'parameters' => [
            $field_name => 'ticket_comment',
        ],
    ]);

    //Ticket complete route for permitted user.
    Route::get("$main_route_path/{id}/complete", $new_tickets_path.'@complete')
        ->name("$main_route.complete");

    //Ticket reopen route for permitted user.
    Route::get("$main_route_path/{id}/reopen", $new_tickets_path.'@reopen')
        ->name("$main_route.reopen");
    //});

    Route::group(['middleware' => \App\Http\Middleware\Ticketit\IsAgentMiddlewareOver::class], function () use ($main_route, $main_route_path, $new_tickets_path) {

        //API return list of agents in particular category
        Route::get("$main_route_path/agents/list/{category_id?}/{ticket_id?}", [
            'as'   => $main_route.'agentselectlist',
            'uses' => $new_tickets_path.'@agentSelectList',
        ]);
    });

    Route::group(['middleware' => \App\Http\Middleware\Ticketit\IsAdminMiddlewareOver::class], function () use ($admin_route, $admin_route_path) {
        //Ticket admin index route (ex. http://url/tickets-admin/)
        Route::get("$admin_route_path/indicator/{indicator_period?}", [
            'as'   => $admin_route.'.dashboard.indicator',
            'uses' => [Kordy\Ticketit\Controllers\DashboardController::class, 'index'],
        ]);
        Route::get($admin_route_path, [Kordy\Ticketit\Controllers\DashboardController::class, 'index']);

        //Ticket statuses admin routes (ex. http://url/tickets-admin/status)
        Route::resource("$admin_route_path/status", 'Kordy\Ticketit\Controllers\StatusesController::class', [
            'names' => [
                'index'   => "$admin_route.status.index",
                'store'   => "$admin_route.status.store",
                'create'  => "$admin_route.status.create",
                'update'  => "$admin_route.status.update",
                'show'    => "$admin_route.status.show",
                'destroy' => "$admin_route.status.destroy",
                'edit'    => "$admin_route.status.edit",
            ],
        ]);

        //Ticket priorities admin routes (ex. http://url/tickets-admin/priority)
        Route::resource("$admin_route_path/priority", 'Kordy\Ticketit\Controllers\PrioritiesController::class', [
            'names' => [
                'index'   => "$admin_route.priority.index",
                'store'   => "$admin_route.priority.store",
                'create'  => "$admin_route.priority.create",
                'update'  => "$admin_route.priority.update",
                'show'    => "$admin_route.priority.show",
                'destroy' => "$admin_route.priority.destroy",
                'edit'    => "$admin_route.priority.edit",
            ],
        ]);

        //Agents management routes (ex. http://url/tickets-admin/agent)
        Route::resource("$admin_route_path/agent", 'Kordy\Ticketit\Controllers\AgentsController::class', [
            'names' => [
                'index'   => "$admin_route.agent.index",
                'store'   => "$admin_route.agent.store",
                'create'  => "$admin_route.agent.create",
                'update'  => "$admin_route.agent.update",
                'show'    => "$admin_route.agent.show",
                'destroy' => "$admin_route.agent.destroy",
                'edit'    => "$admin_route.agent.edit",
            ],
        ]);

        //Agents management routes (ex. http://url/tickets-admin/agent)
        Route::resource("$admin_route_path/category", 'Kordy\Ticketit\Controllers\CategoriesController::class', [
            'names' => [
                'index'   => "$admin_route.category.index",
                'store'   => "$admin_route.category.store",
                'create'  => "$admin_route.category.create",
                'update'  => "$admin_route.category.update",
                'show'    => "$admin_route.category.show",
                'destroy' => "$admin_route.category.destroy",
                'edit'    => "$admin_route.category.edit",
            ],
        ]);

        //Settings configuration routes (ex. http://url/tickets-admin/configuration)
        Route::resource("$admin_route_path/configuration", 'Kordy\Ticketit\Controllers\ConfigurationsController::class', [
            'names' => [
                'index'   => "$admin_route.configuration.index",
                'store'   => "$admin_route.configuration.store",
                'create'  => "$admin_route.configuration.create",
                'update'  => "$admin_route.configuration.update",
                'show'    => "$admin_route.configuration.show",
                'destroy' => "$admin_route.configuration.destroy",
                'edit'    => "$admin_route.configuration.edit",
            ],
        ]);

        //Administrators configuration routes (ex. http://url/tickets-admin/administrators)
        Route::resource("$admin_route_path/administrator", 'Kordy\Ticketit\Controllers\AdministratorsController::class', [
            'names' => [
                'index'   => "$admin_route.administrator.index",
                'store'   => "$admin_route.administrator.store",
                'create'  => "$admin_route.administrator.create",
                'update'  => "$admin_route.administrator.update",
                'show'    => "$admin_route.administrator.show",
                'destroy' => "$admin_route.administrator.destroy",
                'edit'    => "$admin_route.administrator.edit",
            ],
        ]);

        //Tickets demo data route (ex. http://url/tickets-admin/demo-seeds/)
        // Route::get("$admin_route/demo-seeds", [Kordy\Ticketit\Controllers\InstallController::class, 'demoDataSeeder']);
    });
});
