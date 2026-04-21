<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../../../auth/login.php");
    exit;
}

require_once '../../../config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $sql = "DELETE FROM `table` WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$id])) {
            header("Location: manajemenmeja.php?status=success");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: manajemenmeja.php?status=error&msg=" . urlencode("Gagal menghapus: Meja mungkin sedang terhubung dengan data transaksi."));
        exit;
    }
} else {
    header("Location: manajemenmeja.php");
    exit;
}
?>