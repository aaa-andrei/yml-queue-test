<?php

require_once __DIR__ . '/../vendor/autoload.php';

const CATEGORIES_AMOUNT = 500;
const PRODUCTS_PER_CATEGORY_AMOUNT = 10000;

$dbconn = pg_connect("host=localhost dbname=postgres user=postgres password=example")
or die('Could not connect: ' . pg_last_error($dbconn));

pg_query($dbconn, "DROP TABLE IF EXISTS products") or die(pg_last_error($dbconn));

$query = "CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    title varchar(512) NOT NULL,
    category integer NOT NULL,
    show integer NOT NULL DEFAULT '1',
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
)";
pg_query($dbconn, $query) or die(pg_last_error($dbconn));

pg_query($dbconn, "BEGIN") or die(pg_last_error($dbconn));
for ($categoryId = 1; $categoryId <= CATEGORIES_AMOUNT; $categoryId++) {
    echo 'Category' . $categoryId . PHP_EOL;
    $sql = "INSERT INTO products (title, category, show) VALUES ";
    for ($productId = 1; $productId <= PRODUCTS_PER_CATEGORY_AMOUNT; $productId++) {
        $productName = 'Product ' . ($productId * $categoryId);
        $show = $productId%2 ? 1 : 0;
        $sql .= " ('$productName', $categoryId, $show),";
    }
    pg_query($dbconn, rtrim($sql, ',')) or die(pg_last_error($dbconn));
}
pg_query($dbconn, "COMMIT") or die(pg_last_error($dbconn));
