<?php
require_once __DIR__ . '/../../config.php';

if (ob_get_level()) {
    ob_clean();
}
header('Content-Type: application/json');

// Hanya izinkan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Update semua order yang pending
    $stmt = $pdo->prepare("
        UPDATE `order`
        SET status = 3
        WHERE status = 2
          AND expired_at IS NOT NULL
          AND expired_at <= NOW()
    ");
    $stmt->execute();

    $affected = $stmt->rowCount();

    echo json_encode([
        'success'  => true,
        'expired'  => $affected,
        'message'  => "$affected order ditandai expired",
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
exit;