<?php
require_once __DIR__ . '/../config/database.php';

$code = strtoupper(trim($_POST['code'] ?? ''));

if ($code === '') {
    json_response(['valid' => false, 'message' => 'Please enter a voucher code.'], 400);
}

try {
    $stmt = db()->prepare("SELECT code, discount_rate FROM vouchers
                           WHERE code = :c AND is_active = 1
                             AND times_used < max_uses LIMIT 1");
    $stmt->execute([':c' => $code]);
    $v = $stmt->fetch();

    if (!$v) {
        json_response(['valid' => false, 'message' => 'Invalid or expired voucher code.']);
    }

    json_response([
        'valid'         => true,
        'code'          => $v['code'],
        'discount_rate' => (float)$v['discount_rate'],
        'message'       => '✓ Code ' . $v['code'] . ' applied! (' . round($v['discount_rate'] * 100) . '% Off)'
    ]);
} catch (Throwable $e) {
    json_response(['valid' => false, 'message' => $e->getMessage()], 500);
}