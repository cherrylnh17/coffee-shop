// 5 * * * * /usr/bin/curl -s "http://localhost/report/cron/update_status.php" > /dev/null 2>&1
<?php
require_once __DIR__ . '/../../config.php';

if (ob_get_level()) {
    ob_clean();
}
header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("
        UPDATE `order`
        SET status = 3
        WHERE status = 2
          AND expired_at IS NOT NULL
          AND expired_at <= NOW()
    ");
    $stmt->execute();
    $expired = $stmt->rowCount();

    echo json_encode([
        'success' => true,
        'expired' => $expired,
        'message' => "$expired order ditandai expired",
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
exit; 