<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../config.php';

if (isset($_GET['id'])) {
    try {
        $id = (int)$_GET['id'];

        $stmt = $pdo->prepare("DELETE FROM fee_setting WHERE id = ?");
        $stmt->execute([$id]);

        header("Location: index.php?status=success&msg=" . urlencode("Data berhasil dihapus."));
        exit;

    } catch (PDOException $e) {
        header("Location: index.php?status=error&msg=" . urlencode("Gagal menghapus data: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}