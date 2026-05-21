<?php
/**
 * Endpoint AJAX: kasir/order/items?order_id=123
 * Mengembalikan JSON list order_item berdasarkan order_id.
 */
session_start();
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

// Guard: hanya kasir yang login
if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
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

    echo json_encode($items);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([]);
}