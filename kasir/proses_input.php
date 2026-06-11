<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php'; 

if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataScan = trim($_POST['code'] ?? '');

    if (!empty($dataScan)) {
        $stmt = $pdo->prepare("SELECT * FROM `order` WHERE code = ?");
        $stmt->execute([$dataScan]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo "Kode pesanan tidak ditemukan di sistem!";
        } else if ($row['status'] == 3) {
            echo "Kode pesanan telah kadaluarsa!";
        } else if ($row['status'] == 1) {
            echo "Pesanan ini sudah dibayar/selesai!";
        } else {
            echo "SUCCESS:check_order?code=" . $row['code'];
        }
    } else {
        echo "Kode pesanan tidak boleh kosong!";
    }
}
?>