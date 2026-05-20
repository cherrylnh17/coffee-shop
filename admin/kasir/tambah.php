<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $name = trim($_POST['name']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $konfirmasi_password = $_POST['konfirmasi_password'];

        if ($password !== $konfirmasi_password) {
            header("Location: index?status=error&msg=" . urlencode("Password dan Konfirmasi Password tidak cocok!"));
            exit;
        }

        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM user WHERE username = ?");
        $stmt_check->execute([$username]);
        if ($stmt_check->fetchColumn() > 0) {
            header("Location: index?status=error&msg=" . urlencode("Username sudah digunakan, silakan pilih yang lain."));
            exit;
        }

        $sql = "INSERT INTO user (name, username, password, role) VALUES (?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([$name, $username, $password]);
        
        header("Location: index?status=success");
        exit;
        
    } catch (PDOException $e) {
        header("Location: index?status=error&msg=" . urlencode("Terjadi kesalahan database: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: index");
    exit;
}
?>