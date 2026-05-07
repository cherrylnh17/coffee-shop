<?php
ob_start();

require_once '../config.php'; 

ob_clean(); 

header('Content-Type: application/json; charset=utf-8');

$response = ['status' => false, 'message' => ''];

if (isset($_POST['table_name'])) {
    $tableName = trim($_POST['table_name']);

    if ($tableName === '') {
        $response['message'] = 'Input kosong';
    } else {
        try {
            $query = "SELECT 1 FROM `table` WHERE name = ? LIMIT 1";
            $stmt = $pdo->prepare($query); 
            $stmt->execute([$tableName]);
            
            // PDO mengambil data
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                $response['status'] = true;
                $response['message'] = 'Meja valid';
            } else {
                $response['status'] = false;
                $response['message'] = 'Meja tidak ditemukan';
            }
            
        } catch (PDOException $e) {
            $response['status'] = false;
            $response['message'] = 'Database error: ' . $e->getMessage(); 
        }
    }
} else {
    $response['message'] = 'Akses tidak sah (No POST Data)';
}

// Cetak JSON
echo json_encode($response);

exit;