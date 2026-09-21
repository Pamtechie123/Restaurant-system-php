<?php

namespace App\Models;

class Model
{
    protected string $collection;

    public function __construct(string $collection)
    {
        $this->collection = $collection;
    }
}
