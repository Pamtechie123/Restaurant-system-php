<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Firebase\JWT\JWT;

class AuthService
{
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }


    /*
     * Register a new customer
     */
    public function register(array $data): string
    {
        if ($this->userRepo->findByEmail($data['email'])) {

            throw new \Exception(
                'Email already registered'
            );

        }


        /*
         * IMPORTANT:
         *
         * Public registration always creates
         * a customer account.
         *
         * A user cannot register themselves
         * as admin or staff.
         */

        $role = 'customer';


        $id = $this->userRepo->create([

            'name' =>
                $data['name'],

            'email' =>
                $data['email'],

            'phone' =>
                $data['phone'] ?? null,

            'passwordHash' =>
                password_hash(
                    $data['password'],
                    PASSWORD_BCRYPT
                ),

            'role' =>
                $role,

        ]);


        return (string) $id;
    }


    /*
     * Login
     */
    public function login(
        string $email,
        string $password
    ): array {

        $user =
            $this->userRepo->findByEmail(
                $email
            );


        if (
            !$user ||
            !password_verify(
                $password,
                $user['passwordHash']
            )
        ) {

            throw new \Exception(
                'Invalid email or password'
            );

        }


        /*
         * Get the user's role.
         *
         * Existing users without a role
         * will be treated as customers.
         */

        $role =
            $user['role'] ?? 'customer';


        /*
         * JWT payload
         */

        $payload = [

            'userId' =>
                (string) $user['_id'],

            'role' =>
                $role,

            'exp' =>
                time() + (24 * 60 * 60),

        ];


        /*
         * Create JWT
         */

        $token =
            JWT::encode(
                $payload,
                $_ENV['JWT_SECRET'],
                'HS256'
            );


        /*
         * Return token + user information
         */

        return [

            'token' =>
                $token,

            'user' => [

                'id' =>
                    (string) $user['_id'],

                'name' =>
                    $user['name'],

                'role' =>
                    $role,

            ],

        ];

    }
}
