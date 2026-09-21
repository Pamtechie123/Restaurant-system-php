<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;


// ========================================
// LOAD ENVIRONMENT VARIABLES
// ========================================

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();


// ========================================
// CREATE SLIM APP
// ========================================

$app = AppFactory::create();


// ========================================
// ROUTING MIDDLEWARE
// ========================================

$app->addRoutingMiddleware();


// ========================================
// CORS MIDDLEWARE
// ========================================

$app->add(function (
    Request $request,
    RequestHandler $handler
): Response {

    // Handle browser preflight requests
    if ($request->getMethod() === 'OPTIONS') {

        $response = new \Slim\Psr7\Response();

    } else {

        $response = $handler->handle($request);

    }


    return $response
        ->withHeader(
            'Access-Control-Allow-Origin',
            '*'
        )
        ->withHeader(
            'Access-Control-Allow-Headers',
            'Content-Type, Authorization, X-Requested-With'
        )
        ->withHeader(
            'Access-Control-Allow-Methods',
            'GET, POST, PUT, PATCH, DELETE, OPTIONS'
        )
        ->withHeader(
            'Access-Control-Max-Age',
            '86400'
        );

});


// ========================================
// BODY PARSING
// ========================================

$app->addBodyParsingMiddleware();


// ========================================
// ERROR HANDLING
// ========================================

$app->addErrorMiddleware(
    true,
    true,
    true
);


// ========================================
// LOAD ROUTES
// ========================================

$routes = require __DIR__ . '/../src/routes.php';

$routes($app);


// ========================================
// RUN APPLICATION
// ========================================

$app->run();