<?php

namespace App\Repositories;

use App\Config\DatabaseConfig;
use MongoDB\BSON\ObjectId;

class OrderRepository
{
    private $collection;

    public function __construct()
    {
        $this->collection =
            DatabaseConfig::getDB()
                ->selectCollection('orders');
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE ORDER
    |--------------------------------------------------------------------------
    */

    public function create(array $order)
    {
        $result =
            $this->collection->insertOne($order);

        return $result->getInsertedId();
    }


    /*
    |--------------------------------------------------------------------------
    | FIND ALL ORDERS
    |--------------------------------------------------------------------------
    |
    | Admin/staff should be able to see every order,
    | including served and cancelled orders.
    |
    */

    public function findActive()
    {
        return $this->collection->find(
            [],
            [
                'sort' => [
                    'createdAt' => -1
                ]
            ]
        )->toArray();
    }


    /*
    |--------------------------------------------------------------------------
    | FIND ONE ORDER
    |--------------------------------------------------------------------------
    */

    public function findById(string $id)
    {
        return $this->collection->findOne(
            [
                '_id' => new ObjectId($id)
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE ORDER STATUS
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        string $id,
        string $status
    ) {
        return $this->collection->updateOne(
            [
                '_id' => new ObjectId($id)
            ],
            [
                '$set' => [
                    'status' => $status
                ]
            ]
        );
    }
}