<?php

// Small helper functions used by the controllers.

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client;
use MongoDB\Database;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Connect to MongoDB once, then reuse the connection.
// The typeMap makes MongoDB return normal PHP arrays.
function db(): Database
{
    static $database = null;

    if ($database === null) {
        $client = new Client(
            $_ENV['MONGODB_URI'] ?? 'mongodb://localhost:27017',
            [],
            ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']]
        );
        $database = $client->selectDatabase($_ENV['DB_NAME'] ?? 'restaurant_system');
    }

    return $database;
}

// Send JSON back to the browser.
function json(Response $response, $data, int $status = 200): Response
{
    $response->getBody()->write(json_encode($data));
    return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
}

// Turn the id from the URL into a MongoDB ObjectId.
function toObjectId(string $id): ObjectId
{
    try {
        return new ObjectId($id);
    } catch (Throwable $e) {
        throw new Exception('Invalid id', 400);
    }
}

// Make a MongoDB document safe to send as JSON:
// _id becomes "id", dates become text, the password hash is removed.
function cleanDoc(array $doc): array
{
    $doc['id'] = (string) $doc['_id'];
    unset($doc['_id'], $doc['passwordHash']);

    foreach ($doc as $key => $value) {
        if ($value instanceof UTCDateTime) {
            $doc[$key] = $value->toDateTime()->format('c');
        }
    }

    return $doc;
}

// Clean a list of documents.
function cleanList(iterable $docs): array
{
    $list = [];
    foreach ($docs as $doc) {
        $list[] = cleanDoc($doc);
    }
    return $list;
}

// Check that the required fields were sent and are not empty.
function requireFields($data, array $fields): array
{
    $data = is_array($data) ? $data : [];

    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim((string) $data[$field]) === '') {
            throw new Exception("$field is required", 400);
        }
    }

    return $data;
}

// Create a login token that lasts 24 hours.
function makeToken(string $userId, string $role): string
{
    return JWT::encode([
        'userId' => $userId,
        'role' => $role,
        'exp' => time() + 24 * 60 * 60,
    ], $_ENV['JWT_SECRET'], 'HS256');
}

// Find out who is calling. $role is 'customer' or 'admin'.
// Customers live in the "users" collection, admins in the "admins" collection,
// so changing a field on a customer can never make them an admin.
function authUser(Request $request, string $role): array
{
    $header = $request->getHeaderLine('Authorization');

    if (!str_starts_with($header, 'Bearer ')) {
        throw new Exception('Please log in first', 401);
    }

    try {
        $token = JWT::decode(substr($header, 7), new Key($_ENV['JWT_SECRET'], 'HS256'));
    } catch (Throwable $e) {
        throw new Exception('Session expired, please log in again', 401);
    }

    if ($token->role !== $role) {
        throw new Exception('You are not allowed to do this', 403);
    }

    $collection = $role === 'admin' ? 'admins' : 'users';
    $user = db()->selectCollection($collection)->findOne(['_id' => toObjectId($token->userId)]);

    if (!$user) {
        throw new Exception('Account not found, please log in again', 401);
    }

    if (($user['status'] ?? 'active') === 'blocked') {
        throw new Exception('This account has been deactivated', 403);
    }

    return $user;
}
