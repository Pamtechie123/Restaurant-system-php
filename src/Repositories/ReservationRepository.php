<?php

namespace App\Repositories;

use App\Config\DatabaseConfig;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class ReservationRepository
{
    private $collection;

    public function __construct()
    {
        $this->collection = DatabaseConfig::getDB()->selectCollection('reservations');
    }

    public function create(array $reservation)
    {
        $result = $this->collection->insertOne($reservation);

        return $result->getInsertedId();
    }

    public function findAll(?string $date = null)
    {
        $filter = [];

        if ($date) {
            $start = new UTCDateTime(
                strtotime($date . ' 00:00:00') * 1000
            );

            $end = new UTCDateTime(
                strtotime($date . ' 23:59:59') * 1000
            );

            $filter['date'] = [
                '$gte' => $start,
                '$lte' => $end
            ];
        }

        return $this->collection
            ->find($filter, ['sort' => ['date' => 1]])
            ->toArray();
    }

    public function confirm(string $id, int $tableNumber)
    {
        return $this->collection->updateOne(
            ['_id' => new ObjectId($id)],
            [
                '$set' => [
                    'status' => 'confirmed',
                    'tableNumber' => $tableNumber
                ]
            ]
        );
    }

    public function updateStatus(string $id, string $status)
    {
        return $this->collection->updateOne(
            ['_id' => new ObjectId($id)],
            [
                '$set' => [
                    'status' => $status
                ]
            ]
        );
    }

    public function delete(string $id)
    {
        return $this->collection->deleteOne([
            '_id' => new ObjectId($id)
        ]);
    }
}