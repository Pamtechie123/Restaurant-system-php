<?php

namespace App\Controllers;

use App\Services\UserService;
use App\Middleware\RequireRole;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class UserController
{
    private UserService $service;

    public function __construct()
    {
        $this->service = new UserService();
    }

    public function listCustomers(
        Request $request,
        Response $response
    ): Response {
        try {

            RequireRole::check(
                $request,
                ['staff', 'admin']
            );

            $customers =
                $this->service->getAllCustomers();

            $response->getBody()->write(
                json_encode($customers)
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
                ->withStatus(
                    $e->getCode() ?: 403
                )
                ->withHeader(
                    'Content-Type',
                    'application/json'
                );
        }
    }
}

