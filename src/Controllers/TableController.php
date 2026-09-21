<?php

namespace App\Controllers;

class TableController extends Controller
{
    public function index(): void
    {
        $this->json(['message' => 'Tables endpoint']);
    }
}
