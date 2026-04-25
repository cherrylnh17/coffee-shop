<?php
session_start();
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];

    try {
        $stmt = $pdo->prepare("UPDATE `order` SET status = 1 WHERE id = ?");
        $stmt->execute([$order_id]);

        // Simpan pesan ke dalam session
        $_SESSION['swal_msg'] = [
            'icon' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Status Pesanan Berhasil Diperbarui!'
        ];

        // Redirect ke index.php
        header("Location: index");
        exit;
    } catch(PDOException $e) {
        // Simpan pesan error ke dalam session
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