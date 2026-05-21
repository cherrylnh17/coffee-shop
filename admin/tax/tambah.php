<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name']);
        $amount = (float)$_POST['amount'];
        
        $created_at = date('Y-m-d H:i:s');
        
        $stmt = $pdo->prepare("INSERT INTO tax (name, amount, created_at) VALUES (?, ?, ?)");
        $stmt->execute([$name, $amount, $created_at]);
        
        header("Location: index.php?status=success&msg=" . urlencode("Data Pajak berhasil ditambahkan."));
        exit;
        
    } catch (PDOException $e) {
        header("Location: index.php?status=error&msg=" . urlencode("Gagal menambah pajak: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>