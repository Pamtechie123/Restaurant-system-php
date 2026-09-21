<?php

namespace App\Repositories;

class BaseRepository
{
    protected string $collection;

    public function __construct(string $collection)
    {
        $this->collection = $collection;
    }
}
