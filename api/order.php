<?php
require_once __DIR__ . '/../config/database.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$userId      = (int)($input['user_id'] ?? 1);
$items       = $input['items'] ?? [];
$voucherCode = strtoupper(trim($input['voucher'] ?? ''));

if (empty($items)) {
    json_response(['success' => false, 'error' => 'Cart is empty.'], 400);
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    $voucherId = null;
    if ($voucherCode !== '') {
        $v = $pdo->prepare("SELECT voucher_id FROM vouchers
                            WHERE code = :c AND is_active = 1 LIMIT 1");
        $v->execute([':c' => $voucherCode]);
        $voucherId = $v->fetchColumn() ?: null;
    }

    $pdo->prepare("INSERT INTO orders (user_id, voucher_id, status)
                   VALUES (:u, :v, 'paid')")
        ->execute([':u' => $userId, ':v' => $voucherId]);
    $orderId = (int)$pdo->lastInsertId();

    $pStmt = $pdo->prepare("SELECT product_id, name, price FROM products
                            WHERE product_id = :id AND is_active = 1");
    $iStmt = $pdo->prepare("INSERT INTO order_items
        (order_id, product_id, product_name, unit_price, quantity, line_total)
        VALUES (:o, :p, :n, :u, :q, 0)");

    foreach ($items as $it) {
        $pid = (int)$it['product_id'];
        $qty = (int)$it['quantity'];
        if ($qty <= 0) continue;

        $pStmt->execute([':id' => $pid]);
        $prod = $pStmt->fetch();
        if (!$prod) throw new RuntimeException("Product {$pid} not found.");

        $iStmt->execute([
            ':o' => $orderId, ':p' => $pid, ':n' => $prod['name'],
            ':u' => $prod['price'], ':q' => $qty,
        ]);
    }

    $pdo->commit();

    $sum = $pdo->prepare("SELECT order_code, subtotal, discount_amount, total_amount
                          FROM orders WHERE order_id = :id");
    $sum->execute([':id' => $orderId]);

    json_response(['success' => true, 'order' => $sum->fetch()]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    json_response(['success' => false, 'error' => $e->getMessage()], 500);
}