<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo  = db();
    $rows = $pdo->query("
        SELECT p.product_id AS id, p.name, c.name AS category,
               p.price, p.original_price AS originalPrice,
               p.specs, p.image_url AS image
        FROM products p
        JOIN categories c ON c.category_id = p.category_id
        WHERE p.is_active = 1
        ORDER BY p.product_id ASC
    ")->fetchAll();

    foreach ($rows as &$r) {
        $r['price']         = (float)$r['price'];
        $r['originalPrice'] = (float)$r['originalPrice'];
        $r['liked']         = false;
    }

    json_response(['products' => $rows]);
} catch (Throwable $e) {
    json_response(['error' => $e->getMessage()], 500);
}