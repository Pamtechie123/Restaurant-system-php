<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminController
{
    // Numbers for the dashboard
    public function stats(Request $request, Response $response): Response
    {
        authUser($request, 'admin');

        // Add up the total of every order that was not cancelled
        $revenue = db()->orders->aggregate([
            ['$match' => ['status' => ['$ne' => 'cancelled']]],
            ['$group' => ['_id' => null, 'total' => ['$sum' => '$total']]],
        ])->toArray();

        $recent = db()->orders->find([], ['sort' => ['createdAt' => -1], 'limit' => 5]);

        return json($response, [
            'orders' => db()->orders->countDocuments(),
            'pendingOrders' => db()->orders->countDocuments(['status' => 'pending']),
            'revenue' => $revenue[0]['total'] ?? 0,
            'requestedReservations' => db()->reservations->countDocuments(['status' => 'requested']),
            'customers' => db()->users->countDocuments(),
            'dishes' => db()->menu_items->countDocuments(),
            'unavailableDishes' => db()->menu_items->countDocuments(['isAvailable' => false]),
            'recentOrders' => cleanList($recent),
        ]);
    }

    // Every customer with how many orders they made and how much they spent
    public function customers(Request $request, Response $response): Response
    {
        authUser($request, 'admin');

        $totals = [];
        $groups = db()->orders->aggregate([
            ['$group' => [
                '_id' => '$userId',
                'orders' => ['$sum' => 1],
                'spent' => ['$sum' => '$total'],
            ]],
        ]);
        foreach ($groups as $group) {
            $totals[$group['_id']] = $group;
        }

        $customers = cleanList(db()->users->find([], ['sort' => ['createdAt' => -1]]));

        foreach ($customers as $i => $customer) {
            $customers[$i]['status'] = $customer['status'] ?? 'active';
            $customers[$i]['orders'] = $totals[$customer['id']]['orders'] ?? 0;
            $customers[$i]['spent'] = $totals[$customer['id']]['spent'] ?? 0;
        }

        return json($response, $customers);
    }

    // Deactivate (block) or reactivate a customer
    public function setCustomerStatus(Request $request, Response $response, array $args): Response
    {
        authUser($request, 'admin');

        $data = requireFields($request->getParsedBody(), ['status']);

        if (!in_array($data['status'], ['active', 'blocked'], true)) {
            throw new \Exception('Status must be active or blocked', 400);
        }

        db()->users->updateOne(
            ['_id' => toObjectId($args['id'])],
            ['$set' => ['status' => $data['status']]]
        );

        return json($response, ['message' => 'Customer updated']);
    }
}
