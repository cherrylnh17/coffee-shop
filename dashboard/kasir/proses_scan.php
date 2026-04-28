<?php
session_start();
include '../../config.php';
require_once '../../path.php';

if (!isset($_SESSION['name']) || $_SESSION['role'] != 1) {
    echo "SESSION_EXPIRED";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataScan = $_POST['qrcode_data'] ?? '';

    if (!empty($dataScan)) {
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
            echo "SUCCESS:check_order?code=" . $row['code'];
        }
    } else {
        echo "Data kosong!";
    }
}
?>