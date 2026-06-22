<?php
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

$redirectUrl = BASE_URL . 'admin/tax';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name   = trim($_POST['name']);
        $type   = (int)$_POST['type']; // 1 = persen, 2 = fixed
        $value  = (float)$_POST['value'];

        if (empty($name)) {
            header("Location: " . $redirectUrl . "?status=error&msg=" . urlencode("Nama biaya tidak boleh kosong."));
            exit;
        }

        if (!in_array($type, [1, 2])) {
            header("Location: " . $redirectUrl . "?status=error&msg=" . urlencode("Tipe tidak valid."));
            exit;
        }

        if ($type == 1 && ($value <= 0 || $value > 100)) {
            header("Location: " . $redirectUrl . "?status=error&msg=" . urlencode("Persentase harus antara 0.01 hingga 100."));
            exit;
        }

        if ($type == 2 && $value < 0) {
            header("Location: " . $redirectUrl . "?status=error&msg=" . urlencode("Nilai nominal tidak boleh negatif."));
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO fee_setting (name, type, value) VALUES (?, ?, ?)");
        $stmt->execute([$name, $type, $value]);

        header("Location: " . $redirectUrl . "?status=success&msg=" . urlencode("Biaya berhasil ditambahkan."));
        exit;

    } catch (PDOException $e) {
        header("Location: " . $redirectUrl . "?status=error&msg=" . urlencode("Gagal menambah data: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: " . $redirectUrl);
    exit;
}