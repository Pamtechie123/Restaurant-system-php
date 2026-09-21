<?php
namespace App\Repositories;
use App\Config\DatabaseConfig;
use MongoDB\BSON\ObjectId;

class MenuRepository
{
    private $collection;

    public function __construct()
    {
        $this->collection = DatabaseConfig::getDB()->selectCollection('menu_items');
    }

    public function findAllAvailable()
    {
        return $this->collection->find(['isAvailable' => true])->toArray();
    }

    public function findById(string $id)
    {
        return $this->collection->findOne(['_id' => new ObjectId($id)]);
    }

    public function create(array $item)
    {
        $result = $this->collection->insertOne($item);
        return $result->getInsertedId();
    }

    public function update(string $id, array $fields)
    {
        return $this->collection->updateOne(
            ['_id' => new ObjectId($id)],
            ['$set' => $fields]
        );
    }

    public function delete(string $id)
    {
        return $this->collection->deleteOne(['_id' => new ObjectId($id)]);
    }
}
