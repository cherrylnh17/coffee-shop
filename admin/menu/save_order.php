<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Read JSON body
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['order']) || !is_array($input['order'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data format. Expected {order: [{id, sort_order}]}']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("UPDATE menu SET sort_order = ? WHERE id = ?");
    
    foreach ($input['order'] as $item) {
        if (!isset($item['id']) || !isset($item['sort_order'])) {
            throw new Exception('Each item must have id and sort_order');
        }
        $stmt->execute([(int)$item['sort_order'], (int)$item['id']]);
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Menu order saved successfully']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save order: ' . $e->getMessage()]);
}
