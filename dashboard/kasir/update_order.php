<?php
session_start();
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];

    try {
        $stmt = $pdo->prepare("UPDATE `order` SET status = 1 WHERE id = ?");
        $stmt->execute([$order_id]);

        // Kembali ke halaman sebelumnya atau riwayat
        echo "<script>alert('Status Berhasil Diperbarui!'); window.location.href='riwayat_pesanan/riwayat.php';</script>";
    } catch(PDOException $e) {
        die("Gagal memperbarui status: " . $e->getMessage());
    }
}