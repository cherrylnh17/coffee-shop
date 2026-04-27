<?php
session_start();
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    
    $user_id = $_SESSION['user_id'] ?? null; 
    $user_name = $_SESSION['name'] ?? null; 
    if (!$user_id) {
        die("Error: Sesi login tidak ditemukan. Silahkan login kembali.");
    }

    try {
        $sql = "UPDATE `order` SET 
                status = 1, 
                user_id = ?, 
                user_name = ? 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $user_name, $order_id]);

        $_SESSION['swal_msg'] = [
            'icon' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Status Pesanan Berhasil Diperbarui oleh ' . $user_name
        ];

        header("Location: index.php");
        exit;

    } catch(PDOException $e) {
        $_SESSION['swal_msg'] = [
            'icon' => 'error',
            'title' => 'Gagal!',
            'text' => 'Gagal memperbarui status: ' . $e->getMessage()
        ];
        
        header("Location: index.php");
        exit;
    }
}
?>