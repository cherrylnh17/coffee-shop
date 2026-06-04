<?php
require_once __DIR__ . '/../../config.php';

if (ob_get_level()) {
    ob_clean();
}
header('Content-Type: application/json');

try {
    $stmtExpire = $pdo->prepare("
        UPDATE `order`
        SET status = 3
        WHERE status = 2
          AND expired_at IS NOT NULL
          AND expired_at <= NOW()
    ");
    $stmtExpire->execute();
    $expired = $stmtExpire->rowCount();

    $stmtDelete = $pdo->prepare("
        DELETE FROM `order`
        WHERE status = 3
          AND expired_at <= DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $stmtDelete->execute();
    $deleted = $stmtDelete->rowCount();

    echo json_encode([
        'success' => true,
        'expired' => $expired,
        'deleted' => $deleted,
        'message' => "$expired order ditandai expired, $deleted order dihapus",
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
exit;