<?php

// Adds the sample dishes from the design to the database.
// Run it once from the project folder:  php seed.php
// Dishes that already exist (same name) are skipped.

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/helpers.php';

Dotenv\Dotenv::createImmutable(__DIR__)->load();

$dishes = [
    [
        'name' => 'Jollof Rice',
        'description' => 'Smoky party jollof rice served with fried plantain and grilled chicken.',
        'price' => 6000,
        'category' => 'Rice',
        'imageUrl' => '/images/134580.jpg',
    ],
    [
        'name' => 'Pasta',
        'description' => 'Spaghetti in rich tomato sauce with juicy meatballs and parmesan.',
        'price' => 5500,
        'category' => 'Pasta',
        'imageUrl' => '/images/pasta.jpg',
    ],
    [
        'name' => 'Pizza',
        'description' => 'Stone-baked pizza loaded with pepperoni, peppers, olives and stretchy cheese.',
        'price' => 15000,
        'category' => 'Pizza',
        'imageUrl' => '/images/pizza.jpg',
    ],
    [
        'name' => 'Sushi',
        'description' => 'Hand-rolled Sushi made with the freshest ingredients.',
        'price' => 11900,
        'category' => 'Sushi',
        'imageUrl' => '/images/sushi.jpg',
    ],
];

foreach ($dishes as $dish) {
    if (db()->menu_items->findOne(['name' => $dish['name']])) {
        echo "Skipped {$dish['name']} (already exists)\n";
        continue;
    }

    db()->menu_items->insertOne($dish + ['isAvailable' => true]);
    echo "Added {$dish['name']}\n";
}
