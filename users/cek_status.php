<?php
ob_clean();
header('Content-Type: application/json');
include '../config.php';

// Cek parameter 'code'
if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Code tidak ditemukan']);
    exit;
}

try {
    $id = $_GET['id'];
    // QUERY MENGGUNAKAN KOLOM code
    $stmt = $pdo->prepare("SELECT status FROM `order` WHERE id = ?");
    $stmt->execute([$id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        echo json_encode(['status' => (int)$order['status']]);
    } else {
        echo json_encode(['status' => 0, 'message' => 'Order tidak ditemukan']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
exit;