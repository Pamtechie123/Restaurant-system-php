<?php

// Entry point: every request to the API starts here.

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/helpers.php';

use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use Slim\Psr7\Response;

// Load the values in .env (MONGODB_URI, JWT_SECRET, ADMIN_CODE ...)
Dotenv::createImmutable(__DIR__ . '/..')->safeload();

// Reservation times are compared using this timezone
date_default_timezone_set('Africa/Lagos');

$app = AppFactory::create();

$app->addBodyParsingMiddleware();   // lets us read JSON bodies
$app->addRoutingMiddleware();

// Any error thrown in our code is turned into JSON: {"error": "message"}
$errors = $app->addErrorMiddleware(true, false, false);
$errors->setDefaultErrorHandler(function ($request, Throwable $e) {
    $status = ($e->getCode() >= 400 && $e->getCode() < 600) ? $e->getCode() : 500;
    $response = new Response();
    $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
    return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
});

// CORS: allows the frontend (another port) to call this API.
// It is added last so it wraps everything else.
$app->add(function ($request, $handler) {
    $response = $request->getMethod() === 'OPTIONS'
        ? (new Response())->withStatus(204)
        : $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, DELETE, OPTIONS');
});

require __DIR__ . '/../src/routes.php';

$app->run();
