<?php

// All API routes in one place. $app comes from public/index.php

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\MenuController;
use App\Controllers\OrderController;
use App\Controllers\ReservationController;

// ----- Public -----
$app->post('/api/auth/register', [AuthController::class, 'register']);
$app->post('/api/auth/login', [AuthController::class, 'login']);
$app->get('/api/menu', [MenuController::class, 'list']);
$app->get('/api/menu/{id}', [MenuController::class, 'show']);

// ----- Customer (must be logged in) -----
$app->post('/api/orders', [OrderController::class, 'create']);
$app->get('/api/orders/my-orders', [OrderController::class, 'mine']);
$app->get('/api/orders/{id}', [OrderController::class, 'show']);
$app->post('/api/reservations', [ReservationController::class, 'create']);
$app->get('/api/reservations/mine', [ReservationController::class, 'mine']);

// ----- Admin -----
$app->post('/api/admin/register', [AuthController::class, 'adminRegister']);
$app->post('/api/admin/login', [AuthController::class, 'adminLogin']);

$app->get('/api/admin/stats', [AdminController::class, 'stats']);
$app->get('/api/admin/customers', [AdminController::class, 'customers']);
$app->patch('/api/admin/customers/{id}/status', [AdminController::class, 'setCustomerStatus']);

$app->get('/api/admin/orders', [OrderController::class, 'adminList']);
$app->patch('/api/admin/orders/{id}/status', [OrderController::class, 'setStatus']);

$app->post('/api/admin/menu', [MenuController::class, 'create']);
$app->patch('/api/admin/menu/{id}', [MenuController::class, 'setAvailability']);
$app->delete('/api/admin/menu/{id}', [MenuController::class, 'delete']);

$app->get('/api/admin/reservations', [ReservationController::class, 'adminList']);
$app->patch('/api/admin/reservations/{id}', [ReservationController::class, 'update']);
