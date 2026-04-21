<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../../../auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $name = trim($_POST['name']);

        if (empty($name)) {
            header("Location: manajemenmeja.php?status=error&msg=" . urlencode("Nama meja tidak boleh kosong!"));
            exit;
        }

        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM `table` WHERE name = ?");
        $stmt_check->execute([$name]);
        if ($stmt_check->fetchColumn() > 0) {
            header("Location: manajemenmeja.php?status=error&msg=" . urlencode("Nama meja sudah ada di sistem."));
            exit;
        }

        $sql = "INSERT INTO `table` (name) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([$name]);
        
        header("Location: manajemenmeja.php?status=success");
        exit;
        
    } catch (PDOException $e) {
        header("Location: manajemenmeja.php?status=error&msg=" . urlencode("Terjadi kesalahan database: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: manajemenmeja.php");
    exit;
}
?>