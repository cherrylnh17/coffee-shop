<?php
session_start();
require_once '../../../config.php';
require_once '../../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $sql = "DELETE FROM `table` WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$id])) {
            header("Location: index?status=success");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: index?status=error&msg=" . urlencode("Gagal menghapus: Meja mungkin sedang terhubung dengan data transaksi."));
        exit;
    }
} else {
    header("Location: index");
    exit;
}
?>