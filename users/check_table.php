<?php
// 1. Tahan semua output teks/error/spasi yang mungkin muncul
ob_start();

// 2. Include file koneksi database
require_once '../config.php'; 

// 3. Bersihkan semua output yang sempat tertahan dari config.php
ob_clean(); 

// 4. Set header agar browser tahu ini adalah file JSON murni
header('Content-Type: application/json; charset=utf-8');

// Siapkan wadah untuk balasan default
$response = ['status' => false, 'message' => ''];

if (isset($_POST['table_name'])) {
    $tableName = trim($_POST['table_name']);

    if ($tableName === '') {
        $response['message'] = 'Input kosong';
    } else {
        try {
            // PERHATIKAN BARIS INI: 
            // Ubah $conn menjadi $pdo (Sesuai dengan nama variabel di config.php kamu)
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

// 5. Cetak wujud asli JSON
echo json_encode($response);

// 6. Matikan script
exit;