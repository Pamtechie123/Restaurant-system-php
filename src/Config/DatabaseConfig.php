<?php

namespace App\Config;

use MongoDB\Client;
use MongoDB\Database;

class DatabaseConfig
{
    private static ?Database $db = null;

    public static function getDB(): Database
    {
        if (self::$db === null) {
            $uri = getenv('MONGODB_URI') ?: 'mongodb://localhost:27017';
            $databaseName = getenv('DB_NAME') ?: 'restaurant_system';

            $client = new Client($uri);

            self::$db = $client->selectDatabase($databaseName);
        }

        return self::$db;
    }
}