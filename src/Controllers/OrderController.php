<?php

namespace App\Controllers;

use MongoDB\BSON\UTCDateTime;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class OrderController
{
    // Customer: place an order
    public function create(Request $request, Response $response): Response
    {
        $user = authUser($request, 'customer');
        $data = requireFields($request->getParsedBody(), ['orderType']);
        $type = $data['orderType'];

        if (!in_array($type, ['dine-in', 'takeaway', 'delivery'], true)) {
            throw new \Exception('Invalid order type', 400);
        }
        if (empty($data['items']) || !is_array($data['items'])) {
            throw new \Exception('Your cart is empty', 400);
        }

        $order = [
            'userId' => (string) $user['_id'],
            'customerName' => $user['name'],
            'orderType' => $type,
        ];

        // Extra details depend on the order type
        if ($type === 'delivery') {
            $extra = requireFields($data, ['shippingAddress', 'state', 'city', 'phone']);
            $order['shippingAddress'] = trim($extra['shippingAddress']);
            $order['state'] = trim($extra['state']);
            $order['city'] = trim($extra['city']);
            $order['phone'] = trim($extra['phone']);
        }

        if ($type === 'dine-in') {
            $extra = requireFields($data, ['reservationId']);
            $reservation = db()->reservations->findOne([
                '_id' => toObjectId($extra['reservationId']),
                'userId' => (string) $user['_id'],
                'status' => ['$in' => ['confirmed', 'seated']],
            ]);

            if (!$reservation) {
                throw new \Exception('Choose one of your confirmed reservations', 400);
            }

            $order['reservationId'] = (string) $reservation['_id'];
            $order['tableNumber'] = $reservation['tableNumber'];
        }

        // Read the price from the database (never trust the price sent by the browser)
        $order['items'] = [];
        $order['total'] = 0;

        foreach ($data['items'] as $item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            if ($quantity < 1) {
                throw new \Exception('Quantity must be at least 1', 400);
            }

            $dish = db()->menu_items->findOne(['_id' => toObjectId($item['menuItemId'] ?? '')]);
            if (!$dish) {
                throw new \Exception('A dish in your cart no longer exists', 400);
            }
            if (!($dish['isAvailable'] ?? true)) {
                throw new \Exception($dish['name'] . ' is currently unavailable', 400);
            }

            $order['items'][] = [
                'menuItemId' => (string) $dish['_id'],
                'name' => $dish['name'],
                'price' => $dish['price'],
                'quantity' => $quantity,
            ];
            $order['total'] += $dish['price'] * $quantity;
        }

        $order['status'] = 'pending';
        $order['createdAt'] = new UTCDateTime();

        $id = db()->orders->insertOne($order)->getInsertedId();

        return json($response, ['message' => 'Order placed', 'id' => (string) $id], 201);
    }

    // Customer: my orders, newest first
    public function mine(Request $request, Response $response): Response
    {
        $user = authUser($request, 'customer');

        $orders = db()->orders->find(
            ['userId' => (string) $user['_id']],
            ['sort' => ['createdAt' => -1]]
        );

        return json($response, cleanList($orders));
    }

    // Customer: one of my orders
    public function show(Request $request, Response $response, array $args): Response
    {
        $user = authUser($request, 'customer');

        $order = db()->orders->findOne([
            '_id' => toObjectId($args['id']),
            'userId' => (string) $user['_id'],
        ]);

        if (!$order) {
            throw new \Exception('Order not found', 404);
        }

        return json($response, cleanDoc($order));
    }

    // Admin: all orders, newest first
    public function adminList(Request $request, Response $response): Response
    {
        authUser($request, 'admin');

        $orders = db()->orders->find([], ['sort' => ['createdAt' => -1]]);

        return json($response, cleanList($orders));
    }

    // Admin: change the status of an order
    public function setStatus(Request $request, Response $response, array $args): Response
    {
        authUser($request, 'admin');

        $data = requireFields($request->getParsedBody(), ['status']);

        if (!in_array($data['status'], ['pending', 'preparing', 'ready', 'served', 'cancelled'], true)) {
            throw new \Exception('Invalid status', 400);
        }

        db()->orders->updateOne(
            ['_id' => toObjectId($args['id'])],
            ['$set' => ['status' => $data['status']]]
        );

        return json($response, ['message' => 'Order updated']);
    }
}
