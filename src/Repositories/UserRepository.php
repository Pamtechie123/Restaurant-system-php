<?php

namespace App\Repositories;

use App\Config\DatabaseConfig;
use MongoDB\BSON\ObjectId;

class UserRepository
{
    private $collection;

    public function __construct()
    {
        $this->collection = DatabaseConfig::getDB()->selectCollection('users');
    }


    // Create a new user
    public function create(array $user)
    {
        $result = $this->collection->insertOne($user);

        return $result->getInsertedId();
    }


    // Find user by email
    public function findByEmail(string $email)
    {
        return $this->collection->findOne([
            'email' => $email
        ]);
    }


    // Find user by ID
    public function findById(string $id)
    {
        return $this->collection->findOne([
            '_id' => new ObjectId($id)
        ]);
    }


    // Get all customers
    public function getAllCustomers(): array
    {
        $customers = [];

        $cursor = $this->collection->find(
            ['role' => 'customer'],
            [
                'projection' => [
                    'passwordHash' => 0
                ]
            ]
        );

        foreach ($cursor as $customer) {
            $customer['_id'] = (string) $customer['_id'];
            $customers[] = $customer;
        }

        return $customers;
    }
}
