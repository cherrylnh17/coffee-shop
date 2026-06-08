<?php

session_start();
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

// Guard: hanya kasir yang login
if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$order_id = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;

if ($order_id <= 0) {
    http_response_code(400);
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT id, menu_name, qty, subtotal, notes
           FROM order_item
          WHERE order_id = :order_id
          ORDER BY id ASC"
    );
    $stmt->execute([':order_id' => $order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Jika diminta juga fee breakdown (?fees=1)
    $include_fees = isset($_GET['fees']) && $_GET['fees'] == '1';
    if ($include_fees) {
        // Ambil snapshot fee dari order_fee
        $feeStmt = $pdo->prepare("SELECT name, type, rate, amount FROM order_fee WHERE order_id = :order_id ORDER BY id ASC");
        $feeStmt->execute([':order_id' => $order_id]);
        $fees = $feeStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fallback ke fee_setting jika order lama (belum ada snapshot)
        if (empty($fees)) {
            // Ambil subtotal order untuk hitung persen
            $subStmt = $pdo->prepare("SELECT subtotal FROM `order` WHERE id = :order_id");
            $subStmt->execute([':order_id' => $order_id]);
            $subtotal = (int)($subStmt->fetchColumn() ?? 0);

            $fsStmt = $pdo->prepare("SELECT name, type, value AS rate FROM fee_setting");
            $fsStmt->execute();
            foreach ($fsStmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
                $amt = (int)$f['type'] === 1
                    ? (int) round($subtotal * ((float)$f['rate'] / 100))
                    : (int) round((float)$f['rate']);
                $fees[] = ['name' => $f['name'], 'type' => $f['type'], 'rate' => $f['rate'], 'amount' => $amt];
            }
        }

        echo json_encode(['items' => $items, 'fees' => $fees]);
    } else {
        echo json_encode($items);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([]);
}