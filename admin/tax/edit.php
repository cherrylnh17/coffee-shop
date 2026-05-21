<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $amount = (float)$_POST['amount'];
        
        if (empty($name)) {
            header("Location: index.php?status=error&msg=" . urlencode("Nama pajak tidak boleh kosong."));
            exit;
        }
        
        $stmt = $pdo->prepare("UPDATE tax SET name = ?, amount = ? WHERE id = ?");
        $stmt->execute([$name, $amount, $id]);
        
        header("Location: index.php?status=success&msg=" . urlencode("Data Pajak berhasil diperbarui."));
        exit;
        
    } catch (PDOException $e) {
        header("Location: index.php?status=error&msg=" . urlencode("Gagal mengupdate pajak: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>