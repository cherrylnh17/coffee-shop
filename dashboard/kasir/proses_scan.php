<?php
include '../../config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataScan = $_POST['qrcode_data'] ?? '';

    if (!empty($dataScan)) {
        // Kita hanya cari berdasarkan code dulu agar bisa memberikan pesan error yang spesifik
        $stmt = $pdo->prepare("SELECT * FROM `order` WHERE code = ?");
        $stmt->execute([$dataScan]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo "QR tidak terdaftar di sistem!";
        } else if ($row['status'] == 3) {
            echo "QR Telah Kadaluarsa!";
        } else if ($row['status'] == 1) {
            echo "Pesanan ini sudah dibayar/selesai!";
        } else {
           header("Location: checkout?code=" . $row['code']);
        }
    } else {
        echo "Data kosong!";
    }
}
?>