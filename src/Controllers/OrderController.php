<?php

namespace App\Controllers;

use App\Services\OrderService;
use App\Middleware\RequireRole;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class OrderController
{
    private OrderService $service;

    public function __construct()
    {
        $this->service = new OrderService();
    }

    /*
    |--------------------------------------------------------------------------
    | Create Order
    |--------------------------------------------------------------------------
    */
    public function create(
        Request $request,
        Response $response
    ): Response {

        try {

            $userId = $request->getAttribute('userId');

            $data = $request->getParsedBody();

            $id = $this->service->placeOrder(
                $userId,
                $data['orderType'],
                $data['tableNumber'] ?? null,
                $data['items'],
                $data['reservationId'] ?? null,
                $data['shippingAddress'] ?? null,
                $data['state'] ?? null,
                $data['city'] ?? null,
                $data['phone'] ?? null
            );

            $response->getBody()->write(
                json_encode([
                    'message' => 'Order placed successfully',
                    'id' => $id
                ])
            );

            return $response
                ->withStatus(201)
                ->withHeader(
                    'Content-Type',
                    'application/json'
                );

        } catch (\Exception $e) {

            $response->getBody()->write(
                json_encode([
                    'error' => $e->getMessage()
                ])
            );

            return $response
                ->withStatus($e->getCode() ?: 400)
                ->withHeader(
                    'Content-Type',
                    'application/json'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | List Orders
    |--------------------------------------------------------------------------
    */
    public function listActive(
        Request $request,
        Response $response
    ): Response {

        try {

            RequireRole::check(
                $request,
                ['staff', 'admin']
            );

            $orders = $this->service->getActiveOrders();

            $response->getBody()->write(
                json_encode($orders)
            );

            return $response
                ->withHeader(
                    'Content-Type',
                    'application/json'
                );

        } catch (\Exception $e) {

            $response->getBody()->write(
                json_encode([
                    'error' => $e->getMessage()
                ])
            );

            return $response
                ->withStatus($e->getCode() ?: 403)
                ->withHeader(
                    'Content-Type',
                    'application/json'
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Update Order Status
    |--------------------------------------------------------------------------
    */
    public function updateStatus(
        Request $request,
        Response $response,
        array $args
    ): Response {

        try {

            RequireRole::check(
                $request,
                ['staff', 'admin']
            );

            $data = $request->getParsedBody();

            $this->service->updateStatus(
                $args['id'],
                $data['status']
            );

            $response->getBody()->write(
                json_encode([
                    'message' => 'Order status updated successfully'
                ])
            );

            return $response
                ->withHeader(
                    'Content-Type',
                    'application/json'
                );

        } catch (\Exception $e) {

            $response->getBody()->write(
                json_encode([
                    'error' => $e->getMessage()
                ])
            );

            return $response
                ->withStatus($e->getCode() ?: 403)
                ->withHeader(
                    'Content-Type',
                    'application/json'
                );
        }
    }
}