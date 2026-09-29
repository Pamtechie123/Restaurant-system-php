<?php

namespace App\Controllers;

use MongoDB\BSON\UTCDateTime;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ReservationController
{
    // Customer: request a table
    public function create(Request $request, Response $response): Response
    {
        $user = authUser($request, 'customer');
        $data = requireFields(
            $request->getParsedBody(),
            ['customerName', 'phone', 'date', 'time', 'partySize']
        );

        $date = trim($data['date']);
        $time = trim($data['time']);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))) {
            throw new \Exception('Invalid date', 400);
        }
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)) {
            throw new \Exception('Invalid time, use HH:MM', 400);
        }
        if (strtotime("$date $time") < time()) {
            throw new \Exception('Please choose a date and time in the future', 400);
        }
        if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $data['phone'])) {
            throw new \Exception('Enter a valid phone number', 400);
        }
        if ((int) $data['partySize'] < 1) {
            throw new \Exception('Party size must be at least 1', 400);
        }

        $id = db()->reservations->insertOne([
            'userId' => (string) $user['_id'],
            'customerName' => trim($data['customerName']),
            'phone' => trim($data['phone']),
            'partySize' => (int) $data['partySize'],
            'date' => $date,
            'time' => $time,
            'status' => 'requested',
            'tableNumber' => null,
            'createdAt' => new UTCDateTime(),
        ])->getInsertedId();

        return json($response, ['message' => 'Reservation requested', 'id' => (string) $id], 201);
    }

    // Customer: my reservations
    public function mine(Request $request, Response $response): Response
    {
        $user = authUser($request, 'customer');

        $reservations = db()->reservations->find(
            ['userId' => (string) $user['_id']],
            ['sort' => ['date' => -1, 'time' => -1]]
        );

        return json($response, cleanList($reservations));
    }

    // Admin: all reservations (soonest first)
    public function adminList(Request $request, Response $response): Response
    {
        authUser($request, 'admin');

        $reservations = db()->reservations->find([], ['sort' => ['date' => 1, 'time' => 1]]);

        return json($response, cleanList($reservations));
    }

    // Admin: confirm (with a table number), seat, complete or cancel
    public function update(Request $request, Response $response, array $args): Response
    {
        authUser($request, 'admin');

        $data = requireFields($request->getParsedBody(), ['status']);
        $changes = ['status' => $data['status']];

        if (!in_array($data['status'], ['requested', 'confirmed', 'seated', 'completed', 'cancelled'], true)) {
            throw new \Exception('Invalid status', 400);
        }

        if ($data['status'] === 'confirmed') {
            $table = (int) ($data['tableNumber'] ?? 0);
            if ($table < 1) {
                throw new \Exception('Give the reservation a table number', 400);
            }
            $changes['tableNumber'] = $table;
        }

        db()->reservations->updateOne(
            ['_id' => toObjectId($args['id'])],
            ['$set' => $changes]
        );

        return json($response, ['message' => 'Reservation updated']);
    }
}
