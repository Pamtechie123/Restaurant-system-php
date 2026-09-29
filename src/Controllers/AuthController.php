<?php

namespace App\Controllers;

use MongoDB\BSON\UTCDateTime;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    // Customer sign up
    public function register(Request $request, Response $response): Response
    {
        $data = requireFields($request->getParsedBody(), ['name', 'email', 'password']);

        $user = $this->newAccount($data, 'users');
        $user['phone'] = trim($data['phone'] ?? '');

        $id = db()->users->insertOne($user)->getInsertedId();

        return json($response, ['message' => 'Account created', 'userId' => (string) $id], 201);
    }

    // Customer login
    public function login(Request $request, Response $response): Response
    {
        return $this->loginTo('users', 'customer', $request, $response);
    }

    // Admin sign up: needs the secret ADMIN_CODE from the .env file
    public function adminRegister(Request $request, Response $response): Response
    {
        $data = requireFields($request->getParsedBody(), ['name', 'email', 'password', 'adminCode']);

        $secret = $_ENV['ADMIN_CODE'] ?? '';
        if ($secret === '') {
            throw new \Exception('ADMIN_CODE is not set in the .env file', 500);
        }
        if (!hash_equals($secret, (string) $data['adminCode'])) {
            throw new \Exception('Wrong admin code', 403);
        }

        $admin = $this->newAccount($data, 'admins');
        $id = db()->admins->insertOne($admin)->getInsertedId();

        return json($response, ['message' => 'Admin account created', 'userId' => (string) $id], 201);
    }

    // Admin login
    public function adminLogin(Request $request, Response $response): Response
    {
        return $this->loginTo('admins', 'admin', $request, $response);
    }

    // Validate the form and build the document to save (customers and admins share this).
    private function newAccount(array $data, string $collection): array
    {
        $name = trim($data['name']);
        $email = strtolower(trim($data['email']));

        if (strlen($name) < 2) {
            throw new \Exception('Name must be at least 2 characters', 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Enter a valid email address', 400);
        }
        if (strlen($data['password']) < 6) {
            throw new \Exception('Password must be at least 6 characters', 400);
        }
        if (db()->selectCollection($collection)->findOne(['email' => $email])) {
            throw new \Exception('Email already registered', 409);
        }

        return [
            'name' => $name,
            'email' => $email,
            'passwordHash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'status' => 'active',
            'createdAt' => new UTCDateTime(),
        ];
    }

    // Check email + password and return a token.
    private function loginTo(string $collection, string $role, Request $request, Response $response): Response
    {
        $data = requireFields($request->getParsedBody(), ['email', 'password']);

        $user = db()->selectCollection($collection)->findOne([
            'email' => strtolower(trim($data['email'])),
        ]);

        if (!$user || !password_verify($data['password'], $user['passwordHash'])) {
            throw new \Exception('Invalid email or password', 401);
        }
        if (($user['status'] ?? 'active') === 'blocked') {
            throw new \Exception('This account has been deactivated. Please contact the restaurant.', 403);
        }

        return json($response, [
            'token' => makeToken((string) $user['_id'], $role),
            'user' => ['id' => (string) $user['_id'], 'name' => $user['name'], 'role' => $role],
        ]);
    }
}
