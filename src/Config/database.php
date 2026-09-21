<?php

return [
    'uri' => getenv('MONGODB_URI') ?: 'mongodb://localhost:27017',
    'database' => getenv('DB_NAME') ?: 'restaurant_system',
];
