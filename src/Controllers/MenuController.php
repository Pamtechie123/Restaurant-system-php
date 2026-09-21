<?php

namespace App\Controllers;

use App\Services\MenuService;
use App\Middleware\RequireRole;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class MenuController
{
    private MenuService $service;

    public function __construct()
    {
        $this->service = new MenuService();
    }

    // PUBLIC ACTION
    // Customers can view available menu items.
    public function list(
        Request $request,
        Response $response
    ): Response {
        $items = $this->service->listAvailable();

        $response->getBody()->write(
            json_encode($items)
        );

        return $response
            ->withHeader('Content-Type', 'application/json');
    }


    // PUBLIC ACTION
    // Customers can view a specific menu item.
    public function get(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {

            $item = $this->service->getById($args['id']);

            $response->getBody()->write(
                json_encode($item)
            );

            return $response
                ->withHeader('Content-Type', 'application/json');

        } catch (\Exception $e) {

            $response->getBody()->write(
                json_encode(['error' => $e->getMessage()])
            );

            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json');
        }
    }


    // STAFF / ADMIN ACTION
    // Only staff and admins can add menu items.
    public function create(
        Request $request,
        Response $response
    ): Response {
        try {

            RequireRole::check($request, ['staff', 'admin']);

            $data = $request->getParsedBody();

            $id = $this->service->create($data);

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
                ->withStatus($e->getCode() ?: 403)
                ->withHeader('Content-Type', 'application/json');
        }
    }


    // STAFF / ADMIN ACTION
    // Only staff and admins can edit menu items.
    public function update(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {

            RequireRole::check($request, ['staff', 'admin']);

            $this->service->update(
                $args['id'],
                $request->getParsedBody()
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


    // STAFF / ADMIN ACTION
    // Only staff and admins can delete menu items.
    public function delete(
        Request $request,
        Response $response,
        array $args
    ): Response {
        try {

            RequireRole::check($request, ['staff', 'admin']);

            $this->service->delete($args['id']);

            $response->getBody()->write(
                json_encode(['message' => 'Deleted'])
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

