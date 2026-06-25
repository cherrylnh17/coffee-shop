<?php
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

$redirectUrl = BASE_URL . 'admin/tax';

if (isset($_GET['id'])) {
    try {
        $id = (int)$_GET['id'];

        $stmt = $pdo->prepare("DELETE FROM gratuity WHERE id = ?");
        $stmt->execute([$id]);

        header("Location: " . $redirectUrl . "?status=success&msg=" . urlencode("Data berhasil dihapus."));
        exit;

    } catch (PDOException $e) {
        header("Location: " . $redirectUrl . "?status=error&msg=" . urlencode("Gagal menghapus data: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: " . $redirectUrl);
    exit;
}