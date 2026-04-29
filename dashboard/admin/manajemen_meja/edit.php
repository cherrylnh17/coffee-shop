<?php
session_start();
require_once '../../../config.php';
require_once '../../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    try {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);

        if (empty($name)) {
            header("Location: manajemenmeja.php?status=error&msg=" . urlencode("Nama meja tidak boleh kosong!"));
            exit;
        }

        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM `table` WHERE name = ? AND id != ?");
        $stmt_check->execute([$name, $id]);
        if ($stmt_check->fetchColumn() > 0) {
            header("Location: manajemenmeja.php?status=error&msg=" . urlencode("Nama meja sudah digunakan untuk meja lain."));
            exit;
        }

        $sql = "UPDATE `table` SET name = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([$name, $id]);
        
        header("Location: manajemenmeja.php?status=success");
        exit;

    } catch (PDOException $e) {
        header("Location: manajemenmeja.php?status=error&msg=" . urlencode("Terjadi kesalahan: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: manajemenmeja.php");
    exit;
}
?>