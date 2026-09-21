<?php

namespace App\Controllers;

use App\Services\ReservationService;
use App\Middleware\RequireRole;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ReservationController
{
    private ReservationService $service;

    public function __construct()
    {
        $this->service = new ReservationService();
    }

    // CUSTOMER ACTION
    // Customers can create reservations.
    public function create(Request $request, Response $response): Response
    {
        try {
            $userId = $request->getAttribute('userId');

            $id = $this->service->create(
                $userId,
                $request->getParsedBody()
            );

            $response->getBody()->write(
                json_encode(['id' => $id])
            );

            return $response
                ->withStatus(201)
                ->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {

            $response->getBody()->write(
                json_encode(['error' => $e->getMessage()])
            );

            return $response
                ->withStatus(400)
                ->withHeader('Content-Type', 'application/json');
        }
    }


    // STAFF / ADMIN ACTION
    // Only staff and admins can view reservations.
    public function list(Request $request, Response $response): Response
    {
        try {

            RequireRole::check($request, ['staff', 'admin']);

            $params = $request->getQueryParams();

            $reservations = $this->service->listAll(
                $params['date'] ?? null
            );

            $response->getBody()->write(
                json_encode($reservations)
            );

            return $response
                ->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {

            $response->getBody()->write(
                json_encode(['error' => $e->getMessage()])
            );

            return $response
                ->withStatus($e->getCode() ?: 403)
                ->withHeader('Content-Type', 'application/json');
        }
    }


    // STAFF / ADMIN ACTION
    // Only staff and admins can confirm reservations.
    public function confirm(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {

            RequireRole::check($request, ['staff', 'admin']);

            $data = $request->getParsedBody();

            $this->service->confirm(
                $args['id'],
                (int) $data['tableNumber']
            );

            $response->getBody()->write(
                json_encode(['message' => 'Confirmed'])
            );

            return $response
                ->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {

            $response->getBody()->write(
                json_encode(['error' => $e->getMessage()])
            );

            return $response
                ->withStatus($e->getCode() ?: 403)
                ->withHeader('Content-Type', 'application/json');
        }
    }


    // STAFF / ADMIN ACTION
    // Only staff and admins can change reservation status.
    public function updateStatus(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {

            RequireRole::check($request, ['staff', 'admin']);

            $data = $request->getParsedBody();

            $this->service->updateStatus(
                $args['id'],
                $data['status']
            );

            $response->getBody()->write(
                json_encode(['message' => 'Updated'])
            );

            return $response
                ->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {

            $response->getBody()->write(
                json_encode(['error' => $e->getMessage()])
            );

            return $response
                ->withStatus($e->getCode() ?: 403)
                ->withHeader('Content-Type', 'application/json');
        }
    }
}
