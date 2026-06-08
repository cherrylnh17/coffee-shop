// * * 1 * * /usr/bin/curl -s "http://localhost/report/cron/delete_order.php" > /dev/null 2>&1
<?php
require_once __DIR__ . '/../../config.php';

if (ob_get_level()) {
    ob_clean();
}
header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("
        DELETE FROM `order`
        WHERE status = 3
          AND expired_at <= DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $stmt->execute();
    $deleted = $stmt->rowCount();

    echo json_encode([
        'success' => true,
        'deleted' => $deleted,
        'message' => "$deleted order dihapus",
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
exit; 