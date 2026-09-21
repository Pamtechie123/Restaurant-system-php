<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    /**
     * Get all registered customers.
     */
    public function getAllCustomers(): array
    {
        return $this->userRepo->getAllCustomers();
    }
}

