<?php
require_once 'config.php'; 

header('Content-Type: application/json');

if (isset($_GET['table_name'])) {
    $tableName = trim($_GET['table_name']);
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM `table` WHERE name = :name LIMIT 1");
        $stmt->execute(['name' => $tableName]);
        $table = $stmt->fetch();

        if ($table) {
            echo json_encode(['status' => 'success', 'message' => 'Meja ditemukan']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Nomor meja tidak ditemukan']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan database']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
}
?>