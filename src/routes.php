<?php

use Slim\Routing\RouteCollectorProxy;

use App\Controllers\AuthController;
use App\Controllers\MenuController;
use App\Controllers\OrderController;
use App\Controllers\ReservationController;
use App\Controllers\UserController;

use App\Middleware\AuthMiddleware;

return function ($app) {

    // ============================================
    // CONTROLLERS
    // ============================================

    $auth = new AuthController();
    $menu = new MenuController();
    $order = new OrderController();
    $reservation = new ReservationController();
    $user = new UserController();


    // ============================================
    // MIDDLEWARE
    // ============================================

    $authMiddleware = new AuthMiddleware();


    // ============================================
    // AUTHENTICATION
    // ============================================

    // Register
    $app->post(
        '/api/auth/register',
        [$auth, 'register']
    );

    // Login
    $app->post(
        '/api/auth/login',
        [$auth, 'login']
    );


    // ============================================
    // PUBLIC MENU
    // ============================================

    // Get available menu items
    $app->get(
        '/api/menu',
        [$menu, 'list']
    );

    // Get one menu item
    $app->get(
        '/api/menu/{id}',
        [$menu, 'get']
    );


    // ============================================
    // MENU MANAGEMENT
    // ============================================
    //
    // The routes require a valid JWT.
    // MenuController then checks that the user
    // is staff or admin before performing changes.
    //

    $app->group(
        '/api/menu',
        function (RouteCollectorProxy $group) use ($menu) {

            // Add menu item
            $group->post(
                '',
                [$menu, 'create']
            );

            // Edit menu item
            $group->put(
                '/{id}',
                [$menu, 'update']
            );

            // Delete menu item
            $group->delete(
                '/{id}',
                [$menu, 'delete']
            );

        }
    )->add($authMiddleware);


    // ============================================
    // ORDERS
    // ============================================

    $app->group(
        '/api/orders',
        function (RouteCollectorProxy $group) use ($order) {

            // Customer creates an order
            $group->post(
                '',
                [$order, 'create']
            );

            // Staff/admin view active orders
            $group->get(
                '',
                [$order, 'listActive']
            );

            // Staff/admin change order status
            $group->patch(
                '/{id}/status',
                [$order, 'updateStatus']
            );

        }
    )->add($authMiddleware);


    // ============================================
    // RESERVATIONS
    // ============================================

    $app->group(
        '/api/reservations',
        function (RouteCollectorProxy $group) use ($reservation) {

            // Customer creates reservation
            $group->post(
                '',
                [$reservation, 'create']
            );

            // Staff/admin view reservations
            $group->get(
                '',
                [$reservation, 'list']
            );

            // Staff/admin confirm reservation
            $group->patch(
                '/{id}/confirm',
                [$reservation, 'confirm']
            );

            // Staff/admin change reservation status
            $group->patch(
                '/{id}/status',
                [$reservation, 'updateStatus']
            );

        }
    )->add($authMiddleware);


    // ============================================
    // CUSTOMER MANAGEMENT
    // ============================================
    //
    // UserController itself checks that the
    // authenticated user is staff/admin.
    //

    $app->group(
        '/api/users',
        function (RouteCollectorProxy $group) use ($user) {

            // Staff/admin can view customers
            $group->get(
                '/customers',
                [$user, 'listCustomers']
            );

        }
    )->add($authMiddleware);

};
